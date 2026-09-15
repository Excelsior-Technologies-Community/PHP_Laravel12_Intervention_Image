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

        <div class="card-header bg-dark text-white">

            <h4 class="mb-0">
                Laravel 12 Intervention Image
            </h4>

            <small>
                Upload, Crop, Resize, Rotate, Watermark & Optimize
            </small>

        </div>


        <div class="card-body">


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

                <div class="row g-4">


                    {{-- Original Image --}}

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-header bg-primary text-white">

                                Original Image

                            </div>

                            <div class="card-body text-center">

                                <img
                                    src="{{ asset('images/' . session('imageName')) }}"
                                    class="img-fluid rounded"
                                    style="max-height: 300px;"
                                    alt="Original Image"
                                >

                            </div>

                        </div>

                    </div>


                    {{-- Thumbnail --}}

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-header bg-secondary text-white">

                                Thumbnail (100 × 100)

                            </div>

                            <div class="card-body text-center">

                                <img
                                    src="{{ asset('images/thumbnail/' . session('imageName')) }}"
                                    width="100"
                                    height="100"
                                    class="rounded"
                                    alt="Thumbnail"
                                >

                            </div>

                        </div>

                    </div>


                    {{-- Processed Image --}}

                    <div class="col-md-4">

                        <div class="card h-100">

                            <div class="card-header bg-success text-white">

                                Processed & Optimized Image

                            </div>

                            <div class="card-body text-center">

                                <img
                                    src="{{ asset('images/processed/' . session('processedImageName')) }}"
                                    class="img-fluid rounded"
                                    style="max-height: 300px;"
                                    alt="Processed Image"
                                >

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

                                @if (session('crop') === 'original')

                                    <span class="badge bg-secondary">

                                        Original

                                    </span>

                                @else

                                    <span class="badge bg-primary">

                                        {{ session('crop') }}

                                    </span>

                                @endif

                            </div>


                            {{-- Resize --}}

                            <div class="col-md-3">

                                <strong>
                                    Resize:
                                </strong>

                                <br>

                                <span class="badge bg-primary">

                                    {{ session('resize') }}

                                </span>

                            </div>


                            {{-- Rotation --}}

                            <div class="col-md-3">

                                <strong>
                                    Rotation:
                                </strong>

                                <br>

                                <span class="badge bg-warning text-dark">

                                    {{ session('rotation') }}°

                                </span>

                            </div>


                            {{-- Format --}}

                            <div class="col-md-3">

                                <strong>
                                    Output Format:
                                </strong>

                                <br>

                                <span class="badge bg-info text-dark">

                                    {{ session('format') }}

                                </span>

                            </div>


                            {{-- Quality --}}

                            <div class="col-md-3">

                                <strong>
                                    Quality:
                                </strong>

                                <br>

                                <span class="badge bg-dark">

                                    {{ session('quality') }}%

                                </span>

                            </div>


                            {{-- Watermark --}}

                            <div class="col-md-5">

                                <strong>
                                    Watermark:
                                </strong>

                                <br>

                                @if (session('watermark'))

                                    <span class="badge bg-success">

                                        {{ session('watermark') }}

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

                                    {{ number_format(session('processedFileSize') / 1024, 2) }}
                                    KB

                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- Download Processed Image --}}
                {{-- ===================================================== --}}

                <div class="text-center mt-4">

                    <a
                        href="{{ asset('images/processed/' . session('processedImageName')) }}"
                        download
                        class="btn btn-primary"
                    >

                        ⬇️ Download Processed Image

                    </a>

                </div>


                <hr class="my-4">

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


                <div class="row">


                    {{-- ================================================= --}}
                    {{-- Crop & Aspect Ratio --}}
                    {{-- ================================================= --}}

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

                        <small class="text-muted">

                            Crop image to selected ratio.

                        </small>

                    </div>


                    {{-- ================================================= --}}
                    {{-- Resize --}}
                    {{-- ================================================= --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            Custom Resize

                        </label>

                        <select
                            name="resize"
                            class="form-select"
                            required
                        >

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

                        <small class="text-muted">

                            Select dimensions.

                        </small>

                    </div>


                    {{-- ================================================= --}}
                    {{-- Rotation --}}
                    {{-- ================================================= --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            Image Rotation

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

                        <small class="text-muted">

                            Rotate image.

                        </small>

                    </div>


                    {{-- ================================================= --}}
                    {{-- Watermark --}}
                    {{-- ================================================= --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">

                            Text Watermark

                        </label>

                        <input
                            type="text"
                            name="watermark"
                            class="form-control"
                            maxlength="50"
                            placeholder="© Yash Patel"
                        >

                        <small class="text-muted">

                            Optional watermark.

                        </small>

                    </div>

                </div>


                <div class="row">


                    {{-- ================================================= --}}
                    {{-- Output Format --}}
                    {{-- ================================================= --}}

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


                    {{-- ================================================= --}}
                    {{-- Quality --}}
                    {{-- ================================================= --}}

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
                            oninput="qualityValue.innerText = this.value + '%'"
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

                <div class="d-grid">

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