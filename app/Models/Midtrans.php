<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Midtrans extends Model
{
    use HasFactory;

    public function urlTransaction()
    {

        $url = 'https://app.midtrans.com/snap/v1/transactions';


        return $url;
    }

    public function urlCheck($code)
    {

        $url = 'https://app.midtrans.com/snap/v1/' . $code . '/status';


        return $url;
    }

    public function authMidtrans()
    {


        $auth = 'Basic U0ItTWlkLXNlcnZlci1kR1c3Ym9DVW54aUUzTVNCWlhTX1R1LVc=';

        return $auth;
    }

    public function requestMidtrans($id_order, $total)
    {
        $new_date = date('YmdHis');
        $json = [
            'transaction_details' => [
                'order_id' => $id_order . $new_date,
                'gross_amount' => $total
            ]
        ];

        return $this->TransactionPush(json_encode($json));
    }

    public function TransactionPush($data)
    {

        $response = Http::withBody(
               json_encode($data)
            )
            ->withHeaders([
                'Accept'=> '*/*',
                'Authorization'=> 'Basic '.base64_encode(env('MIDTRANS_SERVERKEY')),
                'Content-Type'=> 'application/json',
            ]) 
            ->post('https://app.midtrans.com/snap/v1/transactions');


        return $response->body();
    }

    public  function checkTransaction($code)
    {
        $response = Http::withHeaders([
            'Accept'=> '*/*',
            'Authorization'=> 'Basic '.base64_encode(env('MIDTRANS_SERVERKEY')),
        ])
        ->get('https://app.midtrans.com/snap/v1/'.$code.'/status');

        return $response->body();
    }
}
