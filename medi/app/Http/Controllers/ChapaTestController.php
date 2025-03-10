<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ChapaTestController extends Controller
{
    public function testChapa()
    {
        $txRef = 'TEST-'.uniqid(); // Unique transaction reference
        $amount = 100; // Test amount in ETB
        $email = 'tesaat@gmail.com'; // Test email
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('CHAPA_SECRET_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $txRef,
            'callback_url' => 'https://yourdomain.com/webhook/chapa', // Replace with your webhook URL later
            'return_url' => 'https://yourdomain.com/payment/success', // Optional success redirect
        ]);

        $data = $response->json();

        return response()->json([
            'message' => 'Chapa API call successful',
            'response' => $data,
        ]);
    }
}
