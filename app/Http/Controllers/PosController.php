<?php

namespace App\Http\Controllers;
use App\Models\Owner;
use App\Models\OwnerItem;
use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Size;
use App\Models\ServiceCharge;
use App\Models\StockTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Inertia\Inertia;

class PosController extends Controller
{

     public function index(Request $request)
    {


        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        // Your existing data…
        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string;
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $serviceCharge = ServiceCharge::orderBy('created_at', 'desc')->get();




        $allemployee = Employee::orderBy('created_at', 'desc')->get();

        // NEW: Owners with current-month discount (or latest fallback)
      $owners = Owner::with([
        'thisMonthItem:id,owner_id,discount_value,current_discount,month',
        'latestItem:id,owner_id,discount_value,current_discount,month'
    ])
    ->orderBy('name')
    ->get()
    ->map(function ($o) {
        $item = $o->thisMonthItem ?? $o->latestItem;
        return [
            'id'               => $o->id,
            'name'             => $o->name,
            'code'             => $o->code,
            'status'           => $o->status,
            'discount_value'   => $item->discount_value ?? null,
            'current_discount' => $item->current_discount ?? null,

        ];
    });


        return Inertia::render('Pos/Index', [
            'product'       => null,
            'error'         => null,
            'loggedInUser'  => Auth::user(),
            'allcategories' => $allcategories,
            'allemployee'   => $allemployee,
            'colors'        => $colors,
            'sizes'         => $sizes,
            'owners'        => $owners,
            'serviceCharge' => $serviceCharge,
        ]);
    }

    // AJAX: fetch current discount for selected owner (prefer this month; else latest)
    public function getOwnerDiscount(Request $request)
    {
        $request->validate([
            'owner_id' => ['required','exists:owners,id'],
        ]);

        $owner = Owner::with(['thisMonthItem', 'latestItem'])->findOrFail($request->owner_id);

        $item = $owner->thisMonthItem ?? $owner->latestItem;

        if (!$item) {
            return response()->json([
                'owner_id'         => $owner->id,
                'discount_value'   => 0,
                'current_discount' => 0,
                'month'            => now()->startOfMonth()->format('Y-m'),
                'available'        => false,
                'message'          => 'No discount found for this owner.',
            ]);
        }

        return response()->json([
            'owner_id'         => $owner->id,
            'discount_value'   => (float) $item->discount_value, // fixed LKR
            'current_discount'   => (float) $item->current_discount, // fixed LKR

            'month'            => $item->month->format('Y-m'),
            'available'        => true,
        ]);
    }

    public function getProduct(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'barcode' => 'required',
        ]);

        $product = Product::where('barcode', $request->barcode)
            ->orWhere('code', $request->barcode)
            ->first();

        return response()->json([
            'product' => $product,
            'error' => $product ? null : 'Product not found',
        ]);
    }

    public function getCoupon(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The coupon code missing.', 'code.string' => 'The coupon code must be a valid string.']
        );

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or has been fully used.']);
        }

        return response()->json(['success' => 'Coupon applied successfully.', 'coupon' => $coupon]);
    }


 public function submit(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
        abort(403, 'Unauthorized');
    }

    // ---- Validate
    $data = $request->validate([
        'products'                    => ['required', 'array', 'min:1'],
        'products.*.id'               => ['required', 'integer', 'min:1'],
        'products.*.quantity'         => ['required', 'numeric', 'min:1'],
        'products.*.selling_price'    => ['required', 'numeric', 'min:0'],
        'products.*.cost_price'       => ['nullable', 'numeric', 'min:0'],

        'employee_id'                 => ['nullable', 'integer'],
        'userId'                      => ['required', 'integer'],
        'orderId'                     => ['required', 'string'],
        'paymentMethod'               => ['required', 'in:cash,card'],
        'order_type'                  => ['nullable', 'in:takeaway,pickup'],

        'order_source'                => ['nullable', 'integer'],

        'service_charge'              => ['nullable', 'numeric', 'min:0'],
        'delivery_charge'             => ['nullable', 'numeric', 'min:0'],
        'bank_service_charge'         => ['nullable', 'numeric', 'min:0'],

        'custom_discount'             => 'nullable|numeric|min:0',
        'custom_discount_type'        => 'nullable|string|in:fixed,percent',

        'owner_id'                    => ['nullable', 'integer'],
        'owner_discount_value'        => ['nullable', 'numeric', 'min:0'],

        'bank_name'                   => ['nullable', 'string', 'max:255'],
        'card_last4'                  => ['nullable', 'string', 'max:4'],

        'isConvertPrice'              => ['sometimes', 'boolean'],

        'customer.name'               => ['nullable', 'string', 'max:255'],
        'customer.email'              => ['nullable', 'email'],
        'customer.countryCode'        => ['nullable', 'string', 'max:10'],
        'customer.contactNumber'      => ['nullable', 'string', 'max:25'],
        'customer.address'            => ['nullable', 'string', 'max:500'],
        'customer.bdate'              => ['nullable', 'date'],

        'commission_amount'           => ['nullable', 'numeric', 'min:0'],
        'total'                       => ['nullable', 'numeric', 'min:0'],
    ]);

    $products = $data['products'];

    // ---- Subtotals
    $subtotal = collect($products)->reduce(
        fn($carry, $p) => $carry + ((float)$p['quantity'] * (float)$p['selling_price']),
        0.0
    );

    $totalCost = collect($products)->reduce(
        fn($carry, $p) => $carry + ((float)$p['quantity'] * (float)($p['cost_price'] ?? 0)),
        0.0
    );

    // ---- Product discounts
    $productDiscounts = collect($products)->reduce(function ($carry, $p) {
        if (
            isset($p['discount'], $p['discounted_price'], $p['apply_discount'])
            && (float)$p['discount'] > 0
            && $p['apply_discount'] !== false
        ) {
            $discountAmount = ((float)$p['selling_price'] - (float)$p['discounted_price']) * (float)$p['quantity'];
            return $carry + max(0, $discountAmount);
        }
        return $carry;
    }, 0.0);

    // ---- Coupon + Custom discount
    $couponDiscount = (float) data_get($data, 'appliedCoupon.discount', 0);

    $customInput = (float) ($data['custom_discount'] ?? 0);
    $customType  = $data['custom_discount_type'] ?? 'fixed';

    $customDiscountAmount = 0.0;
    if ($customInput > 0) {
        $customDiscountAmount = $customType === 'percent'
            ? round(($subtotal * $customInput) / 100, 2)
            : $customInput;
    }

    // ---- Owner discount
    $ownerId   = $data['owner_id'] ?? null;
    $ownerDisc = (float) ($data['owner_discount_value'] ?? 0);

    // ---- Final discount
    $totalDiscount = min(
        $subtotal,
        max(0, $productDiscounts + $couponDiscount + $customDiscountAmount + $ownerDisc)
    );

    // ---- Charges
    $orderType           = $data['order_type'] ?? null;
    $deliveryCharge      = $orderType === 'pickup' ? (float) ($data['delivery_charge'] ?? 0) : 0.0;
    $serviceChargeRate   = (float) ($data['service_charge'] ?? 0);
    $serviceChargeAmount = round(($subtotal * $serviceChargeRate) / 100, 2);

    $preBankTotal        = $subtotal - $totalDiscount + $deliveryCharge + $serviceChargeAmount;

    $bankRate            = (float) ($data['bank_service_charge'] ?? 0);
    $bankServiceAmount   = round(($preBankTotal * $bankRate) / 100, 2);

    // ---- Commission (platform/aggregator)
    $grossTotal          = round($preBankTotal + $bankServiceAmount, 2);
    $commissionAmount    = (float) ($data['commission_amount'] ?? 0);
    // Clamp commission to not exceed the gross total
    if ($commissionAmount < 0) {
        $commissionAmount = 0;
    }
    if ($commissionAmount > $grossTotal) {
        $commissionAmount = $grossTotal;
    }

    $grandTotal          = $grossTotal - $commissionAmount;

    DB::beginTransaction();

    try {
        // ---- Customer create (if needed)
        $customer = null;
        if (
            $request->filled('customer.contactNumber') ||
            $request->filled('customer.name') ||
            $request->filled('customer.email')
        ) {
            $phone = ($request->input('customer.countryCode') ?? '') . ($request->input('customer.contactNumber') ?? '');

            $existingByEmail = $request->filled('customer.email')
                ? Customer::where('email', $request->input('customer.email'))->first()
                : null;

            $existingByPhone = !empty($phone)
                ? Customer::where('phone', $phone)->first()
                : null;

            $emailToSave = $existingByEmail ? '' : ($request->input('customer.email') ?? '');
            $phoneToSave = $existingByPhone ? '' : $phone;

            if (!empty($phoneToSave) || !empty($emailToSave) || $request->filled('customer.name')) {
                $customer = Customer::create([
                    'name'           => $request->input('customer.name', ''),
                    'email'          => $emailToSave,
                    'phone'          => $phoneToSave,
                    'address'        => $request->input('customer.address', ''),
                    'member_since'   => now()->toDateString(),
                    'loyalty_points' => 0,
                ]);
            }
        }

        // ---- Sale record
        $sale = Sale::create([
            'customer_id'          => $customer?->id,
            'employee_id'          => $data['employee_id'] ?? null,
            'user_id'              => $data['userId'],
            'order_id'             => $data['orderId'],

            'order_type'           => $orderType,
            'order_source'         => $data['order_source'] ?? null,
            'bank_name'            => $data['bank_name'] ?? null,
            'card_last4'           => $data['card_last4'] ?? null,

            'total_amount'         => $subtotal,
            'discount'             => $totalDiscount,
            'service_charge'       => $serviceChargeRate,
            'service_charge_value' => $serviceChargeAmount,
            'delivery_charge'      => $deliveryCharge,
            'bank_service_charge'  => $bankRate,
            'bank_service_value'   => $bankServiceAmount,

            'commission_amount'    => $commissionAmount,

            'total_cost'           => $totalCost,
            'grand_total'          => $grandTotal,
            'payment_method'       => $data['paymentMethod'],
            'sale_date'            => now()->toDateString(),
            'cash'                 => $request->input('cash'),

            'custom_discount'      => $customInput,
            'custom_discount_type' => $customType,
            'custom_discount_calc' => $customDiscountAmount,

            'owner_id'             => $ownerId ?: null,
            'owner_discount_value' => $ownerDisc ?: 0,

            'isConvertPrice'       => (bool) $request->boolean('isConvertPrice'),
        ]);

        // ---- Sale items + stock checks
        foreach ($products as $p) {
            $productModel = Product::find($p['id']);
            if (!$productModel) {
                DB::rollBack();
                return response()->json(['message' => "Product not found (ID {$p['id']})."], 422);
            }

            $newStock = (int)$productModel->stock_quantity - (int)$p['quantity'];
            if ($newStock < 0) {
                DB::rollBack();
                return response()->json([
                    'message' => "Insufficient stock for product: {$productModel->name} ({$productModel->stock_quantity} available)",
                ], 423);
            }

            if ($productModel->expire_date && now()->greaterThan($productModel->expire_date)) {
                DB::rollBack();
                return response()->json([
                    'message' => "The product '{$productModel->name}' has expired (Expiration Date: {$productModel->expire_date->format('Y-m-d')}).",
                ], 423);
            }

            SaleItem::create([
                'sale_id'     => $sale->id,
                'product_id'  => $p['id'],
                'quantity'    => $p['quantity'],
                'unit_price'  => $p['selling_price'],
                'total_price' => (float)$p['quantity'] * (float)$p['selling_price'],
            ]);

            StockTransaction::create([
                'product_id'        => $p['id'],
                'transaction_type'  => 'Sold',
                'quantity'          => $p['quantity'],
                'transaction_date'  => now(),
                'supplier_id'       => $productModel->supplier_id ?? null,
            ]);

            $productModel->update(['stock_quantity' => $newStock]);
        }

        // ---- Owner usage increment
        if ($ownerId && $ownerDisc > 0) {
            $monthDate = now()->startOfMonth()->toDateString();

            $ownerItem = OwnerItem::where('owner_id', $ownerId)
                ->where('month', $monthDate)
                ->lockForUpdate()
                ->first();

            if ($ownerItem) {
                $ownerItem->increment('current_discount', $ownerDisc);
            } else {
                OwnerItem::create([
                    'owner_id'         => $ownerId,
                    'discount_value'   => 0,
                    'current_discount' => $ownerDisc,
                    'month'            => $monthDate,
                    'status'           => 'active',
                ]);
            }
        }

        DB::commit();

        // ---- Send data to external API (POS sync)
        try {
            $salesItems = collect($products)->map(function ($p) {
                $product = Product::find($p['id']);
                return [
                    'pos_id' => $product->id ?? null,
                    'qty'    => $p['quantity'],
                ];
            })->filter(fn($item) => !empty($item['pos_id']))->values();

          


       
            if ($salesItems->isNotEmpty()) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                ])->post('https://tuk-stock.jaan.lk/api/sync/pos_billing.php', [
                    'sales_items' => $salesItems,
                ]);

                if (!$response->successful()) {
                    Log::warning('POS sync failed', [
                        'response' => $response->body(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error syncing POS billing', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Sale recorded successfully!'], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'An error occurred while processing the sale.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



}