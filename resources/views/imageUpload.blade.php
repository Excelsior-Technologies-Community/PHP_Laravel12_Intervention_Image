<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel 12 Intervention Image Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bs-primary-rgb: 79, 70, 229;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .studio-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            overflow: hidden;
        }
        .studio-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            color: #ffffff;
            padding: 2rem;
        }
        .section-header {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .kpi-card {
            background: #f1f5f9;
            border-radius: 0.75rem;
            padding: 1.25rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
        }
        .kpi-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }
        /* 9-Point Grid Selector */
        .pos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            max-width: 220px;
        }
        .pos-btn {
            aspect-ratio: 1;
            border: 2px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 0.8rem;
            color: #64748b;
        }
        .pos-btn:hover {
            border-color: #6366f1;
            background: #eef2ff;
            color: #4f46e5;
        }
        .pos-radio:checked + .pos-btn {
            border-color: #4f46e5;
            background: #4f46e5;
            color: #ffffff;
            font-weight: bold;
        }
        /* Split Before/After Slider */
        .split-slider-container {
            position: relative;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 0.75rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            background: #000;
            user-select: none;
            aspect-ratio: 16/9;
            max-height: 520px;
        }
        .split-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            pointer-events: none;
        }
        .split-img-before {
            clip-path: polygon(0 0, var(--split-pos, 50%) 0, var(--split-pos, 50%) 100%, 0 100%);
            z-index: 2;
        }
        .split-img-after {
            z-index: 1;
        }
        .split-divider {
            position: absolute;
            top: 0;
            bottom: 0;
            left: var(--split-pos, 50%);
            width: 3px;
            background: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            z-index: 3;
            transform: translateX(-50%);
            pointer-events: none;
        }
        .split-handle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 38px;
            height: 38px;
            background: #ffffff;
            color: #1e1b4b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            font-size: 14px;
            font-weight: bold;
        }
        .split-label {
            position: absolute;
            top: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #ffffff;
            z-index: 4;
            backdrop-filter: blur(4px);
        }
        .split-label-before {
            left: 12px;
            background: rgba(15, 23, 42, 0.75);
        }
        .split-label-after {
            right: 12px;
            background: rgba(16, 185, 129, 0.85);
        }
        .split-range-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: ew-resize;
            z-index: 5;
            margin: 0;
        }
        /* Code Box */
        .code-box {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 0.85rem;
            position: relative;
            overflow-x: auto;
        }
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="studio-card">
        
        {{-- Header --}}
        <div class="studio-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                        <span>🖼️</span> Laravel 12 Intervention Image Studio
                    </h2>
                    <p class="mb-0 text-white-50 small">
                        Dual-Mode Watermarking, Target Size Compression, Responsive Multi-Size Breakpoints & Modern AVIF/WebP Formats
                    </p>
                </div>
                <div>
                    <span class="badge bg-white text-primary px-3 py-2 fw-bold">Intervention Image v3</span>
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">

            @php
                $result = session('image_result', []);
            @endphp

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please check form inputs:</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ================================================================= --}}
            {{-- RESULTS SECTION (IF PROCESSED) --}}
            {{-- ================================================================= --}}
            @if (!empty($result))
                <div class="bg-light p-4 rounded-4 border mb-5">
                    
                    {{-- 1. Analytics KPI Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-3">
                            <div class="kpi-card">
                                <div class="kpi-label">Original Size</div>
                                <div class="kpi-value text-secondary">{{ $result['original_size_kb'] }} KB</div>
                                <small class="text-muted">Uploaded Asset</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="kpi-card">
                                <div class="kpi-label">Optimized Size</div>
                                <div class="kpi-value text-success">{{ $result['processed_size_kb'] }} KB</div>
                                <small class="badge bg-success-subtle text-success">{{ strtoupper($result['format']) }} • Q: {{ $result['quality'] }}%</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="kpi-card">
                                <div class="kpi-label">Storage Saved</div>
                                <div class="kpi-value text-primary">{{ $result['saved_percent'] }}%</div>
                                <small class="text-muted">{{ round($result['saved_bytes'] / 1024, 1) }} KB Reduced</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="kpi-card">
                                <div class="kpi-label">Resolution</div>
                                <div class="kpi-value text-dark">{{ $result['width'] }} × {{ $result['height'] }}</div>
                                <small class="text-muted">{{ $result['crop'] !== 'original' ? 'Cropped ' . $result['crop'] : 'Standard' }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Interactive Before / After Split Slider --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="section-header mb-0">
                                <span>🔍</span> Interactive Before / After Comparison
                            </div>
                            <small class="text-muted">Drag the slider horizontally to compare original vs processed</small>
                        </div>

                        <div class="split-slider-container" id="sliderContainer" style="--split-pos: 50%;">
                            <span class="split-label split-label-before">Original ({{ $result['original_size_kb'] }} KB)</span>
                            <span class="split-label split-label-after">Processed ({{ $result['processed_size_kb'] }} KB)</span>
                            
                            {{-- Before Image --}}
                            <img src="{{ asset('images/' . $result['original']) }}" class="split-img split-img-before" alt="Original Image">
                            
                            {{-- After Image --}}
                            <img src="{{ asset('images/processed/' . $result['processed']) }}" class="split-img split-img-after" alt="Processed Image">
                            
                            {{-- Divider & Handle --}}
                            <div class="split-divider">
                                <div class="split-handle">
                                    <i class="bi bi-arrows-expand-vertical" style="transform: rotate(90deg);"></i>
                                </div>
                            </div>
                            
                            {{-- Range input for seamless dragging --}}
                            <input type="range" min="0" max="100" value="50" class="split-range-input" id="splitRange">
                        </div>
                    </div>

                    {{-- Action Buttons: Download Single & Download All ZIP --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
                        <a href="{{ asset('images/processed/' . $result['processed']) }}" download class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                            <i class="bi bi-download me-1"></i> Download Processed Image ({{ strtoupper($result['format']) }})
                        </a>

                        @if(!empty($result['zip_file']))
                            <a href="{{ route('image.download-zip', ['filename' => $result['zip_file']]) }}" class="btn btn-success px-4 py-2 rounded-pill fw-semibold shadow-sm">
                                <i class="bi bi-file-earmark-zip-fill me-1"></i> Download All Responsive Variants (.ZIP)
                            </a>
                        @endif
                    </div>

                    {{-- 3. Responsive Multi-Size Breakpoints Matrix --}}
                    @if(!empty($result['variants']))
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                                    <span class="badge bg-indigo text-white p-1">⚡</span>
                                    Generated Responsive Multi-Size Variants
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($result['variants'] as $vKey => $variant)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <strong class="text-dark">{{ $variant['label'] }}</strong>
                                                        <span class="badge bg-secondary">{{ $variant['width'] }} × {{ $variant['height'] }}</span>
                                                    </div>
                                                    <small class="text-muted d-block mb-3">Est. Size: {{ $variant['size_kb'] }} KB</small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ asset('images/variants/' . $variant['webp']) }}" target="_blank" class="btn btn-sm btn-outline-primary w-50" download>
                                                        WebP
                                                    </a>
                                                    <a href="{{ asset('images/variants/' . $variant['avif']) }}" target="_blank" class="btn btn-sm btn-outline-success w-50" download>
                                                        AVIF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 4. HTML <picture> Snippet Generator --}}
                    @if(!empty($result['picture_snippet']))
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="h6 mb-0 fw-bold">
                                    📋 Ready-to-Use HTML <code>&lt;picture&gt;</code> Responsive Code
                                </h5>
                                <button type="button" class="btn btn-sm btn-dark" onclick="copyPictureSnippet()">
                                    <i class="bi bi-clipboard me-1" id="copyIcon"></i> <span id="copyText">Copy Snippet</span>
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <pre class="code-box m-0" id="snippetContent"><code>{{ $result['picture_snippet'] }}</code></pre>
                            </div>
                        </div>
                    @endif

                </div>
            @endif


            {{-- ================================================================= --}}
            {{-- STUDIO FORM CONTROLS --}}
            {{-- ================================================================= --}}
            <form action="{{ route('image.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- 1. Image Upload Drop Area --}}
                <div class="mb-4">
                    <div class="section-header">
                        <span>📤</span> 1. Select Source Image
                    </div>
                    <div class="p-4 border border-2 border-dashed rounded-4 bg-light text-center">
                        <input type="file" name="image" id="sourceImage" class="form-control form-control-lg mx-auto mb-2" accept=".jpg,.jpeg,.png,.webp,.avif" required style="max-width: 500px;">
                        <small class="text-muted d-block">
                            Supported: JPG, PNG, WEBP, AVIF (Max 10 MB). Auto-generates responsive sizes & formats.
                        </small>
                    </div>
                </div>

                {{-- 2. Module 1: Dual-Mode Watermarking & 9-Point Positioning Matrix --}}
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <div class="section-header text-primary">
                        <span>💧</span> Module 1: Dual-Mode Watermarking & 9-Point Positioning Matrix
                    </div>

                    <div class="row g-4">
                        {{-- Watermark Mode --}}
                        <div class="col-lg-4">
                            <label class="form-label fw-bold small text-secondary">Watermark Mode</label>
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer">
                                    <input type="radio" name="watermark_mode" value="none" checked onchange="toggleWmFields('none')">
                                    <span>🚫 No Watermark</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer">
                                    <input type="radio" name="watermark_mode" value="text" onchange="toggleWmFields('text')">
                                    <span>✍️ Text Watermark</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer">
                                    <input type="radio" name="watermark_mode" value="logo" onchange="toggleWmFields('logo')">
                                    <span>🖼️ PNG Logo Upload</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer">
                                    <input type="radio" name="watermark_mode" value="tiled" onchange="toggleWmFields('tiled')">
                                    <span>🛡️ Full Diagonal Tiled Pattern</span>
                                </label>
                            </div>
                        </div>

                        {{-- Watermark Inputs --}}
                        <div class="col-lg-4" id="wmInputsCol">
                            <div id="wmTextInput" class="mb-3" style="display: none;">
                                <label class="form-label fw-bold small text-secondary">Watermark Text</label>
                                <input type="text" name="watermark_text" class="form-control" placeholder="© Excelsior Technologies" value="© COPYRIGHT PROTECTED">
                            </div>

                            <div id="wmLogoInput" class="mb-3" style="display: none;">
                                <label class="form-label fw-bold small text-secondary">Upload PNG Logo</label>
                                <input type="file" name="watermark_logo" class="form-control" accept=".png,.webp,.jpg">
                                <small class="text-muted">Transparent PNG recommended (auto-scaled)</small>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label fw-bold small text-secondary">Opacity</label>
                                    <span class="small fw-bold text-primary" id="opacityVal">80%</span>
                                </div>
                                <input type="range" name="watermark_opacity" min="10" max="100" value="80" class="form-range" oninput="document.getElementById('opacityVal').textContent = this.value + '%'">
                            </div>

                            <div>
                                <label class="form-label fw-bold small text-secondary">Edge Margin / Padding (px)</label>
                                <input type="number" name="watermark_padding" class="form-control" value="20" min="5" max="100">
                            </div>
                        </div>

                        {{-- 9-Point Grid Selector --}}
                        <div class="col-lg-4" id="wmPositionCol">
                            <label class="form-label fw-bold small text-secondary">9-Point Alignment Grid</label>
                            <div class="pos-grid">
                                <label><input type="radio" name="watermark_position" value="top-left" class="d-none pos-radio"><span class="pos-btn">TL</span></label>
                                <label><input type="radio" name="watermark_position" value="top" class="d-none pos-radio"><span class="pos-btn">TC</span></label>
                                <label><input type="radio" name="watermark_position" value="top-right" class="d-none pos-radio"><span class="pos-btn">TR</span></label>
                                <label><input type="radio" name="watermark_position" value="left" class="d-none pos-radio"><span class="pos-btn">CL</span></label>
                                <label><input type="radio" name="watermark_position" value="center" class="d-none pos-radio"><span class="pos-btn">MID</span></label>
                                <label><input type="radio" name="watermark_position" value="right" class="d-none pos-radio"><span class="pos-btn">CR</span></label>
                                <label><input type="radio" name="watermark_position" value="bottom-left" class="d-none pos-radio"><span class="pos-btn">BL</span></label>
                                <label><input type="radio" name="watermark_position" value="bottom" class="d-none pos-radio"><span class="pos-btn">BC</span></label>
                                <label><input type="radio" name="watermark_position" value="bottom-right" class="d-none pos-radio" checked><span class="pos-btn">BR</span></label>
                            </div>
                            <small class="text-muted d-block mt-2">Click to choose where watermark anchors</small>
                        </div>
                    </div>
                </div>

                {{-- 3. Module 3: Target Size Compression & Quality --}}
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <div class="section-header text-success">
                        <span>🗜️</span> Module 3: Target Size Compression & Output Format
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Compression Mode</label>
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer">
                                    <input type="radio" name="compression_mode" value="manual" checked onchange="toggleCompMode('manual')">
                                    <span>Manual Quality Slider (%)</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer">
                                    <input type="radio" name="compression_mode" value="target_size" onchange="toggleCompMode('target_size')">
                                    <span>🎯 Target File Size (Under Specified KB)</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4" id="manualQualityDiv">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-bold small text-secondary">Quality / Compression</label>
                                <span class="small fw-bold text-success" id="qualityVal">80%</span>
                            </div>
                            <input type="range" name="quality" min="10" max="100" value="80" class="form-range" oninput="document.getElementById('qualityVal').textContent = this.value + '%'">
                            <small class="text-muted">80% offers near-lossless clarity with substantial size reduction.</small>
                        </div>

                        <div class="col-md-4" id="targetKbDiv" style="display: none;">
                            <label class="form-label fw-bold small text-secondary">Target File Size (KB)</label>
                            <input type="number" name="target_kb" id="targetKbInput" class="form-control mb-2" placeholder="e.g. 200" value="200" min="20" max="5000">
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setTargetKb(100)">100 KB</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setTargetKb(200)">200 KB</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setTargetKb(500)">500 KB</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setTargetKb(1024)">1 MB</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Output Format</label>
                            <select name="format" class="form-select">
                                <option value="original">Original Format</option>
                                <option value="webp" selected>WEBP (Recommended Next-Gen)</option>
                                <option value="avif">AVIF (Ultra Compressed Modern)</option>
                                <option value="jpg">JPEG / JPG</option>
                                <option value="png">PNG (Lossless)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 4. Module 2: Smart Multi-Size Responsive Variants Generator --}}
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <div class="section-header text-indigo">
                        <span>⚡</span> Module 2: Smart Multi-Size Variant & Responsive Snippet
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="form-check form-switch fs-5 mb-1">
                                <input class="form-check-input" type="checkbox" name="generate_variants" value="yes" id="genVariantsCheck" checked>
                                <label class="form-check-label fw-bold fs-6" for="genVariantsCheck">
                                    Auto-Generate All Responsive Breakpoints (Thumbnail, Mobile, Tablet, Desktop, Retina)
                                </label>
                            </div>
                            <small class="text-muted ps-4 d-block">
                                Generates AVIF & WebP versions, packs them into a single <code>.zip</code> archive, and creates responsive HTML <code>&lt;picture&gt;</code> code.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- 5. Standard Transformations (Crop, Resize, Filters) --}}
                <div class="card border rounded-4 p-4 mb-4 bg-white shadow-sm">
                    <div class="section-header">
                        <span>🛠️</span> Standard Transforms & Color Adjustments
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Crop & Aspect Ratio</label>
                            <select name="crop" class="form-select">
                                <option value="original">Original (No Crop)</option>
                                <option value="1:1">1:1 (Square)</option>
                                <option value="4:3">4:3 (Landscape)</option>
                                <option value="3:4">3:4 (Portrait)</option>
                                <option value="16:9">16:9 (Wide HD)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Resize Resolution</label>
                            <select name="resize" class="form-select">
                                <option value="original">Original Dimensions</option>
                                <option value="300x300">300 × 300</option>
                                <option value="600x400">600 × 400</option>
                                <option value="800x600">800 × 600</option>
                                <option value="1200x800">1200 × 800 (HD)</option>
                                <option value="1920x1080">1920 × 1080 (FHD)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Rotation</label>
                            <select name="rotation" class="form-select">
                                <option value="0">0° (Normal)</option>
                                <option value="90">90° Clockwise</option>
                                <option value="180">180° Flip</option>
                                <option value="270">270° Counter-Clockwise</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Auto EXIF Orient</label>
                            <select name="auto_orientation" class="form-select">
                                <option value="yes" selected>Yes (Auto Rotate)</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Brightness (-100 to 100)</label>
                            <input type="number" name="brightness" class="form-control" value="0" min="-100" max="100">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Contrast (-100 to 100)</label>
                            <input type="number" name="contrast" class="form-control" value="0" min="-100" max="100">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-secondary">Grayscale</label>
                            <select name="grayscale" class="form-select">
                                <option value="no">No</option>
                                <option value="yes">Yes (B&W)</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-secondary">Blur (0-20)</label>
                            <input type="number" name="blur" class="form-control" value="0" min="0" max="20">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-secondary">Sharpen (0-20)</label>
                            <input type="number" name="sharpen" class="form-control" value="0" min="0" max="20">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Mirror / Flip</label>
                            <select name="mirror" class="form-select">
                                <option value="none">None</option>
                                <option value="horizontal">Horizontal (Flop)</option>
                                <option value="vertical">Vertical (Flip)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Invert Colors</label>
                            <select name="invert" class="form-select">
                                <option value="no">No</option>
                                <option value="yes">Yes (Negative)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow">
                        🚀 Process, Optimize & Generate Variants
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    // Toggle Watermark Fields
    function toggleWmFields(mode) {
        const textDiv = document.getElementById('wmTextInput');
        const logoDiv = document.getElementById('wmLogoInput');
        const posCol = document.getElementById('wmPositionCol');

        if (mode === 'text') {
            textDiv.style.display = 'block';
            logoDiv.style.display = 'none';
            posCol.style.display = 'block';
        } else if (mode === 'logo') {
            textDiv.style.display = 'none';
            logoDiv.style.display = 'block';
            posCol.style.display = 'block';
        } else if (mode === 'tiled') {
            textDiv.style.display = 'block';
            logoDiv.style.display = 'none';
            posCol.style.display = 'none';
        } else {
            textDiv.style.display = 'none';
            logoDiv.style.display = 'none';
            posCol.style.display = 'block';
        }
    }

    // Toggle Compression Mode
    function toggleCompMode(mode) {
        document.getElementById('manualQualityDiv').style.display = (mode === 'manual') ? 'block' : 'none';
        document.getElementById('targetKbDiv').style.display = (mode === 'target_size') ? 'block' : 'none';
    }

    function setTargetKb(val) {
        document.getElementById('targetKbInput').value = val;
    }

    // Before/After Slider Interaction
    const splitRange = document.getElementById('splitRange');
    const sliderContainer = document.getElementById('sliderContainer');
    if (splitRange && sliderContainer) {
        splitRange.addEventListener('input', function(e) {
            sliderContainer.style.setProperty('--split-pos', e.target.value + '%');
        });
    }

    // Copy Picture HTML Snippet
    function copyPictureSnippet() {
        const code = document.getElementById('snippetContent').innerText;
        navigator.clipboard.writeText(code).then(() => {
            const icon = document.getElementById('copyIcon');
            const text = document.getElementById('copyText');
            icon.className = 'bi bi-check-lg text-success';
            text.textContent = 'Copied!';
            setTimeout(() => {
                icon.className = 'bi bi-clipboard';
                text.textContent = 'Copy Snippet';
            }, 2500);
        });
    }
</script>

</body>
</html>
