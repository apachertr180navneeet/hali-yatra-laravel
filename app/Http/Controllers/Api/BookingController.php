<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    Booking,
    BookingDetail
};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function list(Request $request) 
    {
        try {
            $query = Booking::query();

            // Filter by booking_id
            if ($request->filled('booking_id')) {
                $query->where('booking_id', $request->booking_id);
            }

            // Filter by booking date range
            if ($request->filled('booking_start_date') && $request->filled('booking_end_date')) {
                $query->whereBetween('booking_date', [
                    $request->booking_start_date,
                    $request->booking_end_date
                ]);
            }


            if ($request->filled('boarding_start_date') && $request->filled('boarding_end_date')) {
                $query->whereBetween('boarding_date', [
                    $request->boarding_start_date,
                    $request->boarding_end_date
                ]);
            }

            // Order by latest and paginate
            $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

            // Check if empty
            if ($bookings->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking Data not found',
                ], 200);
            }

            // Replace nulls with empty strings
            $bookings->getCollection()->transform(function ($booking) {
                return collect($booking)->map(function ($value) {
                    return $value === null ? "" : $value;
                });
            });

            return response()->json([
                'status' => true,
                'message' => 'Bookings found successfully.',
                'bookings' => $bookings,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }


    public function bookingviauserlist(Request $request)
    {
        try {
            // Start query with join on bookings table
            $query = BookingDetail::join('bookings', 'booking_detail.booking_id', '=', 'bookings.booking_id')
                ->select('booking_detail.*', 'bookings.no_of_passengers'); // Add required booking fields here

            // Filter by booking_id if provided
            if ($request->has('booking_id') && !empty($request->booking_id)) {
                $query->where('booking_detail.booking_id', $request->booking_id);
            }

            // Filter by booking date range if both dates are provided
            if ($request->filled('booking_start_date') && $request->filled('booking_end_date')) {
                $query->whereBetween('booking_detail.booking_date', [
                    $request->booking_start_date,
                    $request->booking_end_date
                ]);
            }

            // Filter by boarding date range if both dates are provided
            if ($request->filled('boarding_start_date') && $request->filled('boarding_end_date')) {
                $query->whereBetween('booking_detail.boarding_date', [
                    $request->boarding_start_date,
                    $request->boarding_end_date
                ]);
            }

            // Order and paginate results
            $bookings = $query->orderBy('booking_detail.created_at', 'desc')->paginate(10);

            // Check if bookings are empty
            if ($bookings->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking Data not found',
                ], 200);
            }

            // Convert null values to empty strings
            $bookings->getCollection()->transform(function ($booking) {
                return collect($booking)->map(function ($value) {
                    return $value === null ? "" : $value;
                });
            });

            // Success response
            return response()->json([
                'status' => true,
                'message' => 'Bookings found successfully.',
                'bookings' => $bookings,
            ], 200);

        } catch (Exception $e) {
            dd($e);
            // Error response
            return response()->json([
                'status' => false,
                'message' => $e,
            ], 200);
        }
    }



    public function detail(Request $request) 
    {
        try {
            $bookings = Booking::where('booking_id', $request->booking_id)->first();

            // Check if there are no bookings
            if (!$bookings) {
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
 
    public function detailuser(Request $request) 
    {
        try {
            $booking = BookingDetail::where('booking_detail.booking_id', $request->booking_id)
                ->join('bookings', 'bookings.booking_id', '=', 'booking_detail.booking_id')
                ->select(
                    'booking_detail.*',
                    'bookings.total_amount',
                    'bookings.booking_base_fare',
                    'bookings.booking_convenience_fee',
                    'bookings.booking_convenience_fee_tax',
                    'bookings.booking_base_fare_tax',
                )
                ->first();

            if (!$booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking Data not found',
                ], 200);
            }

            // Split by hyphen
            $journeyParts = explode('-', $booking->journey);
            $booking->journey_start = isset($journeyParts[0]) ? trim($journeyParts[0]) : null;
            $booking->journey_end = isset($journeyParts[1]) ? trim($journeyParts[1]) : null;

            return response()->json([
                'status' => true,
                'message' => 'Booking found successfully.',
                'bookings' => $booking,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
    


}
