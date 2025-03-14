<?php

namespace App\Http\Controllers;

use App\Events\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PendingBooking;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function intiatePayment(Request $request)
    {

        // message returned from the backend after pendingbooking is created
        $validated = $request->validate(
            [
                'tx_ref' => 'required|string',
                'amount' => 'required|numeric|min:100',
                'email' => 'required|email',
            ]

        );

        // storing the backend data in the variable( "variable is to be remembered"😅)
        $txRef = $validated['tx_ref'];
        $amount = $validated['amount'];
        $email = $validated['email'];
        // fetching the record we have stored in the pendingbooking table,we use tx_ref column why? because its unique for each pendingbooking record
        $pendingBooking = PendingBooking::where('tx_ref', $txRef)->first();
        if (! $pendingBooking) {
            return response()->json(['error' => 'Invalid or missing booking data'], 400);
        }
        // creating a payment record for the appointment(which will get updated later with the remaining informations)
        $payment = Payment::create([
            'tx_ref' => $txRef,
            'amount' => $amount,
            'currency' => 'ETB',
            'status' => 'pending',
            'payable_type' => 'appointment',
            'payable_id' => null,
            'checkout_url' => null,
            'patient_id' => null,
        ]);
        // updating the payment_id column in the pendingbooking table
        $pendingBooking->update(['payment_id' => $payment->id]);
        // fetching the hospital which is going to be paid for the appointment
        if (! $pendingBooking->hospital_id) {
            throw new Exception(' hospital id is missing ot invalid');
        }

        $hospital = Hospital::where('id', $pendingBooking->hospital_id)->firstOrFail();
        $chapaSecretKey = $hospital->account;

        if (! $chapaSecretKey) {
            throw new Exception('chapa secret key is missing from the hospital');
        }

        $chapaResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $chapaSecretKey,
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $txRef,
            'callback_url' => 'https://c3b6-190-2-141-80.ngrok-free.app/api/webhook/chapa',
        ]);

        if ($chapaResponse->failed()) {
            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }
        $responseData = $chapaResponse->json(); // converting the comming response from chapa into array

        return response()->json([
            'checkout_url' => $responseData['data']['checkout_url'],
            'message' => 'Redirect to this URL to complete payment',
        ]);
    }

    public function handleChapaWebhook(Request $request)
    {
        $data = $request->all();
        $txRef = $request->input('tx_ref');

        dd($data);

        if (! $txRef) {
            Log::error('Webhook received without tx_ref');

            return response()->json(['error' => 'Missing transaction reference'], 400);
        }

        $payment = Payment::where('tx_ref', $txRef)->first();
        if (! $payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $payment->update(['status' => $data['status']]);
        if ($data['status'] === 'success') {
            $pendingBooking = PendingBooking::where('tx_ref', $txRef)->where('payment_id', $payment->id)->first();
            if (! $pendingBooking) {
                return response()->json(['error' => 'Pending booking not found'], 404);
            }

            $pendingData = $pendingBooking->data;

            $patient = Patient::firstOrCreate(
                ['email' => $pendingData['patient']['email']],
                [
                    'first_name' => $pendingData['patient']['first_name'],
                    'last_name' => $pendingData['patient']['last_name'],
                    'phone_number' => $pendingData['patient']['phone_number'],
                    'date_of_birth' => $pendingData['patient']['date_of_birth'],
                    'gender' => $pendingData['patient']['gender'],
                ]
            );

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $pendingData['appointment']['doctor_id'],
                'hospital_id' => $pendingData['appointment']['hospital_id'],
                'appointment_date' => $pendingData['appointment']['appointment_date'],
                'appointment_time' => $pendingData['appointment']['appointment_time'],
                'amount' => $pendingData['appointment']['amount'],
                'status' => 'paid',
            ]);

            $user = User::create([
                'email' => $pendingData['user']['email'],
                'password' => $pendingData['user']['password'],
                'role' => $pendingData['user']['role'],
                'associated_id' => $patient->id,
            ]);

            $payment->update([
                'type' => Appointment::class,
                'type_id' => $appointment->id,
                'patient_id' => $patient->id,
            ]);

            $pendingBooking->delete();
            event(new AppointmentConfirmed($appointment));
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}
