<?php

namespace App\Http\Controllers\Api;

use App\Models\{
    User,
    Setting,
};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;

class SettingController extends Controller
{
    public function get(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            // Select only the required fields
            $setting = Setting::select(
                'id',
                'minimum_body_weight',
                'minimum_luggage_weight',
                'minimum_body_weight_amount',
                'minimum_luggage_weight_amount',
                'tax',
                'one_way_base_fare',
                'both_way_base_fare',
                'one_way_convience_fee',
                'both_way_convience_fee'
            )->find($request->id);

            return response()->json([
                'status' => true,
                'message' => $setting ? 'Setting found successfully.' : 'No setting found.',
                'data' => $setting ?? (object)[],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:settings,id',
            'minimum_body_weight' => 'required|numeric|min:0',
            'minimum_luggage_weight' => 'required|numeric|min:0',
            'minimum_body_weight_amount' => 'required|numeric|min:0',
            'minimum_luggage_weight_amount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'one_way_base_fare' => 'required|numeric|min:0',
            'both_way_base_fare' => 'required|numeric|min:0',
            'both_way_convience_fee' => 'required|numeric|min:0',
            'one_way_convience_fee' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Try to update settings
        try {
            $setting = Setting::findOrFail($request->id);

            $setting->update([
                'minimum_body_weight' => $request->minimum_body_weight,
                'minimum_luggage_weight' => $request->minimum_luggage_weight,
                'minimum_body_weight_amount' => $request->minimum_body_weight_amount,
                'minimum_luggage_weight_amount' => $request->minimum_luggage_weight_amount,
                'tax' => $request->tax,
                'one_way_base_fare' => $request->one_way_base_fare,
                'both_way_base_fare' => $request->both_way_base_fare,
                'both_way_convience_fee' => $request->both_way_convience_fee,
                'one_way_convience_fee' => $request->one_way_convience_fee,
                'updated_by' => Auth::id(), // Optional, if you track who updated
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Settings updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }
    }

}
