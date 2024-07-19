<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Midtrans extends Model
{
    use HasFactory;

    public function urlTransaction()
    {

        $url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';


        return $url;
    }

    public function urlCheck($code)
    {

        $url = 'https://api.sandbox.midtrans.com/v2/' . $code . '/status';


        return $url;
    }

    public function authMidtrans()
    {


        $auth = 'Authorization: Basic ' . base64_encode(getenv('midtrans_key_prod'));

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
        $auth = $this->authMidtrans();
        $url = $this->urlTransaction();
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                $auth,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function checkTransaction($code)
    {
        $url = $this->urlCheck($code);
        $auth = $this->authMidtrans();
        $url = $this->urlTransaction();
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                $auth,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
