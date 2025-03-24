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
            'amount' => 'required|numeric|min:100',
            'email' => 'required|email',
        ]);

        $txRef = $validated['tx_ref'];
        $amount = $validated['amount'];
        $email = $validated['email'];

        // Fetch the pending booking record
        $pendingBooking = PendingBooking::where('tx_ref', $txRef)->first();
        if (! $pendingBooking) {
            Log::error('Invalid or missing booking data', ['tx_ref' => $txRef]);

            return response()->json(['error' => 'Invalid or missing booking data'], 400);
        }

        // Create a payment record
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
            'Authorization' => 'Bearer '.$secret,
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $txRef,
            'callback_url' => 'https://81e0-138-199-7-161.ngrok-free.app/api/webhook/chapa',
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

    public function handleChapaWebhook(Request $request)
    {
        // Log the raw request body and headers
        $requestBody = $request->getContent();
        $chapaSignature = $request->header('Chapa-Signature');

        Log::info('Webhook request received', [
            'headers' => $request->headers->all(),
            'body' => $requestBody,
        ]);

        // Check if the Chapa-Signature header is present
        if (! $chapaSignature) {
            Log::error('Chapa webhook received without Chapa-Signature header');

            return response()->json(['error' => 'Missing Chapa-Signature header'], 400);
        }

        // Fetch the transaction reference from the payload
        $data = json_decode($requestBody, true);
        $txRef = $data['tx_ref'] ?? null;

        if (! $txRef) {
            Log::error('Webhook received without tx_ref');

            return response()->json(['error' => 'Missing transaction reference'], 400);
        }

        // Fetch the payment record
        $payment = Payment::where('tx_ref', $txRef)->first();
        if (! $payment) {
            Log::error('Payment not found for tx_ref', ['tx_ref' => $txRef]);

            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Fetch the hospital associated with the payment
        $pendingBooking = PendingBooking::where('tx_ref', $txRef)
            ->where('payment_id', $payment->id)
            ->first();

        if (! $pendingBooking) {
            Log::error('Pending booking not found for tx_ref', ['tx_ref' => $txRef]);

            return response()->json(['error' => 'Pending booking not found'], 404);
        }

        $hospital = Hospital::findOrFail($pendingBooking->hospital_id);
        $secret = $hospital->account;

        if (! $secret) {
            Log::error('Chapa secret key is missing for the hospital', ['hospital' => $hospital]);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        // Validate the webhook event by generating the HMAC SHA-256 hash
        $hash = hash_hmac('sha256', $requestBody, $secret);

        Log::info('Signature comparison', [
            'expected' => $hash,
            'received' => $chapaSignature,
        ]);

        // Compare the generated hash with the Chapa-Signature header
        if (! hash_equals($hash, $chapaSignature)) {
            Log::error('Invalid Chapa webhook signature', [
                'expected' => $hash,
                'received' => $chapaSignature,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Update payment status
        $payment->update(['status' => $data['status']]);

        if ($data['status'] === 'success') {
            // Process the successful payment
            $pendingData = $pendingBooking->data;

            // Create or update the patient record
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

            // Create the appointment
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $pendingData['appointment']['doctor_id'],
                'hospital_id' => $pendingData['appointment']['hospital_id'],
                'appointment_date' => $pendingData['appointment']['appointment_date'],
                'appointment_time' => $pendingData['appointment']['appointment_time'],
                'amount' => $pendingData['appointment']['amount'],
                'status' => 'paid',
            ]);

            // Create the user
            $user = User::create([
                'email' => $pendingData['user']['email'],
                'password' => $pendingData['user']['password'],
                'role' => $pendingData['user']['role'],
                'associated_id' => $patient->id,
            ]);

            // Update the payment record
            $payment->update([
                'payable_type' => Appointment::class,
                'payable_id' => $appointment->id,
                'patient_id' => $patient->id,
            ]);

            // Delete the pending booking
            $pendingBooking->delete();

            // Dispatch the appointment confirmed event
            event(new AppointmentConfirmed($appointment));
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}
