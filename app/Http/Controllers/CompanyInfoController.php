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

    $validated = $request->validate([
        'name' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:15',
        'email' => 'nullable|email|max:255',
        'website' => 'nullable|url|max:255',
        'logo' => 'nullable|image|max:2048', // Ensures it's an image
    ]);

    if ($request->hasFile('logo')) {
        // Delete old logo if it exists
        $oldPath = public_path($companyInfo->logo);
        if ($companyInfo->logo && File::exists($oldPath)) {
            File::delete($oldPath);
        }

        // Save the new logo to public/CompanyInfos
        $file = $request->file('logo');
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = 'companyInfo_' . date("YmdHis") . '.' . $fileExtension;

        $file->move(public_path('CompanyInfos'), $fileName); // Save directly to public/

        $validated['logo'] = 'CompanyInfos/' . $fileName; // Relative public path
    } else {
        $validated['logo'] = $companyInfo->logo;
    }

    $companyInfo->update($validated);

    return redirect()->route('companyInfo.index')
        ->banner('Company info updated successfully');
}




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyInfo $companyInfo)
    {
        //
    }
}
