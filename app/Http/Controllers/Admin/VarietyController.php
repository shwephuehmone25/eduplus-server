<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variety;
use Illuminate\Http\Request;

class VarietyController extends Controller
{
    public function index()
    {
        $varieties = Variety::all();

        return response()->json(['data' => $varieties, 'status' => 200]);
    }

    public function getVarietyDetails($id)
    {
        $variety = Variety::find($id);

        if (!$variety) {

            return response()->json(['error' => 'Variety not found'], 404);
        }

        return response()->json(['data' => $variety, 'status' => 200]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $variety = Variety::create($data);

        return response()->json(['message' => 'Variety created successfully!', 'data' => $variety, 'status' => 201]);
    }

    public function update(Request $request, Variety $variety)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $variety->update($data);

        return response()->json(['message' => 'Variety updated successfully', 'data' => $variety, 'status' => 200]);
    }

    public function destroy(Variety $variety)
    {
        $variety->delete();

        return response()->json(['message' => 'Variety deleted successfully', 'status' => 200]);
    }
}
