<?php

namespace App\Http\Controllers\Api;

use App\Models\Category; 
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{ 


 // Store new category
    public function storeCategory(Request $request)
    {
       
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'is_featured' => 'nullable|boolean'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            
        ], 201);
    }

    // Update category
    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'is_featured' => 'nullable|boolean'
        ]);

        // Circular check to prevent category becoming its own ancestor
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            while ($parent) {
                if ($parent->id === $category->id) {
                    return response()->json(['error' => 'A category cannot be its own parent or ancestor.'], 422);
                }
                $parent = $parent->parent;
            }
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            
        ]);
    }

    // Delete category
    public function destroyCategory(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

}
