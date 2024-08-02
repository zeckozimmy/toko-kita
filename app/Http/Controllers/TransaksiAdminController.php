<?php

namespace App\Http\Controllers;

use App\Models\Midtrans;
use App\Models\transaksi;
use Illuminate\Http\Request;

class TransaksiAdminController extends Controller
{
    public function index()
    {
        $midtrans=new Midtrans();
        $data = transaksi::paginate(10);
        foreach($data as $item){
            $item['midtrans']=$midtrans->checkTransaction($item['code_transaksi']);

            if(isset($item['midtrans'])){
                $response=(array)json_decode($item['midtrans']);
                if(isset($response['status_code']) && $response['status_code']=="200"){
                    unset($item['midtrans']);
                    $item->update(['status'=>'Paid'],['id'=>$item['id']]);
                }
            }
        }
        return view('admin.page.transaksi', ['title' => "Transaksi", 'name' => 'Transaksi', 'data' => $data]);
    }
}
