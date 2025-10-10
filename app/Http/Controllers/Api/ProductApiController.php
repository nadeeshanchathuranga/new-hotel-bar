<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Category;
use App\Models\Size;
use App\Models\Color;
use App\Models\Supplier;
use App\Models\PromotionItem;
use Illuminate\Support\Facades\Storage;

class ProductApiController extends Controller
{
    use \App\Traits\GeneratesUniqueCode;

    /**
     * Fetch products with optional filters (search, category, color, size, stock status, sort).
     */
    public function fetchProducts(Request $request)
    {
        $query = $request->input('search');
        $sortOrder = $request->input('sort');
        $selectedColor = $request->input('color');
        $selectedSize = $request->input('size');
        $stockStatus = $request->input('stockStatus');
        $selectedCategory = $request->input('selectedCategory');

        $productsQuery = Product::with('category', 'color', 'size', 'supplier')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->when($selectedColor, fn($q) => $q->whereHas('color', fn($sq) => $sq->where('name', $selectedColor)))
            ->when($selectedSize, fn($q) => $q->whereHas('size', fn($sq) => $sq->where('name', $selectedSize)))
            ->when(in_array($sortOrder, ['asc','desc']), fn($q) => $q->orderBy('selling_price', $sortOrder))
            ->when($stockStatus, function ($q) use ($stockStatus) {
                if ($stockStatus === 'in') $q->where('stock_quantity','>',0);
                elseif ($stockStatus === 'out') $q->where('stock_quantity','<=',0);
            })
            ->when($selectedCategory, fn($q) => $q->where('category_id', $selectedCategory));

        $products = $productsQuery->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($products, 200);
    }

    /**
     * Store a new product (with stock transaction).
     */
     public function importProducts(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'nullable|exists:categories,id',
        'name' => 'required|string|max:255',
        'size_id' => 'nullable|exists:sizes,id',
        'color_id' => 'nullable|exists:colors,id',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'doller_price' => 'nullable|numeric|min:0',
        'discounted_price' => 'nullable|numeric|min:0',
        'stock_quantity' => 'required|integer|min:0',
        'discount' => 'nullable|numeric|min:0|max:100',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'barcode' => 'nullable|string|unique:products,barcode',
        'image' => 'nullable|string', // accept base64
        'description' => 'nullable|string',
        'is_beverage' => 'boolean',
    ]);

    try {
        // Handle base64 image
        if (!empty($validated['image']) && str_starts_with($validated['image'], 'data:image')) {
            $imageData = $validated['image'];
            $fileName = 'product_' . now()->format('YmdHis') . '.png';
            $imagePath = 'products/' . $fileName;
            Storage::disk('public')->put($imagePath, base64_decode(preg_replace('#^data:image/\w+;base64,#i','',$imageData)));
            $validated['image'] = 'storage/' . $imagePath;
        }

        // Generate barcode if empty
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateUniqueCode(12);
        }

        // Create product
        $product = Product::create($validated);
        $product->update(['code' => 'PROD-' . $product->id]);

        // Add stock transaction
        $stockQuantity = $validated['stock_quantity'] ?? 0;
        if ($stockQuantity > 0) {
            StockTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Added',
                'quantity' => $stockQuantity,
                'transaction_date' => now(),
                'supplier_id' => $validated['supplier_id'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Product created successfully',
            // 'product' => $product
        ], 201);

    } catch (\Exception $e) {
        \Log::error('API Error creating product: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create product'], 500);
    }
}

    /**
     * Update a product
     */
 
  
 public function updateProducts(Request $request, Product $product)
{
    // Validate input
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'stock_quantity' => 'required|integer|min:0',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
    ]);

    // Handle image update
    if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($product->image && Storage::disk('public')->exists(str_replace('storage/', '', $product->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
        }

        // Save new image
        $fileExtension = $request->file('image')->getClientOriginalExtension();
        $fileName = 'product_' . date("YmdHis") . '.' . $fileExtension;
        $path = $request->file('image')->storeAs('products', $fileName, 'public');
        $validated['image'] = 'storage/' . $path;
    } else {
        $validated['image'] = $product->image;
    }

    // Calculate stock change
    $stockChange = $validated['stock_quantity'] - $product->stock_quantity;

    // Update product
    $product->update($validated);

    // Record stock transaction if there is a change
    if ($stockChange !== 0) {
        StockTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => $stockChange > 0 ? 'Added' : 'Deducted',
            'quantity' => abs($stockChange),
            'transaction_date' => now(),
            'supplier_id' => $validated['supplier_id'] ?? null,
        ]);
    }

    // Return API JSON response
    return response()->json([
        'success' => true,
        'message' => 'Product updated successfully',
      
    ]);
}




    /**
     * Delete product
     */
    public function destroyProducts(Product $product)
{
    try {
        // Delete image
        if ($product->image && Storage::disk('public')->exists(str_replace('storage/', '', $product->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
        }

        // Log stock transaction
        StockTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => 'Deleted',
            'quantity' => $product->stock_quantity ?? 0,
            'transaction_date' => now(),
            'supplier_id' => $product->supplier_id ?? null,
        ]);

        // Delete product
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    } catch (\Exception $e) {
        \Log::error('Delete product error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to delete product'], 500);
    }
}

}
