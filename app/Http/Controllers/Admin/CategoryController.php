<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
// use App\Models\Subcategory;
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
        $categories = Category::get();

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
        $category = Category::find($id);

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

            // $subcategoryIds = $request->input('subcategory_id');
            // $category->subcategories()->attach($subcategoryIds);

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

            // $subcategoryIds = $request->input('subcategory_id');
            // $category->subcategories()->sync($subcategoryIds);

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
    public function destroy(Category $category)
    {
       $category->delete();

        return response()->json(['message' => 'Category deleted successfully', 'status' => 200]);
    }
}
