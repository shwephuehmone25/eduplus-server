<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $subcategories = Subcategory::all();

        return response()->json($subcategories);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getSubcategoryDetails($id)
    {
        $subcategory = Subcategory::with('categories')->find($id);

        if (!$subcategory) {

            return response()->json(['error' => 'Subcategory not found'], 404);
        }

        return response()->json(['data' => $subcategory]);
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
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subcategory = Subcategory::create([
            'name' => $request->input('name'),
        ]);

        $subcategory->categories()->attach($request->input('category_id'));

        return response()->json([
            'data' => $subcategory,
            'message' => 'Subcategory created successfully', 'status' => 201]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subcategory = Subcategory::findOrFail($id);

        $subcategory->update([
            'name' => $request->input('name'),
        ]);

        $subcategory->categories()->sync($request->input('category_id'));

        return response()->json([
            'data' => $subcategory,
            'message' => 'Subcategory updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $subcategory->delete();

        return response()->json(['message' => 'Subcategory deleted successfully']);
    }

    public function getSubcategoriesByCategory(Request $request)
    {
        $categoryId = $request->input('category_id');
        $subcategories = Subcategory::where('category_id', $categoryId)->get();

        return response()->json($subcategories);
    }
}
