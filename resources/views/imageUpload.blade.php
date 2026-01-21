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