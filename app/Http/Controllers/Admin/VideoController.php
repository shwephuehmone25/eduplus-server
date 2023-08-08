<?php

namespace App\Http\Controllers\Admin;

use App\Models\Video;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Spatie\MediaInfo\MediaInfo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $videos = Video::with('categories', 'courses')->get();

        // Update the video URLs to use the S3 URLs
        foreach ($videos as $video) {
            $video->url = Storage::disk('s3')->temporaryUrl(
                $video->url, now()->addMinutes(15)
            );   
        }

        $categories = Category::all();
        $courses = Course::all();

        return response()->json([
            'videos' => $videos,
            'categories' => $categories,
            'courses'   => $courses
        ]);
    }

    // public function VideoUpload()
    // {
        
    // }

     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();

        return view('videos.create', compact('categories'));
    }

    public function show($id)
    {
        $video = Video::findOrFail($id);

        // Generate the S3 URL based on the path stored in the database
        // $videoUrl = Storage::disk('s3')->temporaryUrl(
        //     $video->url,
        //     now()->addMinutes(15)
        // );

        $videoUrl = Storage::disk('s3')->url($video->url);

        return view('videos.show', compact('videoUrl'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|mimetypes:video/mp4|max:214748364800',
            'title' => 'required|string|max:255',
        ]);

        if ($request->hasFile('video')) {
            $videoFile = $request->file('video');
            $filePath = 'videos/' . uniqid() . '.' . $videoFile->getClientOriginalExtension();

            // Upload to S3
            Storage::disk('s3')->put($filePath, file_get_contents($videoFile));

            // Get video duration using a package like "spatie\media-info"
            // $videoInfo = MediaInfo::get($videoFile);
            // $durationInSeconds = $videoInfo->get('duration') ?? 0;

            // Create a new video record in the database
            $video = new Video();
            $video->title = $request->input('title');
            $video->url = $filePath;
            $video->duration = $request->input('duration');
            $video->save();

            // Sync the video with selected categories
            $video->categories()->attach($request->input('category_id'));

            //Sync the video with selected courses
            $video->courses()->attach($request->input('course_id'));

            return response()->json([
                'message' => 'Video uploaded successfully!',
                'data' => $video,
            ], 201);
        }

        return response()->json(['error' => 'Failed to upload video.'], 400);
    }
}
