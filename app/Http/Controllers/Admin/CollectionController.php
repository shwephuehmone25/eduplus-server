<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Collection;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collections = Collection::all();

        return response()->json(['data' => $collections]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:collections,name',
        ]);

        $collection = Collection::create($data);

        return response()->json([
            'message' => 'Collection is created successfully',
            'data' => $collection,
            'status' => 201
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:collections,name,' . $collection->id,
        ]);

        $collection->update($data);

        return response()->json(['message' => 'Collection is updated successfully', 'data' => $collection, 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collection $collection)
    {
        $collection->delete();

        return response()->json(['message' => 'Collection is deleted successfully', 'status' => 204]);
    }
}
