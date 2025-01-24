<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:customers',
            'gender' => 'required|string|max:20',
            'phone' => 'required|numeric|digits_between:10,15',
            'birthday' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buat customer baru
        $customer = Customer::create([
            'username' => $request->username,
            'email' => $request->email,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'message' => 'Registration successful. OTP has been sent via WhatsApp.',
            'phone' => $customer->phone,
        ]);
    }

    // Fungsi untuk mengirim OTP via WhatsApp
    private function sendOtp($phone, $otp)
    {
        // Mendapatkan API Key dan Authorization dari .env
        $apiKey = env('WOO_WA_API_KEY');
        $authorization = env('WOO_WA_AUTHORIZATION');

        // Membuat pesan OTP
        $message = "Your OTP code is: $otp";

        // Mengirim request ke API Woo-WA
        $response = Http::withHeaders([
            'Authorization' => "Basic $authorization",
            'Content-Type' => 'application/json',
        ])
        ->post('https://api.woo-wa.com/v2.0/demo', [
            'phone_no' => $phone,
            'key' => $apiKey,
            'message' => $message,
        ]);

        // Memeriksa apakah request berhasil
        if ($response->successful()) {
            return response()->json(['status' => 'success', 'message' => 'OTP sent successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Failed to send OTP']);
        }
    }

    public function signIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits_between:10,15',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::firstOrCreate(['phone' => $request->phone]);

        // Generate OTP
        $otp = rand(100000, 999999);
        $customer->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),  // OTP valid for 5 minutes
        ]);

        // Send OTP via WhatsApp/SMS
        $this->sendOtp($customer->phone, $otp);

        return response()->json([
            'message' => 'OTP has been sent to your phone number.',
            'phone' => $customer->phone,
        ]);
    }

    // Step 2: Verify OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits_between:10,15',
            'otp_code' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        // Check OTP validity
        if ($customer->otp_code !== $request->otp_code || Carbon::now()->greaterThan($customer->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 401);
        }

        // Clear OTP and log in
        $customer->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Generate API token
        $token = $customer->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'token' => $token,
            'customer' => $customer,
        ]);
    }

    public function logout()
    {
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Hapus token saat ini
        $customer->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
