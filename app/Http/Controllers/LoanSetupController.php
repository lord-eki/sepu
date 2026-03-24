<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanProduct;
use Illuminate\Support\Facades\DB;

class LoanSetupController extends Controller
{
    public function getDefaults()
    {
        // Example: get global default settings from a table called 'loan_defaults'
        $defaults = DB::table('loan_defaults')->first();
        return response()->json($defaults);
    }

    public function saveDefaults(Request $request)
    {
        $data = $request->only([
            'interest_rate',
            'processing_fee_rate',
            'insurance_rate',
            'processing_fee_flat',
            'min_amount',
            'max_amount',
            'min_term_months',
            'max_term_months'
        ]);

        DB::table('loan_defaults')->updateOrInsert(['id' => 1], $data);

        return response()->json(['message' => 'Global defaults saved']);
    }

    public function saveProduct(Request $request)
    {
        $product = LoanProduct::findOrFail($request->product_id);

        $product->update($request->only([
            'interest_rate',
            'processing_fee_rate',
            'insurance_rate',
            'processing_fee_flat',
            'min_amount',
            'max_amount',
            'min_term_months',
            'max_term_months'
        ]));

        return response()->json(['message' => 'Product updated']);
    }
}