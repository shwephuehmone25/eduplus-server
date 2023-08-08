<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Video Details</div>

                    <div class="card-body">
                        <video src="{{ $videoUrl }}"></video>
                    </div>
                    <!-- Display the file using the URL -->
                    <a href="{{ $videoUrl }}" target="_blank">View File</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
    
