<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index()
    {
        $companies = Company::all();
        
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies',
            'address' => 'required|string',
            'contact_email' => 'required|email|unique:companies',
            'contact_phone' => 'required|string|unique:companies',
        ]);
        
        $company = Company::create($validated);
        
        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,'.$company->id,
            'address' => 'required|string',
            'contact_email' => 'required|email|unique:companies,contact_email,'.$company->id,
            'contact_phone' => 'required|string|unique:companies,contact_phone,'.$company->id,
        ]);
        
        $company->update($validated);
        
        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company)
    {
        // Check if the company has users or employees
        if ($company->users()->exists() || $company->employees()->exists()) {
            return redirect()->route('companies.index')
                ->with('error', 'Cannot delete company with associated users or employees.');
        }
        
        $company->delete();
        
        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}