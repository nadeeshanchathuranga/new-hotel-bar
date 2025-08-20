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
    if (!Gate::allows('hasRole', ['Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Base rules (no 'logo' yet)
    $rules = [
        'name'    => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'phone'   => 'nullable|string|max:15',
        'email'   => 'nullable|email|max:255',
        'website' => 'nullable|url|max:255',
    ];

    // Only add the image rule if a file is actually uploaded
    if ($request->hasFile('logo')) {
        $rules['logo'] = 'image|mimes:jpg,jpeg,png,webp,gif|max:2048';
    }

    $validated = $request->validate($rules);

    // Handle logo upload if present
    if ($request->hasFile('logo')) {
        // Delete old file (if you stored in public path previously)
        if ($companyInfo->logo) {
            $oldPath = public_path($companyInfo->logo);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }

        $file = $request->file('logo');
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = 'companyInfo_' . now()->format('YmdHis') . '.' . $fileExtension;

        // Save into /public/CompanyInfos (matches your existing approach)
        $file->move(public_path('companyInfo'), $fileName);
        $validated['logo'] = 'companyInfo/' . $fileName;

        // (Recommended alternative)
        // $path = $file->store('company_infos', 'public'); // storage/app/public/company_infos
        // $validated['logo'] = 'storage/'.$path;
    } else {
        // Keep existing logo path if not uploading a new one
        $validated['logo'] = $companyInfo->logo;
    }

    $companyInfo->update($validated);

    return redirect()->route('companyInfo.index')
        ->with('banner', 'Company info updated successfully');
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyInfo $companyInfo)
    {
        //
    }
}
