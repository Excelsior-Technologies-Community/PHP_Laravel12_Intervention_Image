<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function index(): View
    {
        return view('imageUpload');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'crop' => 'required|in:original,1:1,4:3,3:4,16:9',

            'resize' => 'required|in:300x300,600x400,800x600',

            'rotation' => 'required|in:0,90,180,270',

            'watermark' => 'nullable|string|max:50',

            'format' => 'required|in:original,jpg,png,webp',

            'quality' => 'required|integer|min:10|max:100',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Intervention Image Manager
        |--------------------------------------------------------------------------
        */

        $manager = new ImageManager(new Driver());

        $imageFile = $request->file('image');

        /*
        |--------------------------------------------------------------------------
        | Generate Unique Filename
        |--------------------------------------------------------------------------
        */

        $originalExtension = strtolower(
            $imageFile->getClientOriginalExtension()
        );

        $baseName = pathinfo(
            $imageFile->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $uniqueName = time() . '-' . uniqid();

        /*
        |--------------------------------------------------------------------------
        | Directories
        |--------------------------------------------------------------------------
        */

        $mainPath = public_path('images/');
        $thumbPath = public_path('images/thumbnail/');
        $processedPath = public_path('images/processed/');

        File::ensureDirectoryExists(
            $mainPath,
            0755,
            true
        );

        File::ensureDirectoryExists(
            $thumbPath,
            0755,
            true
        );

        File::ensureDirectoryExists(
            $processedPath,
            0755,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Save Original Image
        |--------------------------------------------------------------------------
        */

        $originalImageName =
            $uniqueName .
            '-' .
            $baseName .
            '.' .
            $originalExtension;

        $originalImage = $manager->read($imageFile);

        $originalImage->save(
            $mainPath . $originalImageName
        );

        /*
        |--------------------------------------------------------------------------
        | Generate 100 x 100 Thumbnail
        |--------------------------------------------------------------------------
        */

        $thumbnail = $manager->read($imageFile);

        $thumbnail->resize(
            100,
            100
        );

        $thumbnail->save(
            $thumbPath . $originalImageName
        );

        /*
        |--------------------------------------------------------------------------
        | Read Image For Processing
        |--------------------------------------------------------------------------
        */

        $processedImage = $manager->read($imageFile);

        /*
        |--------------------------------------------------------------------------
        | 1. Image Crop & Aspect Ratio
        |--------------------------------------------------------------------------
        */

        $selectedCrop = $request->crop;

        if ($selectedCrop !== 'original') {

            /*
            |--------------------------------------------------------------
            | Get Original Image Dimensions
            |--------------------------------------------------------------
            */

            $imageWidth = $processedImage->width();
            $imageHeight = $processedImage->height();

            /*
            |--------------------------------------------------------------
            | Determine Target Aspect Ratio
            |--------------------------------------------------------------
            */

            switch ($selectedCrop) {

                case '1:1':

                    $targetRatio = 1 / 1;

                    break;

                case '4:3':

                    $targetRatio = 4 / 3;

                    break;

                case '3:4':

                    $targetRatio = 3 / 4;

                    break;

                case '16:9':

                    $targetRatio = 16 / 9;

                    break;

                default:

                    $targetRatio = null;
            }

            /*
            |--------------------------------------------------------------
            | Calculate Center Crop
            |--------------------------------------------------------------
            */

            if ($targetRatio !== null) {

                $currentRatio =
                    $imageWidth / $imageHeight;

                if ($currentRatio > $targetRatio) {

                    /*
                    | Image is wider than target.
                    | Reduce width.
                    */

                    $cropHeight = $imageHeight;

                    $cropWidth = (int) round(
                        $imageHeight * $targetRatio
                    );

                } else {

                    /*
                    | Image is taller than target.
                    | Reduce height.
                    */

                    $cropWidth = $imageWidth;

                    $cropHeight = (int) round(
                        $imageWidth / $targetRatio
                    );
                }

                /*
                |----------------------------------------------------------
                | Center Crop
                |----------------------------------------------------------
                */

                $offsetX = (int) round(
                    ($imageWidth - $cropWidth) / 2
                );

                $offsetY = (int) round(
                    ($imageHeight - $cropHeight) / 2
                );

                $processedImage->crop(
                    $cropWidth,
                    $cropHeight,
                    $offsetX,
                    $offsetY
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Custom Resize
        |--------------------------------------------------------------------------
        */

        [$width, $height] = explode(
            'x',
            $request->resize
        );

        $processedImage->resize(
            (int) $width,
            (int) $height
        );

        /*
        |--------------------------------------------------------------------------
        | 3. Image Rotation
        |--------------------------------------------------------------------------
        */

        if ((int) $request->rotation !== 0) {

            $processedImage->rotate(
                (int) $request->rotation
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Text Watermark
        |--------------------------------------------------------------------------
        */

        if ($request->filled('watermark')) {

            $watermarkText = $request->watermark;

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

                    $font->align('left');

                    $font->valign('top');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Format Conversion & Optimization
        |--------------------------------------------------------------------------
        */

        $selectedFormat = $request->format;

        $quality = (int) $request->quality;

        /*
        |--------------------------------------------------------------------------
        | Determine Output Format
        |--------------------------------------------------------------------------
        */

        if ($selectedFormat === 'original') {

            $outputFormat = $originalExtension;

        } else {

            $outputFormat = $selectedFormat;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Processed Filename
        |--------------------------------------------------------------------------
        */

        $processedImageName =
            $uniqueName .
            '-' .
            $baseName .
            '-processed.' .
            $outputFormat;

        $processedFullPath =
            $processedPath .
            $processedImageName;

        /*
        |--------------------------------------------------------------------------
        | Encode According To Selected Format
        |--------------------------------------------------------------------------
        */

        switch ($outputFormat) {

            case 'jpg':

                $encodedImage =
                    $processedImage->toJpeg(
                        $quality
                    );

                break;

            case 'jpeg':

                $encodedImage =
                    $processedImage->toJpeg(
                        $quality
                    );

                break;

            case 'png':

                /*
                | PNG uses compression level 0-9.
                | Convert quality 10-100 into PNG compression.
                */

                $compressionLevel =
                    (int) round(
                        (100 - $quality) / 100 * 9
                    );

                $encodedImage =
                    $processedImage->toPng(
                        $compressionLevel
                    );

                break;

            case 'webp':

                $encodedImage =
                    $processedImage->toWebp(
                        $quality
                    );

                break;

            default:

                $encodedImage =
                    $processedImage->encode();
        }

        /*
        |--------------------------------------------------------------------------
        | Save Optimized / Converted Image
        |--------------------------------------------------------------------------
        */

        File::put(
            $processedFullPath,
            (string) $encodedImage
        );

        /*
        |--------------------------------------------------------------------------
        | Get Processed File Size
        |--------------------------------------------------------------------------
        */

        $processedFileSize =
            File::size(
                $processedFullPath
            );

        /*
        |--------------------------------------------------------------------------
        | Return Result
        |--------------------------------------------------------------------------
        */

        return back()
            ->with(
                'success',
                'Image uploaded, cropped, processed and optimized successfully.'
            )
            ->with(
                'imageName',
                $originalImageName
            )
            ->with(
                'processedImageName',
                $processedImageName
            )
            ->with(
                'crop',
                $selectedCrop
            )
            ->with(
                'resize',
                $request->resize
            )
            ->with(
                'rotation',
                $request->rotation
            )
            ->with(
                'watermark',
                $request->watermark
            )
            ->with(
                'format',
                strtoupper($outputFormat)
            )
            ->with(
                'quality',
                $quality
            )
            ->with(
                'processedFileSize',
                $processedFileSize
            );
    }
}