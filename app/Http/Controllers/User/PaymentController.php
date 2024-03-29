<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function checkout()
    {
        $app_key = env('PAYMENT_API_KEY');
        $app_secret = env('PAYMENT_API_SECRET');
        $timestamp = time();

        $hash_string = "$app_key:$app_secret:$timestamp";
        $hash = hash_hmac('sha256', $hash_string, $app_secret);

        $response = Http::post(env('PAYMENT_API_ENDPOINT') . '/services', [
            'appKey' => env('PAYMENT_API_KEY'),
            'timestamp' => $timestamp,
            'checkSum' => $hash,
        ])->json();

        if (isset($response['data']) && $response['status'] === '05') 
        {
            return response()->json(['data' => $response['data'], 'status' => 404]);

        } elseif (isset($response['data']))
        {
            $services = $response['data'];
            return response()->json(['data' => $services]);
        } else {
            return response()->json(['data' => $response]);
        }
    }

    public function process(Request $request)
    {
        if (!$request->channel) 
        {
            return response()->json(['error' => 'Channel is required.', 'status' => 400]);
        }
    
        if ($request->channel == 'aya_pay') 
        {
            if ($request->has('method') && in_array($request->method, ['QR', 'NOTI'])) {
                $method = $request->method;
            } else {
                return response()->json(['error' => 'Invalid or missing method for aya_pay channel.', 'status' => 400], 400);
            }
        } else {
            $method = 'WEB';
        }
    
        $merch_order_id = Str::uuid();

        $user = auth()->user();

        if (!$user) 
        {
            return response()->json(['error' => 'Unauthenticated user.', 'status' => 401]);
        }

        $amount = $request->input('amount', 0);

        $payment = Payment::create([
            'user_id' => $user->id,
            'allocation_id' => $request->allocation_id,
            'transcation_id' => $merch_order_id,
            'payment_status' => 'pending', 
            'amount' => $amount,
        ]);

        $payment->save();
    
        $app_key = env('PAYMENT_API_KEY');
        $app_secret = env('PAYMENT_API_SECRET');
        $timestamp = time();
        $currency_code = 104;
        $channel = $request->channel;
    
        $isSDK = false;
    
        $user_ref_1 = '';
        $user_ref_2 = '';
        $user_ref_3 = '';
        $user_ref_4 = '';
        $user_ref_5 = '';
        $description = 'Buy Courses from ILBC-Saungpokki';
    
        $hash_string = "$merch_order_id:$amount:$app_key:$timestamp:$user_ref_1:$user_ref_2:$user_ref_3:$user_ref_4:$user_ref_5:$description:$currency_code:$channel:$method";
    
        $hash = hash_hmac('sha256', $hash_string, $app_secret);
    
        $url = env("PAYMENT_API_ENDPOINT") . '/request';
    
        $data = [
            'merchOrderId' => $merch_order_id,
            'amount' => $amount,
            'appKey' => $app_key,
            'timestamp' => $timestamp,
            'userRef1' => $user_ref_1,
            'userRef2' => $user_ref_2,
            'userRef3' => $user_ref_3,
            'userRef4' => $user_ref_4,
            'userRef5' => $user_ref_5,
            'description' => $description,
            'currencyCode' => $currency_code,
            'channel' => $request->channel,
            'method' => $method,
            'isSDK' => $isSDK,
        ];
    
        $response = [
            'hash' => $hash,
            'data' => $data,
            'url' => $url,
        ];
    
        return response()->json(['data' => $response, 'status' => 200]);
    }

    public function submit(Request $request)
    {

        $method = 'NOTI';

        $merch_order_id = time() . rand(100000, 999999);

        $user = auth()->user();
        if (!$user) 
        {
            return response()->json(['error' => 'Unauthenticated user.', 'status' => 401]);
        }

        $amount = $request->input('amount', 0);

        $app_key = env('PAYMENT_API_KEY');
        $app_secret = env('PAYMENT_API_SECRET');
        $timestamp = time();
        $currency_code = 104;
        $channel = 'aya_pay';

        $isSDK = false;

        $user_ref_1 = '';
        $user_ref_2 = '';
        $user_ref_3 = '';
        $user_ref_4 = '';
        $user_ref_5 = '';
        $description = '';

        $hash_string = "$merch_order_id:$amount:$app_key:$timestamp:$user_ref_1:$user_ref_2:$user_ref_3:$user_ref_4:$user_ref_5:$description:$currency_code:$channel:$method";


        $hash = hash_hmac('sha256', $hash_string, $app_secret);

        $url = env("PAYMENT_API_ENDPOINT") . '/request';

        $data = [
            'merchOrderId' => $merch_order_id,
            'amount' => $amount,
            'appKey' => $app_key,
            'timestamp' => $timestamp,
            'userRef1' => $user_ref_1,
            'userRef2' => $user_ref_2,
            'userRef3' => $user_ref_3,
            'userRef4' => $user_ref_4,
            'userRef5' => $user_ref_5,
            'description' => $description,
            'currencyCode' => $currency_code,
            'channel' => $channel,
            'method' => $method,
            'isSDK' => $isSDK,
        ];

        $response = [
            'hash' => $hash,
            'data' => $data,
            'url' => $url,
        ];
    
        return response()->json(['data' => $response, 'status' => 200]);
    }

    public function enquiry ($orderId) 
    {

        $app_key = env('PAYMENT_API_KEY');
        $app_secret = env('PAYMENT_API_SECRET');
        $timestamp = time();

        $hash = hash_hmac('sha256', "$orderId:$timestamp:$app_key", $app_secret);

        $response = Http::post(env('PAYMENT_API_ENDPOINT') . '/enquiry', [
            'appKey' => env('PAYMENT_API_KEY'),
            'merchOrderId' => $orderId,
            'timestamp' => $timestamp,
            'checkSum' => $hash,
        ])->json();

        return $response;

        if (isset($response->success) && $response->success === false) 
        {
            return response()->json($response);
        }

        $payment = Payment::where('transcation_id', $orderId)->first();

        if (!$payment) 
        {
            return response()->json(['error' => 'Payment record not found.', 'status' => 404]);
        }

        $payment->update(['payment_status' => 'success']);

        return response()->json(['message' => 'Enquiry handled successfully.', 'status' => 200]);
    }
}
