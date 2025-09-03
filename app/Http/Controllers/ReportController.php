<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
 

public function index(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Raw query params (may be empty strings)
    $startInput = $request->input('start_date', '');
    $endInput   = $request->input('end_date', '');

    // Parse to Carbon or null. If you store `sale_date` as DATE (no time), use ->toDateString().
    // If it's DATETIME, keep full Carbon. We'll use endOfDay for inclusivity.
    $start = $startInput ? Carbon::parse($startInput)->startOfDay() : null;
    $end   = $endInput   ? Carbon::parse($endInput)->endOfDay()   : null;

    // --- Build range helper ---
    $applyRangeToSale = function ($q) use ($start, $end) {
        if ($start && $end) {
            // full range
            $q->whereBetween('sale_date', [$start, $end]);
        } elseif ($start) {
            $q->where('sale_date', '>=', $start);
        } elseif ($end) {
            $q->where('sale_date', '<=', $end);
        }
    };

    // === PRODUCTS filtered to "sold in range" (if any bound) ===
    // Find product IDs that appear in SaleItems whose Sale is inside the range.
    $productIdsQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyRangeToSale) {
        $applyRangeToSale($q);
    });

    // If no date bounds given, show all products (as before)
    if ($start || $end) {
        $productIds = $productIdsQuery->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->orderBy('created_at', 'desc')->get();
    } else {
        $products = Product::orderBy('created_at', 'desc')->get();
    }

    // === SALES (drives Sales Table + charts/totals) ===
    $salesQuery = Sale::whereHas('saleItems.product.category')
        ->with(['saleItems.product.category', 'employee', 'customer']);

    // Apply date range (if any)
    $applyRangeToSale($salesQuery);

    // === sales_qty per product computed in the SAME filtered window ===
    $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyRangeToSale) {
        $applyRangeToSale($q);
    });

    $salesQuantities = $salesQuantitiesQuery
        ->select('product_id')
        ->selectRaw('SUM(quantity) as total_sales_qty')
        ->groupBy('product_id')
        ->get()
        ->keyBy('product_id');

    // Attach sales_qty to each product
    $products->transform(function ($product) use ($salesQuantities) {
        $product->sales_qty = $salesQuantities->get($product->id)?->total_sales_qty ?? 0;
        return $product;
    });

    // Pull sales (filtered + ordered)
    $sales = $salesQuery->orderBy('sale_date', 'desc')->get();

    // Category sales (based on filtered sales)
    $categorySales = [];
    foreach ($sales as $sale) {
        foreach ($sale->saleItems as $item) {
            $categoryName = $item->product->category->name ?? 'No Category';
            // If you want by item total, use item totals; otherwise this keeps your original logic:
            $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + $sale->total_amount;
        }
    }

    // Payment method totals (filtered)
    $paymentMethodTotals = $sales->groupBy('payment_method')->map(fn($g) => $g->sum('total_amount'))->toArray();

    // Employee sales summary (filtered) — respecting discounts as in your code
    $employeeSalesSummary = [];
    foreach ($sales as $sale) {
        if (!$sale->employee) continue;
        $name = $sale->employee->name;

        $employeeSalesSummary[$name] ??= [
            'Employee Name'       => $name,
            'Total Sales Amount'  => 0,
        ];

        $netAmount = ($sale->total_amount ?? 0) - ($sale->discount ?? 0) - ($sale->custom_discount ?? 0);
        $employeeSalesSummary[$name]['Total Sales Amount'] += $netAmount;
    }

    // Overall stats (filtered)
    $totalSaleAmount         = $sales->sum('total_amount');
    $totalCost               = $sales->sum('total_cost');
    $totalDiscount           = $sales->sum('discount');
    $customeDiscount         = $sales->sum('custom_discount');
    $netProfit               = $totalSaleAmount - $totalCost - $totalDiscount - $customeDiscount;
    $totalTransactions       = $sales->count();
    $averageTransactionValue = $totalTransactions > 0 ? $totalSaleAmount / $totalTransactions : 0;

    // Unique customers in the filtered set
    $totalCustomer = (clone $salesQuery)->distinct('customer_id')->count('customer_id');

    return Inertia::render('Reports/Index', [
        'products'                => $products,
        'sales'                   => $sales,
        'totalSaleAmount'         => $totalSaleAmount,
        'totalCustomer'           => $totalCustomer,
        'netProfit'               => $netProfit,
        'totalDiscount'           => $totalDiscount,
        'customeDiscount'         => $customeDiscount,
        'totalTransactions'       => $totalTransactions,
        'averageTransactionValue' => round($averageTransactionValue, 2),
        'startDate'               => $startInput, // echo back what user chose
        'endDate'                 => $endInput,
        'categorySales'           => $categorySales,
        'employeeSalesSummary'    => $employeeSalesSummary,
        // If you also want to surface payment method totals to the UI:
        // 'paymentMethodTotals'      => $paymentMethodTotals,
    ]);
}







    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
