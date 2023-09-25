<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rank;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ranks = Rank::all();

        return response()->json(['data' => $ranks]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showRankDetails($id)
    {
        $rank = Rank::find($id);

        if (!$rank) {

            return response()->json(['error' => 'Rank not found'], 404);
        }

        return response()->json(['data' => $rank]);
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
            'name' => 'required|string|max:255|unique:ranks,name',
        ]);

        $rank = Rank::create($data);

        return response()->json([
            'message' => 'Rank created successfully',
            'data' => $rank,
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
    public function update(Request $request, Rank $rank)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:ranks,name,' . $rank->id,
        ]);

        $rank->update($data);

        return response()->json(['message' => 'Rank updated successfully', 'data' => $rank, 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rank $rank)
    {
        $rank->delete();

        return response()->json(['message' => 'Rank deleted successfully', 'status' => 200]);
    }
}
