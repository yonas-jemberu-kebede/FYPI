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
    public function initiatePayment(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'tx_ref' => 'required|string',
            'amount' => 'required|numeric|min:99.9',
            'email' => 'required|email',
        ]);

        $tx_ref = $validated['tx_ref'];
        $amount = $validated['amount'];
        $email = $validated['email'];

        // Fetch the pending booking record
        $pendingBooking = PendingBooking::where('tx_ref', $tx_ref)->first();
        if (! $pendingBooking) {
            Log::error('Invalid or missing booking data', ['tx_ref' => $tx_ref]);

            return response()->json(['error' => 'Invalid or missing booking data'], 400);
        }

        // Create a payment record
        $payment = Payment::create([
            'tx_ref' => $tx_ref,
            'amount' => $amount,
            'currency' => 'ETB',

            'status' => 'pending',
            'payable_type' => 'appointment',
            'payable_id' => null,

            'checkout_url' => null,
            'patient_id' => null,
        ]);

        // Update the pending booking with the payment ID
        $pendingBooking->update(['payment_id' => $payment->id]);

        // Fetch the hospital associated with the booking
        if (! $pendingBooking->hospital_id) {
            Log::error('Hospital ID is missing or invalid', ['pending_booking' => $pendingBooking]);
            throw new Exception('Hospital ID is missing or invalid');
        }

        $hospital = Hospital::findOrFail($pendingBooking->hospital_id);
        $secret = $hospital->account;

        if (! $secret) {
            Log::error('Chapa secret key is missing for the hospital', ['hospital' => $hospital]);
            throw new Exception('Chapa secret key is missing for the hospital');
        }

        // Initiate payment with Chapa
        $chapaResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secret,
            'Content-Type' => 'application/json',
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $tx_ref,
            'return_url' => route('payment.return', ['tx_ref' => $tx_ref]),
        ]);

        if ($chapaResponse->failed()) {
            Log::error('Failed to initiate payment with Chapa', ['response' => $chapaResponse->json()]);

            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }

        $responseData = $chapaResponse->json();

        return response()->json([
            'checkout_url' => $responseData['data']['checkout_url'],
            'message' => 'Redirect to this URL to complete payment',
        ]);
    }

    public function handleChapaWebhook(Request $request, $tx_ref)
    {

        Log::info('handleChapaWebhook called with tx_ref', ['tx_ref' => $tx_ref]);

        if (! $tx_ref) {
            Log::error('Webhook received without tx_ref');

            return response()->json(['error' => 'Missing transaction reference'], 400);
        }
        Log::info('Checking payment for tx_ref', ['tx_ref' => $tx_ref]);
        // Fetch the payment record
        $payment = Payment::where('tx_ref', $tx_ref)->first();

        if (! $payment) {
            Log::error('Payment not found for tx_ref', ['tx_ref' => $tx_ref]);

            return response()->json(['error' => 'Payment not found'], 404);
        }
        Log::info('Payment found', ['tx_ref' => $tx_ref, 'payment_id' => $payment->id]);

        $pendingBooking = PendingBooking::where('payment_id', $payment->id)->first();

        if (! $pendingBooking) {
            Log::error('Pending booking not found', ['tx_ref' => $tx_ref]);

            return response()->json(['error' => 'Pending booking not found'], 404);
        }

        // Update payment status
        $payment->update(['status' => 'success']);

        // Process the successful payment
        $pendingData = $pendingBooking->data;

        // Create or update the patient record
        // $patient = Patient::firstOrCreate(
        //     ['email' => $pendingData['patient']['email']],
        //     [
        //         'first_name' => $pendingData['patient']['first_name'],
        //         'last_name' => $pendingData['patient']['last_name'],
        //         'phone_number' => $pendingData['patient']['phone_number'],
        //         'date_of_birth' => $pendingData['patient']['date_of_birth'],
        //         'gender' => $pendingData['patient']['gender'],
        //     ]
        // );

        // Create the appointment
        $appointment = Appointment::create([
            'patient_id' => $pendingData['appointment']['patient_id'],
            'doctor_id' => $pendingData['appointment']['doctor_id'],
            'hospital_id' => $pendingData['appointment']['hospital_id'],
            'appointment_date' => $pendingData['appointment']['appointment_date'],
            'appointment_time' => $pendingData['appointment']['appointment_time'],
            'amount' => $pendingData['appointment']['amount'],
            'status' => 'paid',
        ]);

        // Create the user
        // $user = User::create([
        //     'email' => $pendingData['user']['email'],
        //     'password' => $pendingData['user']['password'],
        //     'role' => $pendingData['user']['role'],
        //     'associated_id' => $patient->id,
        // ]);

        // Update the payment record
        $payment->update([
            'payable_type' => Appointment::class,
            'payable_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
        ]);

        // Delete the pending booking
        $pendingBooking->delete();

        // Dispatch the appointment confirmed event
        Log::info('Event fired', ['data' => $appointment]);
        event(new AppointmentConfirmed($appointment));

        return response()->json(['message' => 'congrats appointment is secured successfuly!'], 200);
    }
}
