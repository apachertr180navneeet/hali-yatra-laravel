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
    PaymentType
};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth,NumberFormatter;
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
            'transactions.*.transaction_type' => 'required',
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
            $bookingDetail = BookingDetail::where('booking_id', $data['booking_id'])->first();

            if (!$bookingDetail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking detail not found.',
                ], 404);
            }

            $extraWeight = ExtraWeightBooking::updateOrCreate(
                ['booking_id' => $data['booking_id']],
                [
                    'extra_body_weight' => $data['extra_body_weight'] ?? 0,
                    'extra_luggage' => $data['extra_luggage'] ?? 0,
                    'extra_luggage_amount' => $data['extra_luggage_amount'] ?? 0,
                    'extra_body_weight_amount' => $data['extra_body_weight_amount'] ?? 0,
                    'total_amount' => $data['total_amount'],
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'payable_amount' => $data['payable_amount'],
                ]
            );

            // Format receipt number
            $receiptNumber = str_pad($extraWeight->id, 6, '0', STR_PAD_LEFT);

            // Convert ExtraWeight data to array and add receipt_number
            $extraWeightData = $extraWeight->toArray();
            $extraWeightData['receipt_number'] = $receiptNumber;

            // Format ExtraWeight dates
            if (!empty($extraWeightData['created_at'])) {
                $extraWeightData['created_at'] = Carbon::parse($extraWeightData['created_at'])->format('d/m/Y');
            }
            if (!empty($extraWeightData['updated_at'])) {
                $extraWeightData['updated_at'] = Carbon::parse($extraWeightData['updated_at'])->format('d/m/Y');
            }
            if (!empty($extraWeightData['updated_at'])) {
                $extraWeightData['payable_amount_in_words'] = $this->convertNumberToWords($extraWeightData['payable_amount']);
            }
            // Delete previous transactions
            Transiction::where('booking_id', $data['booking_id'])->forceDelete();

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

            // Format booking detail
            $bookingDetailData = $bookingDetail->toArray();

            if (!empty($bookingDetailData['created_at'])) {
                $bookingDetailData['created_at'] = Carbon::parse($bookingDetailData['created_at'])->format('d/m/Y');
            }
            if (!empty($bookingDetailData['updated_at'])) {
                $bookingDetailData['updated_at'] = Carbon::parse($bookingDetailData['updated_at'])->format('d/m/Y');
            }
            if (!empty($bookingDetailData['last_update_time'])) {
                $bookingDetailData['last_update_time'] = Carbon::parse($bookingDetailData['last_update_time'])->format('d/m/Y');
            }
            if (!empty($bookingDetailData['booking_date'])) {
                $bookingDetailData['booking_date'] = Carbon::parse($bookingDetailData['booking_date'])->format('d/m/Y');
            }

            if (!empty($bookingDetailData['boarding_date'])) {
                $bookingDetailData['boarding_date'] = Carbon::parse($bookingDetailData['boarding_date'])->format('d/m/Y');
            }

            $transictions = $data['transactions'];

            $transactionIds = [];

            foreach ($transictions as $transiction) {
                $transactionIds[] = $transiction['transaction_type']; // Assuming each transaction has an 'id' key
            }

            $paymentTypes = PaymentType::whereIn('id', $transactionIds)->get();

            $paymentTypeNames = $paymentTypes->pluck('type_name')->toArray();

            $commaSeparatedNames = implode(', ', $paymentTypeNames);

            $extraWeightData['payment_type'] = $commaSeparatedNames;

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Extra weight and transaction details saved successfully.',
                'data' => [
                    'extra_weight' => $extraWeightData,
                    'booking_detail' => $bookingDetailData,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to save data: ' . $e->getMessage(),
            ], 500);
        }
    }


    private function convertNumberToWords($number)
    {
        $fmt = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        return ucfirst($fmt->format($number)) . ' only';
    }




}
