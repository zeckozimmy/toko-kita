<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Models\Product;
use App\Models\TblCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $best = Product::where('quantity_out', '>=', 5)->get();
        $data = Product::paginate(15);
        $countKeranjang = TblCart::where(['idUser' => 'guest123', 'status' => 0])->count();
        return view('pelanggan.page.home', [
            'title' => 'Home',
            'data'  => $data,
            'best'  => $best,
            'count' => $countKeranjang,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addToCart(Request $request)
    {
        $idProduct = $request->input('idProduct');
        $product = Product::find($idProduct);
        $field = [
            'idUser'    => 'guest123',
            'id_barang' => $idProduct,
            'qty'       => 1,
            'price'     => $product->harga,
        ];

        TblCart::create($field);
        return redirect('/');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransaksiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaksi $transaksi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaksi $transaksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaksi $transaksi)
    {
        //
    }

    /**
     * Update the status of a transaction.
     */
    public function updateStatus(Request $request, $id)
    {
        $transaction = Transaksi::findOrFail($id);
        $transaction->status = $request->input('status');
        $transaction->save();

        return redirect()->route('transactions.index')->with('success', 'Transaction status updated successfully.');
    }
}
