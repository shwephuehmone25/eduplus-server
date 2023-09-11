<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::with('subcategories')->get();

        if ($categories->isEmpty()) 
        {
        
        return response()->json(['message' => 'No categories found.', 'status' => 404]);
        }

        return response()->json(['data' => $categories]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCategoryDetails($id)
    {
        $category = Category::with('subcategories')->find($id);

        if (!$category) {

            return response()->json(['error' => 'Category not found', 'status' => 404]);
        }

        return response()->json(['data' => $category]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            try {
            $category = new Category();
            $category->name = $request->input('name');
            $category->save();

            $subcategoryIds = $request->input('subcategory_id');
            $category->subcategories()->attach($subcategoryIds);

            return response()->json(['message' => 'Category created successfully', 'data' => $category, 'status' => 201]);
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'Category creation failed', 'message' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
            try {
            $category = Category::findOrFail($id);
            $category->name = $request->input('name');
            $category->save();

            $subcategoryIds = $request->input('subcategory_id');
            $category->subcategories()->sync($subcategoryIds);

            return response()->json(['message' => 'Category updated successfully', 'data' => $category, 'status' => 200]);
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'Category update failed', 'message' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
        $category = Category::findOrFail($id);

        // Detach all subcategories from this category
        $category->subcategories()->detach();

        // Detach all associated courses without deleting them
        $category->courses()->detach();

        // Now, delete the category itself
        $category->delete();

            return response()->json(['message' => 'Category and associated records deleted successfully', 'status' => 200]);
        } catch (\Exception $e) {
            // Check if the error message contains the foreign key constraint error
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {

                return response()->json(['error' => 'Cannot delete the category because it has associated records.', 'status' => 400]);
            }

            return response()->json(['error' => 'Category deletion failed', 'status' => 500]);
        }
    }
}
