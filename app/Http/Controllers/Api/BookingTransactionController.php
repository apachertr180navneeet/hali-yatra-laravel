<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    Booking,
    BookingDetail,
    ExtraWeightBooking,
    Transiction,
};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use Carbon\Carbon;

class BookingTransactionController extends Controller
{
    public function storeOrUpdateExtraWeightBooking(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'booking_id' => 'required',
            'extra_body_weight' => 'nullable|numeric',
            'extra_luggage' => 'nullable|numeric',
            'extra_luggage_amount' => 'nullable|numeric',
            'extra_body_weight_amount' => 'nullable|numeric',
            'total_amount' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'payable_amount' => 'required|numeric',

            'transactions' => 'required|array',
            'transactions.*.transaction_type' => 'required|string',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.remark' => 'nullable|string',
            'transactions.*.transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        DB::beginTransaction();
        $user = JWTAuth::parseToken()->authenticate();

        try {
            // Save or update extra weight booking
            $extraWeight = ExtraWeightBooking::updateOrCreate(
                ['booking_id' => $data['booking_id']],
                [
                    'extra_body_weight' => $data['extra_body_weight'] ?? 0,
                    'extra_luggage' => $data['extra_luggage'] ?? 0,
                    'extra_luggage_amount' => $data['extra_luggage_amount'] ?? 0,
                    'extra_body_weight_amount' => $data['extra_body_weight_amount'] ?? 0,
                    'total_amount' => $data['total_amount'],
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'payable_amount' => $data['payable_amount']
                ]
            );

            // Optionally delete existing related transactions
            $transactions = Transiction::where('booking_id', $data['booking_id'])->get();

            foreach ($transactions as $transaction) {
                $transaction->forceDelete();
            }

            // Create new transactions
            foreach ($data['transactions'] as $txn) {
                
                Transiction::create([
                    'transiction_type' => $txn['transaction_type'],
                    'booking_id' => $data['booking_id'],
                    'amount' => $txn['amount'],
                    'remark' => $txn['remark'] ?? null,
                    'transaction_id' => $txn['transaction_id'] ?? null,
                    'created_by' => $user->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Extra weight and transaction details saved successfully.',
                'data' => $extraWeight,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ], 500);
        }
    }

}
