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
        ]);

        $manager = new ImageManager(new Driver());

        $imageFile = $request->file('image');
        $imageName = time() . '-' . $imageFile->getClientOriginalName();

        $mainPath = public_path('images/');
        $thumbPath = public_path('images/thumbnail/');

        // Create folders if not exist
        File::ensureDirectoryExists($mainPath, 0755, true);
        File::ensureDirectoryExists($thumbPath, 0755, true);

        // Save original image
        $image = $manager->read($imageFile);
        $image->save($mainPath . $imageName);

        // Save thumbnail
        $image->resize(100, 100);
        $image->save($thumbPath . $imageName);

        return back()
            ->with('success', 'Image uploaded successfully')
            ->with('imageName', $imageName);
    }
}
