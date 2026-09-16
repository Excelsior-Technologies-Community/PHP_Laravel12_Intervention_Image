<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    public function index(): View
    {
        return view('imageUpload');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // Existing features
            'crop' => [
                'required',
                'in:original,1:1,4:3,3:4,16:9',
            ],

            'resize' => [
                'required',
                'in:original,300x300,600x400,800x600',
            ],

            'rotation' => [
                'required',
                'in:0,90,180,270',
            ],

            'watermark' => [
                'nullable',
                'string',
                'max:100',
            ],

            'format' => [
                'required',
                'in:original,jpg,png,webp',
            ],

            'quality' => [
                'required',
                'integer',
                'min:10',
                'max:100',
            ],

            // New features
            'auto_orientation' => [
                'required',
                'in:yes,no',
            ],

            'brightness' => [
                'required',
                'integer',
                'min:-100',
                'max:100',
            ],

            'contrast' => [
                'required',
                'integer',
                'min:-100',
                'max:100',
            ],

            'grayscale' => [
                'required',
                'in:yes,no',
            ],

            'blur' => [
                'required',
                'integer',
                'min:0',
                'max:20',
            ],

            'sharpen' => [
                'required',
                'integer',
                'min:0',
                'max:20',
            ],

            'mirror' => [
                'required',
                'in:none,horizontal,vertical',
            ],

            'invert' => [
                'required',
                'in:yes,no',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create directories
        |--------------------------------------------------------------------------
        */

        $imagesPath = public_path('images');

        $thumbnailPath = public_path('images/thumbnail');

        $processedPath = public_path('images/processed');

        if (!File::exists($imagesPath)) {
            File::makeDirectory(
                $imagesPath,
                0755,
                true
            );
        }

        if (!File::exists($thumbnailPath)) {
            File::makeDirectory(
                $thumbnailPath,
                0755,
                true
            );
        }

        if (!File::exists($processedPath)) {
            File::makeDirectory(
                $processedPath,
                0755,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Image Manager
        |--------------------------------------------------------------------------
        */

        $manager = new ImageManager(
            new Driver(),
            autoOrientation: false
        );

        /*
        |--------------------------------------------------------------------------
        | Upload original image
        |--------------------------------------------------------------------------
        */

        $uploadedImage = $request->file('image');

        $originalExtension = strtolower(
            $uploadedImage->getClientOriginalExtension()
        );

        $originalName = pathinfo(
            $uploadedImage->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $safeOriginalName = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $originalName
        );

        $timestamp = now()->format('Ymd_His');

        $originalFileName =
            $safeOriginalName . '_' .
            $timestamp . '.' .
            $originalExtension;

        /*
        |--------------------------------------------------------------------------
        | Save original image
        |--------------------------------------------------------------------------
        */

        $uploadedImage->move(
            $imagesPath,
            $originalFileName
        );

        $originalFullPath =
            $imagesPath . DIRECTORY_SEPARATOR . $originalFileName;

        /*
        |--------------------------------------------------------------------------
        | Read original image
        |--------------------------------------------------------------------------
        */

        $originalImage = $manager->read(
            $originalFullPath
        );

        /*
        |--------------------------------------------------------------------------
        | Create 100x100 thumbnail
        |--------------------------------------------------------------------------
        */

        $thumbnailImage = $manager->read(
            $originalFullPath
        );

        if ($validated['auto_orientation'] === 'yes') {
            $thumbnailImage->orient();
        }

        $thumbnailImage->cover(
            100,
            100
        );

        $thumbnailFileName =
            pathinfo(
                $originalFileName,
                PATHINFO_FILENAME
            ) . '_thumbnail.' .
            $originalExtension;

        $thumbnailFullPath =
            $thumbnailPath .
            DIRECTORY_SEPARATOR .
            $thumbnailFileName;

        /*
        |--------------------------------------------------------------------------
        | Save thumbnail according to original extension
        |--------------------------------------------------------------------------
        */

        switch ($originalExtension) {
            case 'jpg':
            case 'jpeg':
                $thumbnailImage
                    ->toJpeg(85)
                    ->save($thumbnailFullPath);
                break;

            case 'png':
                $thumbnailImage
                    ->toPng(false)
                    ->save($thumbnailFullPath);
                break;

            case 'webp':
                $thumbnailImage
                    ->toWebp(85)
                    ->save($thumbnailFullPath);
                break;

            default:
                $thumbnailImage
                    ->toJpeg(85)
                    ->save($thumbnailFullPath);

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Read image for processing
        |--------------------------------------------------------------------------
        */

        $processedImage = $manager->read(
            $originalFullPath
        );

        /*
        |--------------------------------------------------------------------------
        | 1. EXIF Auto Orientation
        |--------------------------------------------------------------------------
        */

        if ($validated['auto_orientation'] === 'yes') {
            $processedImage->orient();
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Crop / Aspect Ratio
        |--------------------------------------------------------------------------
        */

        switch ($validated['crop']) {
            case '1:1':
                $processedImage->cover(
                    600,
                    600
                );
                break;

            case '4:3':
                $processedImage->cover(
                    800,
                    600
                );
                break;

            case '3:4':
                $processedImage->cover(
                    600,
                    800
                );
                break;

            case '16:9':
                $processedImage->cover(
                    800,
                    450
                );
                break;

            case 'original':
            default:
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Resize
        |--------------------------------------------------------------------------
        */

        switch ($validated['resize']) {
            case '300x300':
                $processedImage->resize(
                    300,
                    300
                );
                break;

            case '600x400':
                $processedImage->resize(
                    600,
                    400
                );
                break;

            case '800x600':
                $processedImage->resize(
                    800,
                    600
                );
                break;

            case 'original':
            default:
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Rotation
        |--------------------------------------------------------------------------
        */

        $rotation = (int) $validated['rotation'];

        if ($rotation !== 0) {
            $processedImage->rotate(
                $rotation
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Brightness
        |--------------------------------------------------------------------------
        */

        $brightness = (int) $validated['brightness'];

        if ($brightness !== 0) {
            $processedImage->brightness(
                $brightness
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Contrast
        |--------------------------------------------------------------------------
        */

        $contrast = (int) $validated['contrast'];

        if ($contrast !== 0) {
            $processedImage->contrast(
                $contrast
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Grayscale
        |--------------------------------------------------------------------------
        */

        if ($validated['grayscale'] === 'yes') {
            $processedImage->greyscale();
        }

        /*
        |--------------------------------------------------------------------------
        | Blur
        |--------------------------------------------------------------------------
        */

        $blur = (int) $validated['blur'];

        if ($blur > 0) {
            $processedImage->blur(
                $blur
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sharpen
        |--------------------------------------------------------------------------
        */

        $sharpen = (int) $validated['sharpen'];

        if ($sharpen > 0) {
            $processedImage->sharpen(
                $sharpen
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Horizontal / Vertical Mirror
        |--------------------------------------------------------------------------
        */

        switch ($validated['mirror']) {
            case 'horizontal':
                $processedImage->flop();
                break;

            case 'vertical':
                $processedImage->flip();
                break;

            case 'none':
            default:
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Invert Colors
        |--------------------------------------------------------------------------
        */

        if ($validated['invert'] === 'yes') {
            $processedImage->invert();
        }

        /*
        |--------------------------------------------------------------------------
        | Watermark
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['watermark']) &&
            trim($validated['watermark']) !== ''
        ) {
            $watermarkText = trim(
                $validated['watermark']
            );

            $processedImage->text(
                $watermarkText,
                20,
                20,
                function ($font) {
                    $font->size(24);
                    $font->color('#ffffff');
                    $font->stroke(
                        '#000000',
                        2
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Output Format
        |--------------------------------------------------------------------------
        */

        $format = $validated['format'];

        $quality = (int) $validated['quality'];

        $extension = $originalExtension;

        /*
        |--------------------------------------------------------------------------
        | Encode image
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Intervention Image installed in this project expects:
        |
        | toPng(bool $interlaced = false)
        |
        | Therefore we MUST NOT pass the numeric quality/compression
        | value to toPng().
        |
        */

        switch ($format) {
            /*
            |--------------------------------------------------------------------------
            | JPG
            |--------------------------------------------------------------------------
            */

            case 'jpg':

                $encodedImage =
                    $processedImage->toJpeg(
                        $quality
                    );

                $extension = 'jpg';

                break;

            /*
            |--------------------------------------------------------------------------
            | PNG
            |--------------------------------------------------------------------------
            */

            case 'png':

                $encodedImage =
                    $processedImage->toPng(
                        false
                    );

                $extension = 'png';

                break;

            /*
            |--------------------------------------------------------------------------
            | WEBP
            |--------------------------------------------------------------------------
            */

            case 'webp':

                $encodedImage =
                    $processedImage->toWebp(
                        $quality
                    );

                $extension = 'webp';

                break;

            /*
            |--------------------------------------------------------------------------
            | Original
            |--------------------------------------------------------------------------
            */

            case 'original':
            default:

                switch ($originalExtension) {
                    case 'jpg':
                    case 'jpeg':

                        $encodedImage =
                            $processedImage->toJpeg(
                                $quality
                            );

                        $extension = 'jpg';

                        break;

                    case 'png':

                        $encodedImage =
                            $processedImage->toPng(
                                false
                            );

                        $extension = 'png';

                        break;

                    case 'webp':

                        $encodedImage =
                            $processedImage->toWebp(
                                $quality
                            );

                        $extension = 'webp';

                        break;

                    default:

                        $encodedImage =
                            $processedImage->toJpeg(
                                $quality
                            );

                        $extension = 'jpg';

                        break;
                }

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Processed filename
        |--------------------------------------------------------------------------
        */

        $processedFileName =
            pathinfo(
                $originalFileName,
                PATHINFO_FILENAME
            ) .
            '_processed_' .
            now()->format('Ymd_His') .
            '.' .
            $extension;

        $processedFullPath =
            $processedPath .
            DIRECTORY_SEPARATOR .
            $processedFileName;

        /*
        |--------------------------------------------------------------------------
        | Save processed image
        |--------------------------------------------------------------------------
        */

        $encodedImage->save(
            $processedFullPath
        );

        /*
        |--------------------------------------------------------------------------
        | Processed file information
        |--------------------------------------------------------------------------
        */

        $processedFileSize =
            File::size(
                $processedFullPath
            );

        $processedWidth =
            $processedImage->width();

        $processedHeight =
            $processedImage->height();

        /*
        |--------------------------------------------------------------------------
        | Store result information in session
        |--------------------------------------------------------------------------
        */

        session([
            'image_result' => [
                'original' => $originalFileName,

                'thumbnail' => $thumbnailFileName,

                'processed' => $processedFileName,

                'crop' => $validated['crop'],

                'resize' => $validated['resize'],

                'rotation' => $validated['rotation'],

                'watermark' =>
                    $validated['watermark'] ?? '',

                'format' => $format,

                'quality' => $quality,

                'auto_orientation' =>
                    $validated['auto_orientation'],

                'brightness' =>
                    $brightness,

                'contrast' =>
                    $contrast,

                'grayscale' =>
                    $validated['grayscale'],

                'blur' =>
                    $blur,

                'sharpen' =>
                    $sharpen,

                'mirror' =>
                    $validated['mirror'],

                'invert' =>
                    $validated['invert'],

                'width' =>
                    $processedWidth,

                'height' =>
                    $processedHeight,

                'file_size' =>
                    $processedFileSize,

                'file_size_kb' =>
                    round(
                        $processedFileSize / 1024,
                        2
                    ),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('image.index')
            ->with(
                'success',
                'Image processed successfully.'
            );
    }
}