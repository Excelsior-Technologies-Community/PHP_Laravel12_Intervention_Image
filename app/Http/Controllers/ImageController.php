<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ImageController extends Controller
{
    /**
     * Display the Image Processing Studio.
     */
    public function index(): View
    {
        return view('imageUpload');
    }

    /**
     * Process image with transforms, dual-mode watermarks, target size compression, and responsive variants.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'max:10240', // 10MB
            ],

            // Transforms
            'crop' => ['required', 'in:original,1:1,4:3,3:4,16:9'],
            'resize' => ['required', 'in:original,300x300,600x400,800x600,1200x800,1920x1080'],
            'rotation' => ['required', 'in:0,90,180,270'],
            'auto_orientation' => ['required', 'in:yes,no'],
            'brightness' => ['required', 'integer', 'min:-100', 'max:100'],
            'contrast' => ['required', 'integer', 'min:-100', 'max:100'],
            'grayscale' => ['required', 'in:yes,no'],
            'blur' => ['required', 'integer', 'min:0', 'max:20'],
            'sharpen' => ['required', 'integer', 'min:0', 'max:20'],
            'mirror' => ['required', 'in:none,horizontal,vertical'],
            'invert' => ['required', 'in:yes,no'],

            // Output & Compression
            'format' => ['required', 'in:original,jpg,png,webp,avif'],
            'compression_mode' => ['required', 'in:manual,target_size'],
            'quality' => ['required', 'integer', 'min:10', 'max:100'],
            'target_kb' => ['nullable', 'integer', 'min:20', 'max:5000'],

            // Module 1: Dual-Mode Watermarking & 9-Point Positioning Matrix
            'watermark_mode' => ['required', 'in:none,text,logo,tiled'],
            'watermark_text' => ['nullable', 'string', 'max:100'],
            'watermark_logo' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'watermark_position' => ['required', 'in:top-left,top,top-right,left,center,right,bottom-left,bottom,bottom-right'],
            'watermark_opacity' => ['required', 'integer', 'min:10', 'max:100'],
            'watermark_padding' => ['required', 'integer', 'min:5', 'max:100'],

            // Module 2: Smart Multi-Size Responsive Variants Generator
            'generate_variants' => ['required', 'in:yes,no'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prepare Required Directories
        |--------------------------------------------------------------------------
        */
        $paths = [
            'images' => public_path('images'),
            'thumbnail' => public_path('images/thumbnail'),
            'processed' => public_path('images/processed'),
            'variants' => public_path('images/variants'),
            'zips' => public_path('images/zips'),
        ];

        foreach ($paths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }

        $manager = new ImageManager(new Driver(), autoOrientation: false);

        /*
        |--------------------------------------------------------------------------
        | Save Original Upload
        |--------------------------------------------------------------------------
        */
        $uploadedFile = $request->file('image');
        $originalExt = strtolower($uploadedFile->getClientOriginalExtension());
        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeOriginalName = preg_replace('/[^A-Za-z0-9_-]/', '_', $originalName);
        $timestamp = now()->format('Ymd_His');

        $originalFileName = "{$safeOriginalName}_{$timestamp}.{$originalExt}";
        $originalFullPath = $paths['images'] . DIRECTORY_SEPARATOR . $originalFileName;
        $uploadedFile->move($paths['images'], $originalFileName);

        $originalFileSize = File::size($originalFullPath);

        /*
        |--------------------------------------------------------------------------
        | 1. Create 150x150 Thumbnail
        |--------------------------------------------------------------------------
        */
        $thumbnailImg = $manager->read($originalFullPath);
        if ($validated['auto_orientation'] === 'yes') {
            $thumbnailImg->orient();
        }
        $thumbnailImg->cover(150, 150);

        $thumbnailFileName = "{$safeOriginalName}_{$timestamp}_thumb.{$originalExt}";
        $thumbnailFullPath = $paths['thumbnail'] . DIRECTORY_SEPARATOR . $thumbnailFileName;

        if ($originalExt === 'png') {
            $thumbnailImg->toPng(false)->save($thumbnailFullPath);
        } elseif ($originalExt === 'webp') {
            $thumbnailImg->toWebp(85)->save($thumbnailFullPath);
        } elseif ($originalExt === 'avif') {
            $thumbnailImg->toAvif(85)->save($thumbnailFullPath);
        } else {
            $thumbnailImg->toJpeg(85)->save($thumbnailFullPath);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Main Image Transformations
        |--------------------------------------------------------------------------
        */
        $image = $manager->read($originalFullPath);

        // Auto orientation
        if ($validated['auto_orientation'] === 'yes') {
            $image->orient();
        }

        // Crop / Aspect Ratio
        switch ($validated['crop']) {
            case '1:1':
                $minDim = min($image->width(), $image->height());
                $image->cover($minDim, $minDim);
                break;
            case '4:3':
                $w = $image->width();
                $h = (int) round($w * 3 / 4);
                if ($h > $image->height()) {
                    $h = $image->height();
                    $w = (int) round($h * 4 / 3);
                }
                $image->cover($w, $h);
                break;
            case '3:4':
                $w = $image->width();
                $h = (int) round($w * 4 / 3);
                if ($h > $image->height()) {
                    $h = $image->height();
                    $w = (int) round($h * 3 / 4);
                }
                $image->cover($w, $h);
                break;
            case '16:9':
                $w = $image->width();
                $h = (int) round($w * 9 / 16);
                if ($h > $image->height()) {
                    $h = $image->height();
                    $w = (int) round($h * 16 / 9);
                }
                $image->cover($w, $h);
                break;
        }

        // Resize
        if ($validated['resize'] !== 'original') {
            [$rw, $rh] = explode('x', $validated['resize']);
            $image->resize((int) $rw, (int) $rh);
        }

        // Rotation
        $rotation = (int) $validated['rotation'];
        if ($rotation !== 0) {
            $image->rotate($rotation);
        }

        // Brightness & Contrast
        $brightness = (int) $validated['brightness'];
        if ($brightness !== 0) {
            $image->brightness($brightness);
        }

        $contrast = (int) $validated['contrast'];
        if ($contrast !== 0) {
            $image->contrast($contrast);
        }

        // Grayscale
        if ($validated['grayscale'] === 'yes') {
            $image->greyscale();
        }

        // Blur & Sharpen
        $blur = (int) $validated['blur'];
        if ($blur > 0) {
            $image->blur($blur);
        }

        $sharpen = (int) $validated['sharpen'];
        if ($sharpen > 0) {
            $image->sharpen($sharpen);
        }

        // Mirror / Flip
        if ($validated['mirror'] === 'horizontal') {
            $image->flop();
        } elseif ($validated['mirror'] === 'vertical') {
            $image->flip();
        }

        // Invert Colors
        if ($validated['invert'] === 'yes') {
            $image->invert();
        }

        /*
        |--------------------------------------------------------------------------
        | Module 1: Dual-Mode Watermarking & 9-Point Positioning Matrix
        |--------------------------------------------------------------------------
        */
        $watermarkMode = $validated['watermark_mode'];
        $wmPosition = $validated['watermark_position'];
        $wmOpacity = (int) $validated['watermark_opacity'];
        $wmPadding = (int) $validated['watermark_padding'];
        $watermarkInfo = 'None';

        if ($watermarkMode === 'logo' && $request->hasFile('watermark_logo')) {
            // Logo Watermark
            $logoFile = $request->file('watermark_logo');
            $logo = $manager->read($logoFile->getRealPath());

            // Auto scale logo to max 25% of target image width
            $maxLogoW = (int) round($image->width() * 0.25);
            if ($logo->width() > $maxLogoW) {
                $logo->scale(width: $maxLogoW);
            }

            $image->place($logo, $wmPosition, $wmPadding, $wmPadding, $wmOpacity);
            $watermarkInfo = "Logo Image ({$wmPosition}, {$wmOpacity}% opacity)";
        } elseif ($watermarkMode === 'text' && !empty($validated['watermark_text'])) {
            // Text Watermark placed with 9-point grid and opacity
            $text = trim($validated['watermark_text']);
            $fontSize = max(16, (int) round($image->width() * 0.035));

            // Estimate text dimensions and place
            $textCanvas = $manager->create((int) ($fontSize * mb_strlen($text) * 0.8), (int) ($fontSize * 1.8));
            $textCanvas->text($text, 10, (int) ($fontSize * 1.2), function ($font) use ($fontSize) {
                $font->size($fontSize);
                $font->color('#ffffff');
                $font->stroke('#000000', 2);
            });

            $image->place($textCanvas, $wmPosition, $wmPadding, $wmPadding, $wmOpacity);
            $watermarkInfo = "Text: \"{$text}\" ({$wmPosition}, {$wmOpacity}% opacity)";
        } elseif ($watermarkMode === 'tiled') {
            // Full Diagonal Tiled Copyright Pattern
            $tiledText = !empty($validated['watermark_text']) ? trim($validated['watermark_text']) : '© COPYRIGHT PROTECTED';
            $w = $image->width();
            $h = $image->height();
            $stepX = max(180, (int) ($w / 4));
            $stepY = max(120, (int) ($h / 5));

            for ($x = 20; $x < $w; $x += $stepX) {
                for ($y = 40; $y < $h; $y += $stepY) {
                    $image->text($tiledText, $x, $y, function ($font) {
                        $font->size(20);
                        $font->color('ffffff55');
                        $font->stroke('00000033', 1);
                    });
                }
            }
            $watermarkInfo = "Full Tiled Pattern (\"{$tiledText}\")";
        }

        /*
        |--------------------------------------------------------------------------
        | Module 3: Target Size Compression & Quality Optimization
        |--------------------------------------------------------------------------
        */
        $outputFormat = $validated['format'] === 'original' ? $originalExt : $validated['format'];
        if (!in_array($outputFormat, ['jpg', 'jpeg', 'png', 'webp', 'avif'], true)) {
            $outputFormat = 'jpg';
        }

        $quality = (int) $validated['quality'];
        $targetKb = $validated['target_kb'] ? (int) $validated['target_kb'] : null;

        if ($validated['compression_mode'] === 'target_size' && $targetKb > 0 && in_array($outputFormat, ['jpg', 'jpeg', 'webp', 'avif'])) {
            // Iterative binary search to find the optimal quality meeting target size in KB
            $targetBytes = $targetKb * 1024;
            $low = 10;
            $high = 95;
            $bestQuality = 75;

            while ($low <= $high) {
                $mid = (int) round(($low + $high) / 2);
                $encodedSample = $this->encodeImage($image, $outputFormat, $mid);
                $sampleSize = strlen((string) $encodedSample);

                if ($sampleSize <= $targetBytes) {
                    $bestQuality = $mid;
                    $low = $mid + 1; // Try for better quality
                } else {
                    $high = $mid - 1; // Need smaller size
                }
            }
            $quality = $bestQuality;
        }

        // Final encoding of processed image
        $encodedProcessed = $this->encodeImage($image, $outputFormat, $quality);

        $processedExt = ($outputFormat === 'jpeg') ? 'jpg' : $outputFormat;
        $processedFileName = "{$safeOriginalName}_{$timestamp}_processed.{$processedExt}";
        $processedFullPath = $paths['processed'] . DIRECTORY_SEPARATOR . $processedFileName;
        $encodedProcessed->save($processedFullPath);

        $processedFileSize = File::size($processedFullPath);
        $savedBytes = max(0, $originalFileSize - $processedFileSize);
        $savedPercent = ($originalFileSize > 0) ? round(($savedBytes / $originalFileSize) * 100, 1) : 0;

        /*
        |--------------------------------------------------------------------------
        | Module 2: Smart Multi-Size Variant & Responsive <picture> Snippet Generator
        |--------------------------------------------------------------------------
        */
        $variantsList = [];
        $zipFileName = null;
        $pictureSnippet = '';

        if ($validated['generate_variants'] === 'yes') {
            $variantDefinitions = [
                'thumb'   => ['label' => 'Thumbnail', 'w' => 150, 'h' => 150, 'mode' => 'cover'],
                'mobile'  => ['label' => 'Mobile View', 'w' => 480, 'h' => null, 'mode' => 'scale'],
                'tablet'  => ['label' => 'Tablet View', 'w' => 768, 'h' => null, 'mode' => 'scale'],
                'desktop' => ['label' => 'Desktop HD', 'w' => 1200, 'h' => null, 'mode' => 'scale'],
                'retina'  => ['label' => 'Retina 2x', 'w' => 2000, 'h' => null, 'mode' => 'scale'],
            ];

            $zipFileName = "variants_{$safeOriginalName}_{$timestamp}.zip";
            $zipFullPath = $paths['zips'] . DIRECTORY_SEPARATOR . $zipFileName;
            $zip = new ZipArchive();
            $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($variantDefinitions as $key => $def) {
                $vImg = $manager->read($processedFullPath);
                if ($def['mode'] === 'cover') {
                    $vImg->cover($def['w'], $def['h']);
                } else {
                    if ($vImg->width() > $def['w']) {
                        $vImg->scale(width: $def['w']);
                    }
                }

                // Generate WebP and AVIF versions for each variant
                $vNameWebp = "{$safeOriginalName}_{$timestamp}_{$key}.webp";
                $vPathWebp = $paths['variants'] . DIRECTORY_SEPARATOR . $vNameWebp;
                $vImg->toWebp(80)->save($vPathWebp);

                $vNameAvif = "{$safeOriginalName}_{$timestamp}_{$key}.avif";
                $vPathAvif = $paths['variants'] . DIRECTORY_SEPARATOR . $vNameAvif;
                $vImg->toAvif(80)->save($vPathAvif);

                // Add to ZIP
                $zip->addFile($vPathWebp, "webp/{$vNameWebp}");
                $zip->addFile($vPathAvif, "avif/{$vNameAvif}");

                $variantsList[$key] = [
                    'label' => $def['label'],
                    'width' => $vImg->width(),
                    'height' => $vImg->height(),
                    'webp' => $vNameWebp,
                    'avif' => $vNameAvif,
                    'size_kb' => round(File::size($vPathWebp) / 1024, 1),
                ];
            }

            // Also add main processed image to zip
            $zip->addFile($processedFullPath, "main/{$processedFileName}");
            $zip->close();

            // Generate HTML <picture> code snippet
            $pictureSnippet = $this->generatePictureSnippet($safeOriginalName, $timestamp, $variantsList, $processedFileName);
        }

        /*
        |--------------------------------------------------------------------------
        | Store Result Payload in Session
        |--------------------------------------------------------------------------
        */
        session([
            'image_result' => [
                'original' => $originalFileName,
                'thumbnail' => $thumbnailFileName,
                'processed' => $processedFileName,
                'original_size_bytes' => $originalFileSize,
                'original_size_kb' => round($originalFileSize / 1024, 2),
                'original_size_mb' => round($originalFileSize / (1024 * 1024), 2),
                'processed_size_bytes' => $processedFileSize,
                'processed_size_kb' => round($processedFileSize / 1024, 2),
                'saved_bytes' => $savedBytes,
                'saved_percent' => $savedPercent,
                'width' => $image->width(),
                'height' => $image->height(),
                'format' => $processedExt,
                'quality' => $quality,
                'compression_mode' => $validated['compression_mode'],
                'target_kb' => $targetKb,
                'watermark_mode' => $watermarkMode,
                'watermark_info' => $watermarkInfo,
                'watermark_position' => $wmPosition,
                'crop' => $validated['crop'],
                'resize' => $validated['resize'],
                'rotation' => $validated['rotation'],
                'variants' => $variantsList,
                'zip_file' => $zipFileName,
                'picture_snippet' => $pictureSnippet,
            ],
        ]);

        return redirect()
            ->route('image.index')
            ->with('success', 'Image processed, watermarked, optimized and responsive variants generated successfully!');
    }

    /**
     * Download generated ZIP archive of responsive variants.
     */
    public function downloadZip(string $filename): BinaryFileResponse|RedirectResponse
    {
        $safeName = basename($filename);
        $fullPath = public_path('images/zips/' . $safeName);

        if (!File::exists($fullPath)) {
            return redirect()->route('image.index')->with('error', 'Requested ZIP archive not found or expired.');
        }

        return response()->download($fullPath, $safeName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Encode image based on format and quality.
     */
    protected function encodeImage($image, string $format, int $quality)
    {
        switch ($format) {
            case 'webp':
                return $image->toWebp($quality);
            case 'avif':
                return $image->toAvif($quality);
            case 'png':
                return $image->toPng(false);
            case 'jpg':
            case 'jpeg':
            default:
                return $image->toJpeg($quality);
        }
    }

    /**
     * Generate HTML <picture> code snippet with responsive srcset breakpoints.
     */
    protected function generatePictureSnippet(string $safeName, string $timestamp, array $variants, string $fallbackFile): string
    {
        $baseUrl = asset('images/variants');
        $mainUrl = asset('images/processed/' . $fallbackFile);

        $avifSources = [];
        $webpSources = [];

        if (isset($variants['mobile'])) {
            $avifSources[] = "{$baseUrl}/{$variants['mobile']['avif']} 480w";
            $webpSources[] = "{$baseUrl}/{$variants['mobile']['webp']} 480w";
        }
        if (isset($variants['tablet'])) {
            $avifSources[] = "{$baseUrl}/{$variants['tablet']['avif']} 768w";
            $webpSources[] = "{$baseUrl}/{$variants['tablet']['webp']} 768w";
        }
        if (isset($variants['desktop'])) {
            $avifSources[] = "{$baseUrl}/{$variants['desktop']['avif']} 1200w";
            $webpSources[] = "{$baseUrl}/{$variants['desktop']['webp']} 1200w";
        }

        $avifSrcset = implode(', ', $avifSources);
        $webpSrcset = implode(', ', $webpSources);

        return <<<HTML
<picture>
    <!-- AVIF next-gen format for ultra fast modern browsers -->
    <source type="image/avif" srcset="{$avifSrcset}" sizes="(max-width: 768px) 100vw, 1200px">
    
    <!-- WebP format for broad modern browser support -->
    <source type="image/webp" srcset="{$webpSrcset}" sizes="(max-width: 768px) 100vw, 1200px">
    
    <!-- Fallback default image -->
    <img src="{$mainUrl}" alt="Responsive Optimized Media" class="img-fluid rounded shadow" loading="lazy" decoding="async">
</picture>
HTML;
    }
}