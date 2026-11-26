<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanSetupRequest;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanSetupController extends Controller
{
   

    public function getDefaults()
    {
        return response()->json(DB::table('loan_defaults')->first());
    }

    public function saveDefaults(LoanSetupRequest $request)
    {
        $data = $request->validated();
        DB::table('loan_defaults')->updateOrInsert(['id' => 1], $data);

        return response()->json(['message' => 'Global defaults saved']);
    }

    public function saveProduct(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|exists:loan_products,id'] + (new LoanSetupRequest())->rules());

        $product = LoanProduct::findOrFail($data['product_id']);
        unset($data['product_id']);
        $product->update($data);

        return response()->json(['message' => 'Product updated']);
    }
}