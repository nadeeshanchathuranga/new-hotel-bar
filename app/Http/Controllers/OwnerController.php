<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Owner;
use App\Models\OwnerItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;


class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */


  public function index()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString(); // 'YYYY-MM-01'
        $defaultDiscount   = 25000; // change if you need a different default

        DB::transaction(function () use ($currentMonthStart, $defaultDiscount) {
            // 1) Deactivate all previous months
            OwnerItem::where('month', '<', $currentMonthStart)
                ->where('status', '!=', 'inactive')
                ->update([
                    'status'     => 'inactive',
                    'updated_at' => now(),
                ]);

            // 2) Ensure an ACTIVE row for this month exists for every owner
            $ownerIds = Owner::pluck('id');
            foreach ($ownerIds as $ownerId) {
                OwnerItem::firstOrCreate(
                    [
                        'owner_id' => $ownerId,
                        'month'    => $currentMonthStart,
                    ],
                    [
                        'discount_value'   => $defaultDiscount,
                        'current_discount' => 0,
                        'status'           => 'active',
                    ]
                );
            }
        });

        // 3) Load owners with items (newest month first)
     $owners = Owner::with(['items' => function ($q) {
    $q->whereIn('status', ['active', 'inactive'])
      ->orderBy('month', 'desc');
}])->get();

        return Inertia::render('Owner/Index', [
            'allOwners'   => $owners,
            'totalOwners' => $owners->count(),
        ]);
    }


     public function import(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'owners'              => ['required', 'array', 'min:1'],
            'owners.*.code'       => ['required', 'string', 'max:191'],
            'owners.*.name'       => ['required', 'string', 'max:191'],
            'default_discount'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $ownersData      = $validated['owners'];
        $defaultDiscount = (float)($validated['default_discount'] ?? 25000);
        $currentMonth    = Carbon::now()->startOfMonth()->toDateString();

        DB::transaction(function () use ($ownersData, $defaultDiscount, $currentMonth) {
            // Upsert owners by code
            foreach ($ownersData as $row) {
                DB::table('owners')->updateOrInsert(
                    ['code' => $row['code']],
                    [
                        'name'       => $row['name'],
                        'code'       => $row['code'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Ensure a current-month OwnerItem for every owner
            $ownerIds = DB::table('owners')->pluck('id');
            foreach ($ownerIds as $ownerId) {
                OwnerItem::updateOrCreate(
                    [
                        'owner_id' => $ownerId,
                        'month'    => $currentMonth,
                    ],
                    [
                        'discount_value'   => $defaultDiscount,
                        'current_discount' => 0,
                        'status'           => 'active',
                    ]
                );
            }

            // Deactivate old months (idempotent)
            OwnerItem::where('month', '<', $currentMonth)
                ->where('status', '!=', 'inactive')
                ->update([
                    'status'     => 'inactive',
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success', 'Owners imported and current month items ensured.');
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
    public function show(Owner $owner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Owner $owner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Owner $owner)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Owner $owner)
    {
        //
    }
}
