<?php

namespace App\Http\Controllers;

use App\Models\ProductTransaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('buyer')) {
            $productTransactions = $user->productTransactions()->orderBy('id','DESC')->get();
        } else {
            $productTransactions = ProductTransaction::orderBy('id','DESC')->get();
        }
        return view('admin.product_transactions.index', [
            'product_transactions'=> $productTransactions,
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
        //
        $user = Auth::user();

        $validated = $request->validate([
            'address'=>'required|string|max:255',
            'city'=> 'required|string|max:255',
            'proof'=> 'required|image|mimes:png,jpg,jpeg',
            'notes'=> 'required|string|max:65535',
            'post_code'=>'required|integer',
            'phone_number'=>'required|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            $subTotalCents = 0;
            $deliveryFeeCents = 10000 * 100;
            $cartItems = $user->carts()->with('product')->get();
            
            foreach ($cartItems as $item) {
                $subTotalCents +=( $item->product->price*100);
            }
            $taxCents = (int) round(0.11 * $subTotalCents);
            $insuranceCents = (int) round(0.23* $subTotalCents);
            $grandTotalCents = $subTotalCents + $taxCents + $insuranceCents + $deliveryFeeCents;

            $grandTotal = $grandTotalCents / 100;

            $validated['user_id'] = $user->id;
            $validated['total_amount'] = $grandTotal;
            $validated['is_paid'] = false;

            if ($request->hasFile('proof')){
                $proofPath = $request->file('proof')->store('payment_proofs', 'public');
                $validated['proof']= $proofPath;
            }

            $newTrx = ProductTransaction::create($validated);

            foreach ($cartItems as $item) {
                TransactionDetail::create([
                    'product_transaction_id'=>$newTrx->id,
                    'product_id'=> $item->product_id,
                    'price'=> $item->product->price,
                ]);

                $item->delete();
            }

            DB::commit();

            return redirect()->route('product_transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error'=>['System error!' . $e->getMessage()],
            ]);

            throw $error;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTransaction $productTransaction)
    {
        $productTransaction = ProductTransaction::with('transactionDetails.product')->find($productTransaction->id);
        return view('admin.product_transactions.details',[
            'productTransaction'=> $productTransaction,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductTransaction $productTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductTransaction $productTransaction)
    {
        //
        $productTransaction->update([
            'is_paid'=> true,
            'updated_at'=> Date::now(),
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductTransaction $productTransaction)
    {
        //
    }
}
