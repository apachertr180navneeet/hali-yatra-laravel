<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    Booking
};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function list() 
    {
        try {
            // Fetch booking data with pagination, ordered by created_at in descending order (limit 10)
            $bookings = Booking::orderBy('created_at', 'desc')->paginate(10);

            // Check if there are no bookings
            if ($bookings->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking Data not found',
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'bookings found successfully.',
                'bookings' => $bookings,  // Include the booking data in the response
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

}
