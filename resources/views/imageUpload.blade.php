<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laravel 12 Intervention Image</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-5 mb-5">

    <div class="card shadow">

        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <div class="card-header bg-dark text-white">

            <h4 class="mb-0">
                Laravel 12 Intervention Image
            </h4>

            <small>
                Upload, Crop, Resize, Rotate, Watermark,
                Optimize & Advanced Effects
            </small>

        </div>


        <div class="card-body">

            {{-- ========================================================= --}}
            {{-- Get Processing Result --}}
            {{-- ========================================================= --}}

            @php

                $result = session('image_result', []);

            @endphp


            {{-- ========================================================= --}}
            {{-- Validation Errors --}}
            {{-- ========================================================= --}}

            @if ($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        Please fix the following errors:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- Success Message --}}
            {{-- ========================================================= --}}

            @if (session('success'))

                <div class="alert alert-success">

                    {{ session('success') }}

                </div>


                {{-- ===================================================== --}}
                {{-- Image Results --}}
                {{-- ===================================================== --}}

                @if (!empty($result))

                    <div class="row g-4">


                        {{-- ================================================= --}}
                        {{-- Original Image --}}
                        {{-- ================================================= --}}

                        <div class="col-md-4">

                            <div class="card h-100">

                                <div class="card-header bg-primary text-white">

                                    Original Image

                                </div>

                                <div class="card-body text-center">

                                    @if (!empty($result['original']))

                                        <img
                                            src="{{ asset('images/' . $result['original']) }}"
                                            class="img-fluid rounded"
                                            style="max-height: 300px;"
                                            alt="Original Image"
                                        >

                                    @else

                                        <p class="text-muted">
                                            Original image not available.
                                        </p>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- Thumbnail --}}
                        {{-- ================================================= --}}

                        <div class="col-md-4">

                            <div class="card h-100">

                                <div class="card-header bg-secondary text-white">

                                    Thumbnail (100 × 100)

                                </div>

                                <div class="card-body text-center">

                                    @if (!empty($result['thumbnail']))

                                        <img
                                            src="{{ asset('images/thumbnail/' . $result['thumbnail']) }}"
                                            width="100"
                                            height="100"
                                            class="rounded"
                                            alt="Thumbnail"
                                        >

                                    @else

                                        <p class="text-muted">
                                            Thumbnail not available.
                                        </p>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- Processed Image --}}
                        {{-- ================================================= --}}

                        <div class="col-md-4">

                            <div class="card h-100">

                                <div class="card-header bg-success text-white">

                                    Processed & Optimized Image

                                </div>

                                <div class="card-body text-center">

                                    @if (!empty($result['processed']))

                                        <img
                                            src="{{ asset('images/processed/' . $result['processed']) }}"
                                            class="img-fluid rounded"
                                            style="max-height: 300px;"
                                            alt="Processed Image"
                                        >

                                    @else

                                        <p class="text-muted">
                                            Processed image not available.
                                        </p>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Processing Information --}}
                    {{-- ===================================================== --}}

                    <div class="card mt-4">

                        <div class="card-header">

                            <strong>
                                Processing & Optimization Details
                            </strong>

                        </div>

                        <div class="card-body">

                            <div class="row g-3">


                                {{-- Crop --}}

                                <div class="col-md-3">

                                    <strong>
                                        Crop:
                                    </strong>

                                    <br>

                                    <span class="badge bg-primary">

                                        {{ $result['crop'] ?? 'original' }}

                                    </span>

                                </div>


                                {{-- Resize --}}

                                <div class="col-md-3">

                                    <strong>
                                        Resize:
                                    </strong>

                                    <br>

                                    <span class="badge bg-primary">

                                        {{ $result['resize'] ?? 'original' }}

                                    </span>

                                </div>


                                {{-- Rotation --}}

                                <div class="col-md-3">

                                    <strong>
                                        Rotation:
                                    </strong>

                                    <br>

                                    <span class="badge bg-warning text-dark">

                                        {{ $result['rotation'] ?? 0 }}°

                                    </span>

                                </div>


                                {{-- Format --}}

                                <div class="col-md-3">

                                    <strong>
                                        Output Format:
                                    </strong>

                                    <br>

                                    <span class="badge bg-info text-dark">

                                        {{ strtoupper($result['format'] ?? 'original') }}

                                    </span>

                                </div>


                                {{-- Quality --}}

                                <div class="col-md-3">

                                    <strong>
                                        Quality:
                                    </strong>

                                    <br>

                                    <span class="badge bg-dark">

                                        {{ $result['quality'] ?? 80 }}%

                                    </span>

                                </div>


                                {{-- Watermark --}}

                                <div class="col-md-5">

                                    <strong>
                                        Watermark:
                                    </strong>

                                    <br>

                                    @if (!empty($result['watermark']))

                                        <span class="badge bg-success">

                                            {{ $result['watermark'] }}

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            None

                                        </span>

                                    @endif

                                </div>


                                {{-- File Size --}}

                                <div class="col-md-4">

                                    <strong>
                                        Processed File Size:
                                    </strong>

                                    <br>

                                    <span class="badge bg-success">

                                        {{ number_format((float) ($result['file_size_kb'] ?? 0), 2) }}
                                        KB

                                    </span>

                                </div>


                                {{-- Final Dimensions --}}

                                <div class="col-md-4">

                                    <strong>
                                        Final Dimensions:
                                    </strong>

                                    <br>

                                    <span class="badge bg-primary">

                                        {{ $result['width'] ?? 0 }}
                                        ×
                                        {{ $result['height'] ?? 0 }}

                                    </span>

                                </div>


                                {{-- Auto Orientation --}}

                                <div class="col-md-4">

                                    <strong>
                                        Auto Orientation:
                                    </strong>

                                    <br>

                                    <span class="badge bg-info text-dark">

                                        {{ strtoupper($result['auto_orientation'] ?? 'no') }}

                                    </span>

                                </div>


                                {{-- Brightness --}}

                                <div class="col-md-4">

                                    <strong>
                                        Brightness:
                                    </strong>

                                    <br>

                                    <span class="badge bg-warning text-dark">

                                        {{ $result['brightness'] ?? 0 }}

                                    </span>

                                </div>


                                {{-- Contrast --}}

                                <div class="col-md-4">

                                    <strong>
                                        Contrast:
                                    </strong>

                                    <br>

                                    <span class="badge bg-warning text-dark">

                                        {{ $result['contrast'] ?? 0 }}

                                    </span>

                                </div>


                                {{-- Grayscale --}}

                                <div class="col-md-4">

                                    <strong>
                                        Grayscale:
                                    </strong>

                                    <br>

                                    <span class="badge bg-secondary">

                                        {{ strtoupper($result['grayscale'] ?? 'no') }}

                                    </span>

                                </div>


                                {{-- Blur --}}

                                <div class="col-md-4">

                                    <strong>
                                        Blur:
                                    </strong>

                                    <br>

                                    <span class="badge bg-secondary">

                                        {{ $result['blur'] ?? 0 }}

                                    </span>

                                </div>


                                {{-- Sharpen --}}

                                <div class="col-md-4">

                                    <strong>
                                        Sharpen:
                                    </strong>

                                    <br>

                                    <span class="badge bg-secondary">

                                        {{ $result['sharpen'] ?? 0 }}

                                    </span>

                                </div>


                                {{-- Mirror --}}

                                <div class="col-md-4">

                                    <strong>
                                        Mirror:
                                    </strong>

                                    <br>

                                    <span class="badge bg-dark">

                                        {{ strtoupper($result['mirror'] ?? 'none') }}

                                    </span>

                                </div>


                                {{-- Invert --}}

                                <div class="col-md-4">

                                    <strong>
                                        Invert:
                                    </strong>

                                    <br>

                                    <span class="badge bg-dark">

                                        {{ strtoupper($result['invert'] ?? 'no') }}

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- Download --}}
                    {{-- ===================================================== --}}

                    @if (!empty($result['processed']))

                        <div class="text-center mt-4">

                            <a
                                href="{{ asset('images/processed/' . $result['processed']) }}"
                                download
                                class="btn btn-primary"
                            >

                                ⬇️ Download Processed Image

                            </a>

                        </div>

                    @endif


                    <hr class="my-4">

                @endif

            @endif


            {{-- ========================================================= --}}
            {{-- Upload Form --}}
            {{-- ========================================================= --}}

            <form
                action="{{ route('image.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf


                {{-- ===================================================== --}}
                {{-- Image Upload --}}
                {{-- ===================================================== --}}

                <div class="mb-4">

                    <label class="form-label fw-bold">

                        Select Image

                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small class="text-muted">

                        JPG, JPEG, PNG or WEBP — Maximum 2 MB

                    </small>

                </div>


                {{-- ===================================================== --}}
                {{-- Existing Features --}}
                {{-- ===================================================== --}}

                <div class="row">


                    {{-- Crop --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            ✂️ Crop & Aspect Ratio

                        </label>

                        <select
                            name="crop"
                            class="form-select"
                            required
                        >

                            <option value="original">
                                Original — No Crop
                            </option>

                            <option value="1:1">
                                1:1 — Square
                            </option>

                            <option value="4:3">
                                4:3 — Landscape
                            </option>

                            <option value="3:4">
                                3:4 — Portrait
                            </option>

                            <option value="16:9">
                                16:9 — Wide
                            </option>

                        </select>

                    </div>


                    {{-- Resize --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            📐 Custom Resize

                        </label>

                        <select
                            name="resize"
                            class="form-select"
                            required
                        >

                            <option value="original">
                                Original — No Resize
                            </option>

                            <option value="300x300">
                                300 × 300
                            </option>

                            <option value="600x400">
                                600 × 400
                            </option>

                            <option value="800x600">
                                800 × 600
                            </option>

                        </select>

                    </div>


                    {{-- Rotation --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            🔄 Image Rotation

                        </label>

                        <select
                            name="rotation"
                            class="form-select"
                            required
                        >

                            <option value="0">
                                No Rotation
                            </option>

                            <option value="90">
                                Rotate 90°
                            </option>

                            <option value="180">
                                Rotate 180°
                            </option>

                            <option value="270">
                                Rotate 270°
                            </option>

                        </select>

                    </div>


                    {{-- Watermark --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            💧 Text Watermark

                        </label>

                        <input
                            type="text"
                            name="watermark"
                            class="form-control"
                            maxlength="50"
                            placeholder="© Excelsior Technologies"
                        >

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- New Features Row 1 --}}
                {{-- ===================================================== --}}

                <div class="card mt-3">

                    <div class="card-header bg-primary text-white">

                        <strong>
                            🛠️ Advanced Image Effects
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="row">


                            {{-- Auto Orientation --}}

                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">

                                    1️⃣ EXIF Auto Orientation

                                </label>

                                <select
                                    name="auto_orientation"
                                    class="form-select"
                                    required
                                >

                                    <option value="yes">
                                        Yes — Auto Orient
                                    </option>

                                    <option value="no">
                                        No — Keep Original
                                    </option>

                                </select>

                                <small class="text-muted">

                                    Correct camera orientation using EXIF.

                                </small>

                            </div>


                            {{-- Brightness --}}

                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">

                                    2️⃣ Brightness

                                </label>

                                <input
                                    type="range"
                                    name="brightness"
                                    id="brightness"
                                    class="form-range"
                                    min="-100"
                                    max="100"
                                    value="0"
                                    oninput="document.getElementById('brightnessValue').innerText = this.value"
                                >

                                <div class="d-flex justify-content-between">

                                    <small>
                                        Dark
                                    </small>

                                    <strong id="brightnessValue">
                                        0
                                    </strong>

                                    <small>
                                        Bright
                                    </small>

                                </div>

                            </div>


                            {{-- Contrast --}}

                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">

                                    3️⃣ Contrast

                                </label>

                                <input
                                    type="range"
                                    name="contrast"
                                    id="contrast"
                                    class="form-range"
                                    min="-100"
                                    max="100"
                                    value="0"
                                    oninput="document.getElementById('contrastValue').innerText = this.value"
                                >

                                <div class="d-flex justify-content-between">

                                    <small>
                                        Low
                                    </small>

                                    <strong id="contrastValue">
                                        0
                                    </strong>

                                    <small>
                                        High
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- New Features Row 2 --}}
                {{-- ===================================================== --}}

                <div class="card mt-3">

                    <div class="card-header bg-success text-white">

                        <strong>
                            🎨 Color & Detail Effects
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="row">


                            {{-- Grayscale --}}

                            <div class="col-md-3 mb-3">

                                <label class="form-label fw-bold">

                                    4️⃣ Grayscale

                                </label>

                                <select
                                    name="grayscale"
                                    class="form-select"
                                    required
                                >

                                    <option value="no">
                                        No
                                    </option>

                                    <option value="yes">
                                        Yes — Black & White
                                    </option>

                                </select>

                            </div>


                            {{-- Blur --}}

                            <div class="col-md-3 mb-3">

                                <label class="form-label fw-bold">

                                    5️⃣ Blur

                                </label>

                                <select
                                    name="blur"
                                    class="form-select"
                                    required
                                >

                                    <option value="0">
                                        No Blur
                                    </option>

                                    <option value="2">
                                        Low — 2
                                    </option>

                                    <option value="5">
                                        Medium — 5
                                    </option>

                                    <option value="10">
                                        Strong — 10
                                    </option>

                                    <option value="20">
                                        Very Strong — 20
                                    </option>

                                </select>

                            </div>


                            {{-- Sharpen --}}

                            <div class="col-md-3 mb-3">

                                <label class="form-label fw-bold">

                                    6️⃣ Sharpen

                                </label>

                                <select
                                    name="sharpen"
                                    class="form-select"
                                    required
                                >

                                    <option value="0">
                                        No Sharpen
                                    </option>

                                    <option value="2">
                                        Low — 2
                                    </option>

                                    <option value="5">
                                        Medium — 5
                                    </option>

                                    <option value="10">
                                        Strong — 10
                                    </option>

                                    <option value="20">
                                        Very Strong — 20
                                    </option>

                                </select>

                            </div>


                            {{-- Invert --}}

                            <div class="col-md-3 mb-3">

                                <label class="form-label fw-bold">

                                    9️⃣ Invert Colors

                                </label>

                                <select
                                    name="invert"
                                    class="form-select"
                                    required
                                >

                                    <option value="no">
                                        No
                                    </option>

                                    <option value="yes">
                                        Yes — Invert
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- New Feature Row 3 --}}
                {{-- ===================================================== --}}

                <div class="card mt-3">

                    <div class="card-header bg-warning text-dark">

                        <strong>
                            ↔️ Mirror Effects
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="row">


                            {{-- Mirror --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">

                                    7️⃣ 8️⃣ Image Mirror

                                </label>

                                <select
                                    name="mirror"
                                    class="form-select"
                                    required
                                >

                                    <option value="none">
                                        No Mirror
                                    </option>

                                    <option value="horizontal">
                                        Horizontal — Left ↔ Right
                                    </option>

                                    <option value="vertical">
                                        Vertical — Top ↕ Bottom
                                    </option>

                                </select>

                                <small class="text-muted">

                                    Horizontal uses a left/right mirror.
                                    Vertical uses a top/bottom mirror.

                                </small>

                            </div>


                            {{-- Feature Summary --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">

                                    ✨ New Effects

                                </label>

                                <div>

                                    <span class="badge bg-primary me-1">
                                        Auto Orientation
                                    </span>

                                    <span class="badge bg-success me-1">
                                        Brightness
                                    </span>

                                    <span class="badge bg-info text-dark me-1">
                                        Contrast
                                    </span>

                                    <span class="badge bg-secondary me-1">
                                        Grayscale
                                    </span>

                                    <span class="badge bg-dark me-1">
                                        Blur
                                    </span>

                                    <span class="badge bg-dark me-1">
                                        Sharpen
                                    </span>

                                    <span class="badge bg-warning text-dark me-1">
                                        Mirror
                                    </span>

                                    <span class="badge bg-danger me-1">
                                        Invert
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- Output Format & Quality --}}
                {{-- ===================================================== --}}

                <div class="row mt-3">


                    {{-- Output Format --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Output Format

                        </label>

                        <select
                            name="format"
                            class="form-select"
                            required
                        >

                            <option value="original">
                                Original Format
                            </option>

                            <option value="jpg">
                                JPEG
                            </option>

                            <option value="png">
                                PNG
                            </option>

                            <option value="webp">
                                WebP ⭐
                            </option>

                        </select>

                        <small class="text-muted">

                            Convert & optimize image.

                        </small>

                    </div>


                    {{-- Quality --}}

                    <div class="col-md-6 mb-3">

                        <label
                            for="quality"
                            class="form-label fw-bold"
                        >

                            Image Quality

                        </label>

                        <input
                            type="range"
                            name="quality"
                            id="quality"
                            class="form-range"
                            min="10"
                            max="100"
                            value="80"
                            oninput="document.getElementById('qualityValue').innerText = this.value + '%'"
                        >

                        <div class="d-flex justify-content-between">

                            <small>
                                Lower Size
                            </small>

                            <strong id="qualityValue">
                                80%
                            </strong>

                            <small>
                                Higher Quality
                            </small>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- Submit --}}
                {{-- ===================================================== --}}

                <div class="d-grid mt-4">

                    <button
                        type="submit"
                        class="btn btn-success btn-lg"
                    >

                        🚀 Upload & Process Image

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- ================================================================ --}}
{{-- Bootstrap JS --}}
{{-- ================================================================ --}}

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>