<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Upload Video</div>

                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('video.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" class="form-control" id="title" required>
                        </div>
                        <!-- <div class="form-group">
                            <label for="title">Duration</label>
                            <input type="text" name="duration" class="form-control" id="title" required>
                        </div>  -->
                        <div class="form-group">
                            <label for="video">Video File</label>
                            <input type="file" name="video" class="form-control-file" id="video" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Choose Category:</label>
                            <select name="category_id[]" id="category_id[]">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select><br>
                        </div>
                        <div class="form-group">
                            <label for="course_id">Choose Course:</label>
                            <select name="course_id[]" id="course_id[]">
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select><br>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Video</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>