<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Size;
use App\Models\Color;
use App\Models\Category;
use App\Models\Product;
use App\Models\PromotionItem;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Traits\GeneratesUniqueCode;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use GeneratesUniqueCode;

    public function test(Request $request)
    {
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        return Inertia::render('Products/index2', [
            'categories' => $allcategories
        ]);
    }

    public function getPromotionItems($productId)
    {
        // Fetch promotion items where promotion_id equals $productId
        $promotionItems = PromotionItem::where('promotion_id', $productId)
            ->with('product') // Include related product details
            ->get();

        // Check if any promotion items are found
        if ($promotionItems->isEmpty()) {
            return response()->json(['error' => 'No promotion items found for this promotion ID.'], 404);
        }

        return response()->json([
            'promotion_items' => $promotionItems,
        ]);
    }

    public function fetchProducts(Request $request)
    {
        $query = $request->input('search');
        $sortOrder = $request->input('sort');
        $selectedColor = $request->input('color');
        $selectedSize = $request->input('size');
        $stockStatus = $request->input('stockStatus');
        $selectedCategory = $request->input('selectedCategory');

        $categoryIds = [];

        // Fetch all descendants of the selected category
        if ($selectedCategory) {
            $categoryIds = $this->getAllDescendantCategoryIds($selectedCategory);
        }

        $productsQuery = Product::with('category', 'color', 'size', 'supplier')
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->when($selectedColor, function ($queryBuilder) use ($selectedColor) {
                $queryBuilder->whereHas('color', function ($colorQuery) use ($selectedColor) {
                    $colorQuery->where('name', $selectedColor);
                });
            })
            ->when($selectedSize, function ($queryBuilder) use ($selectedSize) {
                $queryBuilder->whereHas('size', function ($sizeQuery) use ($selectedSize) {
                    $sizeQuery->where('name', $selectedSize);
                });
            })
            ->when(in_array($sortOrder, ['asc', 'desc']), function ($queryBuilder) use ($sortOrder) {
                $queryBuilder->orderBy('selling_price', $sortOrder);
            })
            ->when($stockStatus, function ($queryBuilder) use ($stockStatus) {
                if ($stockStatus === 'in') {
                    $queryBuilder->where('stock_quantity', '>', 0);
                } elseif ($stockStatus === 'out') {
                    $queryBuilder->where('stock_quantity', '<=', 0);
                }
            })
            ->when(!empty($categoryIds), function ($queryBuilder) use ($categoryIds) {
                $queryBuilder->whereIn('category_id', $categoryIds);
            });

        $products = $productsQuery->orderBy('created_at', 'desc')->paginate(8);

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Recursively fetch all descendant category IDs for a given parent category ID.
     */
    private function getAllDescendantCategoryIds($parentId)
    {
        $categoryIds = [$parentId]; // Start with the parent category ID

        $children = Category::where('parent_id', $parentId)->get();

        foreach ($children as $child) {
            // Recursively fetch the descendants of each child
            $categoryIds = array_merge($categoryIds, $this->getAllDescendantCategoryIds($child->id));
        }

        return $categoryIds;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('search');
        $sortOrder = $request->input('sort');
        $selectedColor = $request->input('color');
        $selectedSize = $request->input('size');
        $stockStatus = $request->input('stockStatus');
        $selectedCategory = $request->input('selectedCategory');


        $productsQuery = Product::with('category', 'color', 'size', 'supplier')
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->when($selectedColor, function ($queryBuilder) use ($selectedColor) {
                $queryBuilder->whereHas('color', function ($colorQuery) use ($selectedColor) {
                    $colorQuery->where('name', $selectedColor);
                });
            })
            ->when($selectedSize, function ($queryBuilder) use ($selectedSize) {
                $queryBuilder->whereHas('size', function ($sizeQuery) use ($selectedSize) {
                    $sizeQuery->where('name', $selectedSize);
                });
            })
            ->when(in_array($sortOrder, ['asc', 'desc']), function ($queryBuilder) use ($sortOrder) {
                $queryBuilder->orderBy('selling_price', $sortOrder);
            })
            ->when($stockStatus, function ($queryBuilder) use ($stockStatus) {
                if ($stockStatus === 'in') {
                    $queryBuilder->where('stock_quantity', '>', 0); // In Stock
                } elseif ($stockStatus === 'out') {
                    $queryBuilder->where('stock_quantity', '<=', 0); // Out of Stock
                }
            })
            ->when($selectedCategory, function ($queryBuilder) use ($selectedCategory) {
                $queryBuilder->where('category_id', $selectedCategory); // Filter by category
            });


        $count = $productsQuery->count();

        $products = $productsQuery->orderBy('created_at', 'desc')->paginate(8);


        // $allcategories = Category::with('parent')->get();
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();


        return Inertia::render('Products/Index', [
            'products' => $products,
            'allcategories' => $allcategories,
            'colors' => $colors,
            'sizes' => $sizes,
            'suppliers' => $suppliers,
            'totalProducts' => $count,
            'search' => $query,
            'sort' => $sortOrder,
            'color' => $selectedColor,
            'size' => $selectedSize,
            'stockStatus' => $stockStatus,
            'selectedCategory' => $selectedCategory
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $categories = Category::all();
    //     $products = Product::all();
    //     $suppliers = Supplier::all();
    //     $colors = Color::all();
    //     $sizes = Size::all();



    //     return Inertia::render('Products/Create', [
    //         'products' => $products,
    //         'categories' => $categories,
    //         'suppliers' => $suppliers,
    //         'colors' => $colors,
    //         'sizes' => $sizes,
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */


public function store(Request $request)
{
    // Step 1: Validate incoming request
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
        'barcode' => 'nullable|string|unique:products',
        'unit_id' => 'nullable|exists:units,id', // assuming you have units
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'nullable|string',
        'is_beverage' => 'boolean',
    ]);

    try {
        // Step 2: Handle image upload
        if ($request->hasFile('image')) {
            $fileName = 'product_' . date("YmdHis") . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        }

        // Step 3: Generate barcode if missing
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateUniqueCode(12);
        }

        // Step 4: Save product locally
        $product = Product::create($validated);
        $product->update(['code' => 'PROD-' . $product->id]);

        // Step 5: Add stock transaction
        if (!empty($validated['stock_quantity']) && $validated['stock_quantity'] > 0) {
            StockTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Added',
                'quantity' => $validated['stock_quantity'],
                'transaction_date' => now(),
                'supplier_id' => $validated['supplier_id'] ?? null,
            ]);
        }

        // Step 6: Prepare data for external API
        $apiData = [
            'name' => $product->name,
            'pos_id' => $product->id,
            'unit_id' => $validated['unit_id'] ?? null,
            'opening_qty' => $validated['stock_quantity'] ?? 0,
            'current_qty' => $validated['stock_quantity'] ?? 0,
            'selling_price' => $product->selling_price,
        ];

        // Step 7: Send data to external API
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://tuk-stock.jaan.lk/api/sync/products.php', $apiData);


                //
            Log::info('POS API Sync Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('POS API Sync Failed: ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Product created and synced successfully!');
    } catch (\Exception $e) {
        Log::error('Error creating product: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while creating the product.');
    }
}


   
public function productVariantStore(Request $request)
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
        'unit_id' => 'nullable|exists:units,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'nullable|string',
      
    ]);

    // Generate barcode if not provided
    if (empty($validated['barcode'])) {
        $validated['barcode'] = $this->generateUniqueCode(12);
    }

    try {
        // Handle image upload
        if ($request->hasFile('image')) {
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'product_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        }

        // Create product locally
        $product = Product::create($validated);
        $product->update(['code' => 'PROD-' . $product->id]);

        // Add stock transaction if quantity provided
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

       
        $apiData = [
            'name' => $product->name,
            'pos_id' => $product->id,
            'unit_id' => $validated['unit_id'] ?? null,
            'opening_qty' => $stockQuantity,
            'current_qty' => $stockQuantity,
            'selling_price' => $product->selling_price,
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://tuk-stock.jaan.lk/api/sync/products.php', $apiData);

            Log::info('POS API Sync Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $apiError) {
            Log::error('POS API Sync failed: ' . $apiError->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Product variant created and synced successfully.');
    } catch (\Exception $e) {

        dd($e);
        Log::error('Error creating product variant: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while creating the product variant. Please try again.');
    }
}



    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        
        // $categories = Category::all();
        // $sizes = Size::all();
        // $suppliers = Supplier::all();
        // $colors = Color::all();
        $categories = Category::orderBy('created_at', 'desc')->get();
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();

        $product->load('category', 'color', 'size', 'suppliers');

        return Inertia::render('Products/Show', [

            'categories' => $categories,
            'product' => $product,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('created_at', 'desc')->get();
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();

        return inertia('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

 

public function update(Request $request, Product $product)
{
    // Step 1: Validate request
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
        'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
        'unit_id' => 'nullable|exists:units,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'nullable|string',
        'is_beverage' => 'boolean',
    ]);

    try {
        // Step 2: Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }
            $fileName = 'product_' . date("YmdHis") . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        }

        // Step 3: Update product locally
        $product->update($validated);

        // Step 4: Update stock transaction if quantity changed
        if (isset($validated['stock_quantity']) && $validated['stock_quantity'] != $product->stock_quantity) {
            StockTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Updated',
                'quantity' => $validated['stock_quantity'],
                'transaction_date' => now(),
                'supplier_id' => $validated['supplier_id'] ?? null,
            ]);
        }

        // Step 5: Sync with external API
        $apiData = [
            'name' => $product->name,
            'pos_id' => $product->id,
            'unit_id' => $validated['unit_id'] ?? null,
            'opening_qty' => $validated['stock_quantity'] ?? 0,
            'current_qty' => $validated['stock_quantity'] ?? 0,
            'selling_price' => $product->selling_price,
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://tuk-stock.jaan.lk/api/sync/products.php', $apiData);

            Log::info('POS API Sync Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('POS API Sync Failed: ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Product updated and synced successfully!');
    } catch (\Exception $e) {
        Log::error('Error updating product: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while updating the product.');
    }
}


 
 public function destroy(Product $product)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Prepare to delete the image
        $imagePath = str_replace('storage/', '', $product->image);
        $imageUsageCount = Product::where('image', $product->image)
            ->where('id', '!=', $product->id)
            ->count();

        if ($imageUsageCount === 0 && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        try {
            // Log the stock transaction
            StockTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Deleted',
                'quantity' => $product->stock_quantity ?? 0, // Fallback to 0 if undefined
                'transaction_date' => now(),
                'supplier_id' => $product->supplier_id ?? null, // Handle potential null value
            ]);
        } catch (\Exception $e) {
            // Log error and return a failure message
            report($e);
            return redirect()->route('products.index')->withErrors('Failed to log stock transaction. Please try again.');
        }

        // Delete the product
        $product->delete();

        return redirect()->route('products.index')->banner('Product Deleted successfully.');
    }


    public function addPromotion(Request $request)
    {
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();


        return Inertia::render('Products/Promotions', [
            'allcategories' => $allcategories,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    




public function submitPromotion(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
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
        'barcode' => 'nullable|string|unique:products',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'nullable|string',
        'products' => 'nullable|array',
    ], [
        'category_id.required' => 'Category is required.',
        'category_id.exists' => 'The selected category is invalid.',
    ]);

    try {
        // Step 1: Handle image upload
        if ($request->hasFile('image')) {
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'product_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('products', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        }

        // Step 2: Generate barcode if not provided
        if (empty($validated['barcode'])) {
            $validated['barcode'] = $this->generateUniqueCode(12);
        }

        // Step 3: Extract promotion items
        $products = $validated['products'] ?? [];
        unset($validated['products']);

        // Step 4: Create the promotion product
        $validated['is_promotion'] = true;
        $product = Product::create($validated);
        $product->update(['code' => 'PROD-' . $product->id]);

        // Step 5: Create PromotionItem entries
        foreach ($products as $promotionItem) {
            PromotionItem::create([
                'product_id' => $promotionItem['id'],
                'promotion_id' => $product->id,
                'quantity' => $promotionItem['quantity'],
            ]);
        }

        // Step 6: Sync promotion product to POS API
        try {
            $apiData = [
                'name' => $product->name,
                'pos_id' => $product->id,
                'unit_id' => $validated['unit_id'] ?? null,
                'opening_qty' => $validated['stock_quantity'] ?? 0,
                'current_qty' => $validated['stock_quantity'] ?? 0,
                'selling_price' => $product->selling_price,
                'is_promotion' => true,
            ];

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://tuk-stock.jaan.lk/api/sync/products.php', $apiData);

            Log::info('POS API Promotion Sync Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $apiError) {
            Log::error('POS API Promotion Sync failed: ' . $apiError->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Promotion created and synced successfully.');
    } catch (\Exception $e) {
        Log::error('Error creating promotion product: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while creating the promotion. Please try again.');
    }
}









}
