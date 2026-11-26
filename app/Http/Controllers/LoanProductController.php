<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanProductRequest;
use App\Http\Requests\UpdateLoanProductRequest;
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
    public function store(StoreLoanProductRequest $request)
    {
        $validated = $request->validated();

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
    public function update(UpdateLoanProductRequest $request, LoanProduct $loanProduct)
    {
        $validated = $request->validated();

        $loanProduct->update($validated);

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }

    /**
     * Remove the specified loan product.
     */
    public function destroy(LoanProduct $loanProduct)
    {
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