<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanSetupController extends Controller
{
    private const RULES = [
        'interest_rate'       => 'nullable|numeric|min:0|max:100',
        'processing_fee_rate' => 'nullable|numeric|min:0|max:100',
        'insurance_rate'      => 'nullable|numeric|min:0|max:100',
        'processing_fee_flat' => 'nullable|numeric|min:0',
        'min_amount'          => 'nullable|numeric|min:0',
        'max_amount'          => 'nullable|numeric|gte:min_amount',
        'min_term_months'     => 'nullable|integer|min:1',
        'max_term_months'     => 'nullable|integer|gte:min_term_months',
    ];

    public function getDefaults()
    {
        return response()->json(DB::table('loan_defaults')->first());
    }

    public function saveDefaults(Request $request)
    {
        $data = $request->validate(self::RULES);
        DB::table('loan_defaults')->updateOrInsert(['id' => 1], $data);

        return response()->json(['message' => 'Global defaults saved']);
    }

    public function saveProduct(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|exists:loan_products,id'] + self::RULES);

        $product = LoanProduct::findOrFail($data['product_id']);
        unset($data['product_id']);
        $product->update($data);

        return response()->json(['message' => 'Product updated']);
    }
}