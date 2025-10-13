<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanProductController extends Controller
{
     /**
     * Display a listing of loan products.
     */
    public function index()
    {
        $loanProducts = LoanProduct::orderBy('created_at', 'desc')->get();
        
        return Inertia::render('LoanProducts/Index', [
            'loanProducts' => $loanProducts
        ]);
    }

    /**
     * Show the form for creating a new loan product.
     */
    public function create()
    {
        return Inertia::render('LoanProducts/Create');
    }

    /**
     * Store a newly created loan product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code',
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_term_months' => 'required|integer|min:1',
            'max_term_months' => 'required|integer|gt:min_term_months',
            'processing_fee_rate' => 'required|numeric|min:0|max:100',
            'insurance_rate' => 'required|numeric|min:0|max:100',
            'grace_period_days' => 'required|integer|min:0',
            'penalty_rate' => 'required|numeric|min:0|max:100',
            'eligibility_criteria' => 'required|array',
            'required_documents' => 'required|array',
            'requires_guarantor' => 'required|boolean',
            'min_guarantors' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        LoanProduct::create($validated);

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product created successfully.');
    }

    /**
     * Display the specified loan product.
     */
    public function show(LoanProduct $loanProduct)
    {
        return Inertia::render('LoanProducts/Show', [
            'loanProduct' => $loanProduct
        ]);
    }

    /**
     * Show the form for editing the specified loan product.
     */
    public function edit(LoanProduct $loanProduct)
    {
        return Inertia::render('LoanProducts/Edit', [
            'loanProduct' => $loanProduct
        ]);
    }

    /**
     * Update the specified loan product.
     */
    public function update(Request $request, LoanProduct $loanProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code,' . $loanProduct->id,
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_term_months' => 'required|integer|min:1',
            'max_term_months' => 'required|integer|gt:min_term_months',
            'processing_fee_rate' => 'required|numeric|min:0|max:100',
            'insurance_rate' => 'required|numeric|min:0|max:100',
            'grace_period_days' => 'required|integer|min:0',
            'penalty_rate' => 'required|numeric|min:0|max:100',
            'eligibility_criteria' => 'required|array',
            'required_documents' => 'required|array',
            'requires_guarantor' => 'required|boolean',
            'min_guarantors' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $loanProduct->update($validated);

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }

    /**
     * Remove the specified loan product.
     */
    public function destroy(LoanProduct $loanProduct)
    {
        // Check if product has associated loans
        if ($loanProduct->loans()->count() > 0) {
            return back()->with('error', 'Cannot delete loan product with existing loans. Consider deactivating it instead.');
        }

        $loanProduct->delete();

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product deleted successfully.');
    }

    /**
     * Toggle active status of loan product.
     */
    public function toggleStatus(LoanProduct $loanProduct)
    {
        $loanProduct->update(['is_active' => !$loanProduct->is_active]);

        return back()->with('success', 'Loan product status updated successfully.');
    }
}
