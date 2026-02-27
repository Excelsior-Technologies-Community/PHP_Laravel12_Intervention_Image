# PHP_Laravel12_Intervention_Image

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.2%2B-blue" alt="PHP Version">
    <img src="https://img.shields.io/badge/Laravel-12-red" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Intervention_Image-v3-green" alt="Intervention Image">
    <img src="https://img.shields.io/badge/Image%20Upload-Thumbnail%20Support-orange" alt="Feature">
    <img src="https://img.shields.io/badge/Status-Stable-brightgreen" alt="Project Status">
    <img src="https://img.shields.io/badge/License-MIT-lightgrey" alt="License">
</p>


## Overview

This project demonstrates how to upload images and generate thumbnail images in **Laravel 12** using **Intervention Image v3**. It follows the latest best practices, avoids deprecated facade-based syntax, and is suitable for real-world use cases such as e-commerce product images, profile pictures, and banners.

The implementation is simple, clean, and fully compatible with Laravel 12.

---

## Features

* Image upload using Laravel 12
* Thumbnail generation using Intervention Image v3
* No facade usage (recommended approach)
* Automatic directory creation
* Server-side validation
* Bootstrap-based UI
* Ready for e-commerce and production use

---

## Folder Structure

```
example-app/
├── app/
│   └── Http/
│       └── Controllers/
│           └── ImageController.php
├── public/
│   └── images/
│       ├── thumbnail/
│       └── (uploaded images)
├── resources/
│   └── views/
│       └── imageUpload.blade.php
├── routes/
│   └── web.php
└── composer.json
```

---

## Requirements

* PHP 8.2+
* Laravel 12
* Composer
* GD extension enabled (recommended)

---

## Step 1: Create Laravel Project (Optional)

If you already have a Laravel 12 project, you can skip this step.

```
composer create-project laravel/laravel example-app
```
---

## Step 2: Install Intervention Image

Laravel 12 uses Intervention Image v3, which does not rely on facades.

```
composer require intervention/image
```
No service provider or alias configuration is required.

---

## Step 3: Create Routes

routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('image-upload', [ImageController::class, 'index']);
Route::post('image-upload', [ImageController::class, 'store'])->name('image.store');
```

---

## Step 4: Create Controller

Generate a controller using Artisan:
```
php artisan make:controller ImageController
```
app/Http/Controllers/ImageController.php

```php
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
```

---

## Step 5: Create View File

resources/views/imageUpload.blade.php

```html
<!DOCTYPE html>
<html>
<head>
<title>Laravel 12 Image Upload</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>


<div class="container mt-5">
<div class="card">
<div class="card-header">Laravel 12 Intervention Image</div>
<div class="card-body">


<!-- Display validation errors -->
@if ($errors->any())
<div class="alert alert-danger">
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif


<!-- Display success message -->
@if (session('success'))
<div class="alert alert-success">
{{ session('success') }}
</div>


<div class="row">
<div class="col-md-6">
<strong>Original Image</strong><br>
<img src="/images/{{ session('imageName') }}" width="300">
</div>
<div class="col-md-6">
<strong>Thumbnail Image</strong><br>
<img src="/images/thumbnail/{{ session('imageName') }}">
</div>
</div>
<hr>
@endif


<!-- Upload form -->
<form action="{{ route('image.store') }}" method="POST" enctype="multipart/form-data">
@csrf


<div class="mb-3">
<label class="form-label">Select Image</label>
<input type="file" name="image" class="form-control">
</div>


<button class="btn btn-success">Upload</button>
</form>
</div>
</div>
</div>


</body>
</html>
```

---

## Step 6: Run Application

```
php artisan serve
```

Open the following URL in your browser:

```
http://localhost:8000/image-upload
```
---

## Output

<img width="1626" height="769" alt="Screenshot 2026-01-21 165729" src="https://github.com/user-attachments/assets/67fae260-593a-4a07-ad55-46885ec47f6f" />


---

## Notes

* This implementation is Laravel 12 compatible
* Uses Intervention Image v3 (recommended)
* Avoids deprecated facade-based syntax
* Suitable for E-commerce product images, profile images, and banners
