<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Variety;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index()
    {
        $notice = Notice::with('images')->latest()->get();

        return response()->json(['data' => $notice, 'status' => 200]);
    }

    public function getNoticeDetails($id)
    {
        $notice = Notice::find($id);
        $notice->load('images');

        if (!$notice) {

            return response()->json(['error' => 'News not found!', 'status' => 404]);
        }

        return response()->json(['data' => $notice, 'status' => 200]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'variety_id' => 'required|exists:varieties,id',
            'image' => 'required',
        ]);

        $news = new Notice;
        $news->title = $data['title'];
        $news->content = $data['content'];
        $news->variety_id = $data['variety_id'];
        $news->admin_id = Auth::check() ? Auth::id() : null;
        $news->save();

        if ($request->hasFile('image')) {
            $s3Path = Storage::disk('s3')->put('news', $request->file('image'));

            $image = new Image();
            $image->url = Storage::disk('s3')->url($s3Path);
            $news->images()->save($image);

            $news->load('images');
        }

        return response()->json(['message' => 'News created successfully!', 'data' => $news, 'status' => 201]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'variety_id' => 'required|exists:varieties,id',
            'image' => 'required',
        ]);

        $news = Notice::find($id);

        if (!$news) {
            return response()->json(['error' => 'News not found'], 404);
        }

        $news->title = $request->input('title');
        $news->content = $request->input('content');
        $news->variety_id = $request->input('variety_id');
        $news->save();

        if ($request->hasFile('image')) {
            $image = $news->images()->first();
            if ($image) {
                $imageUrl = parse_url($image->url, PHP_URL_PATH);
                if (Storage::disk('s3')->exists($imageUrl)) {
                    Storage::disk('s3')->delete($imageUrl);
                }
                $image->delete();
            } else {
                $image = new Image();
            }

            $s3Path = Storage::disk('s3')->put('news', $request->file('image'));

            $image->url = $s3Path;
            $news->images()->save($image);
        }

        return response()->json(['message' => 'News updated successfully!', 'data' =>  $news, 'status' => 200]);
    }

    public function destroy($id)
    {
        $news = Notice::find($id);

        if(!$news){
            return response()->json(['error' => 'News not found!', 'status' => 404]);
        }

        if ($news->images->count() > 0) {
            $image = $news->images->first();
            $imageUrl = parse_url($image->url, PHP_URL_PATH);

            if (Storage::disk('s3')->exists($imageUrl)) {
                // Delete the image from S3
                Storage::disk('s3')->delete($imageUrl);
            }
            $image->delete();
        }

        $news->delete();

        return response()->json(['message' => 'News deleted successfully!', 'status' => 204]);
    }

    public function getNewsByVariety(Request $request, $variety)
    {
        $variety = Variety::where('name', $variety)->first();
        $varietyId = $variety->id;
        $notice = Notice::where('variety_id', $varietyId)->get();

        if (!$notice)
        {
            return response()->json(['error' => 'News not found'], 404);
        }

        return response()->json(['data' => $notice]);
    }

    /**
     * Restore a single deleted course by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $restoredNews = Notice::withTrashed()->find($id)->restore();

        if ($restoredNews)
        {
            return response()->json(['message' => 'News restored successfully', 'status' => 200]);
        } else
        {

            return response()->json(['message' => 'News not found or already restored', 'status' => 404]);
        }
    }

    /**
     * Restore all deleted courses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreAll()
    {
        $restoredAllNews = Notice::onlyTrashed()->restore();

        if ($restoredAllNews)
        {

            return response()->json(['message' => 'All deleted news restored successfully', 'status' => 200]);
        } else
        {

            return response()->json(['message' => 'No deleted news found to restore', 'status' => 404]);
        }
    }
}
