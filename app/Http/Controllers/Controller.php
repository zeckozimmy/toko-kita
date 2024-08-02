<?php

namespace App\Http\Controllers;

use App\Models\Midtrans;
use App\Models\modelDetailTransaksi;
use App\Models\product;
use App\Models\tblCart;
use App\Models\transaksi;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function shop(Request $request)
    {
        $builder = product::orderBy('created_at', 'DESC');
        if ($request->has('type')) {
            $builder->where('type', $request->type);
        }
        $data = $builder->paginate(5);
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();
        $label = product::select('type')->distinct()->get();

        return view('pelanggan.page.shop', [
            'title' => 'Shop',
            'data' => $data,
            'count' => $countKeranjang,
            'label' => $label
        ]);
    }

    public function transaksi()
    {
        $db = tblCart::with('product')->where(['idUser' => 'guest123', 'status' => 0])->get();
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();

        return view('pelanggan.page.transaksi', [
            'title' => 'Transaksi',
            'count' => $countKeranjang,
            'data' => $db
        ]);
    }

    public function contact()
    {
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();

        return view('pelanggan.page.contact', [
            'title' => 'Contact Us',
            'count' => $countKeranjang,
        ]);
    }

    public function checkout()
    {
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();
        $code = transaksi::count();
        $codeTransaksi = date('Ymd') . ($code + 1);
        $detailBelanja = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->sum('price');
        $jumlahBarang = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->count('id_barang');
        $qtyBarang = modelDetailTransaksi::where(['id_transaksi' => $codeTransaksi, 'status' => 0])
            ->sum('qty');

        return view('pelanggan.page.checkOut', [
            'title' => 'Check Out',
            'count' => $countKeranjang,
            'detailBelanja' => $detailBelanja,
            'jumlahbarang' => $jumlahBarang,
            'qtyOrder' => $qtyBarang,
            'codeTransaksi' => $codeTransaksi
        ]);
    }

    public function prosesCheckout(Request $request, $id)
    {
        $data = $request->all();
        $code = transaksi::count();
        $codeTransaksi = date('Ymd') . ($code + 1);

        // Save detail transaction
        $detailTransaksi = new modelDetailTransaksi();
        $fieldDetail = [
            'id_transaksi' => $codeTransaksi,
            'id_barang' => $data['idBarang'],
            'qty' => $data['qty'],
            'price' => $data['total']
        ];
        $detailTransaksi::create($fieldDetail);

        // Update cart
        $fieldCart = [
            'qty' => $data['qty'],
            'price' => $data['total'],
            'status' => 1,
        ];
        tblCart::where('id', $id)->update($fieldCart);

        Alert::toast('Checkout Berhasil', 'success');
        return redirect()->route('checkout');
    }

    public function prosesPembayaran(Request $request)
    {
        $data = $request->all();
        $dbTransaksi = new transaksi();

        $dbTransaksi->code_transaksi = $data['code'];
        $dbTransaksi->total_qty = $data['totalQty'];
        $dbTransaksi->total_harga = $data['dibayarkan'];
        $dbTransaksi->nama_customer = $data['namaPenerima'];
        $dbTransaksi->alamat = $data['alamatPenerima'];
        $dbTransaksi->no_tlp = $data['tlp'];
        $dbTransaksi->ekspedisi = $data['ekspedisi'];
        $dbTransaksi->save();

        $dataCart = modelDetailTransaksi::where('id_transaksi', $data['code'])->get();
        foreach ($dataCart as $x) {
            $dataUp = modelDetailTransaksi::where('id', $x->id)->first();
            $dataUp->status = 1;
            $dataUp->save();

            $idProduct = product::where('id', $x->id_barang)->first();
            $idProduct->quantity -= $x->qty;
            $idProduct->quantity_out = $x->qty;
            $idProduct->save();
        }

        Alert::alert()->success('Transaksi berhasil', 'Ditunggu barangnya');
        return redirect()->route('home');
    }

    public function keranjang()
    {
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();
        $all_trx = transaksi::all();

        return view('pelanggan.page.keranjang', [
            'name' => 'Payment',
            'title' => 'Payment Process',
            'count' => $countKeranjang,
            'data' => $all_trx
        ]);
    }

    public function bayar($id)
    {
        $find_data = transaksi::find($id);
        $countKeranjang = tblCart::where(['idUser' => 'guest123', 'status' => 0])->count();

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => $find_data->id,
                'gross_amount' => $find_data->total_harga,
            ],
            'customer_details' => [
                'first_name' => 'Mr',
                'last_name' => $find_data->nama_customer,
                'phone' => $find_data->no_tlp,
            ],
        ];

        $midtrans = new Midtrans();
        $res = (array)json_decode($midtrans->TransactionPush($params));

        return view('pelanggan.page.detailTransaksi', [
            'name' => 'Detail Transaksi',
            'title' => 'Detail Transaksi',
            'count' => $countKeranjang,
            'token' => $res['token'],
            'data' => $find_data,
        ]);
    }

    public function admin()
    {
        $dataProduct = product::count();
        $dataStock = product::sum('quantity');
        $dataTransaksi = transaksi::count();
        $dataPenghasilan = transaksi::sum('total_harga');

        return view('admin.page.dashboard', [
            'name' => "Dashboard",
            'title' => 'Admin Dashboard',
            'totalProduct' => $dataProduct,
            'sumStock' => $dataStock,
            'dataTransaksi' => $dataTransaksi,
            'dataPenghasilan' => $dataPenghasilan,
        ]);
    }

    public function userManagement()
    {
        return view('admin.page.user', [
            'name' => "User Management",
            'title' => 'Admin User Management',
        ]);
    }

    public function login()
    {
        return view('admin.page.login', [
            'name' => "Login",
            'title' => 'Admin Login',
        ]);
    }

    public function loginProses(Request $request)
    {
        Session::flash('error', $request->email);
        $dataLogin = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = User::where('email', $request->email)->first();

        if ($user === null) {
            Session::flash('error', 'Email tidak ditemukan');
            return back();
        }

        if ($user->is_admin === 0) {
            Session::flash('error', 'Kamu bukan admin');
            return back();
        } else {
            if (Auth::attempt($dataLogin)) {
                Alert::toast('Kamu berhasil login', 'success');
                $request->session()->regenerate();
                return redirect()->intended('/admin/dashboard');
            } else {
                Alert::toast('Email dan Password salah', 'error');
                return back();
            }
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        Alert::toast('Kamu berhasil Logout', 'success');
        return redirect('admin');
    }

    public function cetak_pdf()
    {
        try {
            $transaksi = Transaksi::all();

            if ($transaksi->isEmpty()) {
                throw new \Exception('No transaksi data available.');
            }

            // Load the PDF view with the transaksi data
            $pdf = PDF::loadView('admin.page.transaksi_pdf', ['transaksi' => $transaksi]);

            if (!$pdf) {
                throw new \Exception('Failed to create PDF.');
            }

            return $pdf->download('laporan-transaksi.pdf');
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF.'], 500);
        }
    }
}
