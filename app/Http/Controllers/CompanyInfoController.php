<?php

namespace App\Http\Controllers;

use App\Models\CompanyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;


class CompanyInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $companyInfo = CompanyInfo::first();

        return Inertia::render('CompanyInfo/Index', [
            'companyInfo' => $companyInfo,
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

    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyInfo $companyInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyInfo $companyInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */



     public function update(Request $request, CompanyInfo $companyInfo)
    {
        if (! Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Validation (logo rule added only if present)
        $rules = [
            'name'    => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:15',
            'email'   => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ];
        if ($request->hasFile('logo')) {
            $rules['logo'] = 'image|mimes:jpg,jpeg,png,webp,gif|max:2048';
        }

        $validated = $request->validate($rules);


        if ($request->hasFile('logo')) {

            if ($companyInfo->logo) {

                if (str_starts_with($companyInfo->logo, 'storage/')) {
                    $oldRel = str_replace('storage/', '', $companyInfo->logo);
                    Storage::disk('public')->delete($oldRel);
                } else {

                    $oldPublicPath = public_path($companyInfo->logo);
                    if (File::exists($oldPublicPath)) {
                        File::delete($oldPublicPath);
                    }
                }
            }


            $path = $request->file('logo')->store('company_info', 'public');


            $validated['logo'] = 'storage/' . $path; //
        } else {
         
            $validated['logo'] = $companyInfo->logo;
        }

        $companyInfo->update($validated);

        return redirect()
            ->route('companyInfo.index')
            ->with('success', 'Company info updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyInfo $companyInfo)
    {
        //
    }
}
