<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    Booking,
    BookingDetail,
    ExtraWeightBooking,
    Transiction
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
            // Subquery: Get the latest booking_detail ID per booking_id
            $subQuery = BookingDetail::selectRaw('MAX(id) as id')
                ->groupBy('booking_id');

            // Main query: Join latest booking_detail with bookings
            $query = BookingDetail::joinSub($subQuery, 'latest_booking', function ($join) {
                    $join->on('booking_detail.id', '=', 'latest_booking.id');
                })
                ->join('bookings', 'booking_detail.booking_id', '=', 'bookings.booking_id')
                ->select(
                    'booking_detail.*',
                    'bookings.no_of_passengers',
                    'bookings.total_amount',
                    'bookings.booking_base_fare',
                    'bookings.booking_convenience_fee',
                    'bookings.booking_convenience_fee_tax',
                    'bookings.booking_base_fare_tax'
                );

            // Filter by booking_id
            if ($request->has('booking_id') && !empty($request->booking_id)) {
                $query->where('booking_detail.booking_id', $request->booking_id);
            }

            // Filter by booking date range
            if ($request->filled('booking_start_date') && $request->filled('booking_end_date')) {
                $query->whereBetween('booking_detail.booking_date', [
                    $request->booking_start_date,
                    $request->booking_end_date
                ]);
            }

            // Filter by boarding date range
            if ($request->filled('boarding_start_date') && $request->filled('boarding_end_date')) {
                $query->whereBetween('booking_detail.boarding_date', [
                    $request->boarding_start_date,
                    $request->boarding_end_date
                ]);
            }

            // Paginate the results
            $bookings = $query->orderBy('booking_detail.created_at', 'desc')->paginate(10);

            // If no records found
            if ($bookings->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking Data not found',
                ], 200);
            }

            // Format response: convert nulls to "" and format dates
            $bookings->getCollection()->transform(function ($booking) {
                return collect($booking)->map(function ($value, $key) {
                    // Format date fields
                    if (in_array($key, ['booking_date', 'boarding_date']) && !empty($value)) {
                        return Carbon::parse($value)->format('d/m/Y');
                    }

                    // Default: convert nulls to empty string
                    return $value === null ? "" : $value;
                });
            });

            // Return success
            return response()->json([
                'status' => true,
                'message' => 'Bookings found successfully.',
                'bookings' => $bookings,
            ], 200);

        } catch (Exception $e) {
            // Return error
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
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
            // Load booking with passenger details
            $booking = Booking::with('bookingDetails')
                ->where('booking_id', $request->booking_id)
                ->first();

            $extraweightbooking = ExtraWeightBooking::where('booking_id', $request->booking_id)
            ->select([
                'extra_body_weight',
                'extra_luggage',
                'extra_body_weight_amount',
                'extra_luggage_amount',
                'total_amount',
                'discount_amount',
                'payable_amount'
            ])
            ->first();


            $transictionData = Transiction::join('payment_type', 'transictions.transiction_type', '=', 'payment_type.id')
            ->where('transictions.booking_id', $request->booking_id)
            ->select(
                'transictions.transiction_type',
                'transictions.booking_id',
                'transictions.amount',
                'transictions.remark',
                'transictions.trasiction_id',
                'payment_type.type_name as payment_type_name'
            )
            ->get();

            // If no booking found, return error
            if (!$booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking data not found.',
                ], 200);
            }

            // Split journey string (e.g., "CityA - CityB")
            $journeyParts = explode('-', $booking->journey ?? '');
            $journey_start = isset($journeyParts[0]) ? trim($journeyParts[0]) : '';
            $journey_end = isset($journeyParts[1]) ? trim($journeyParts[1]) : '';

            // Format each passenger
            $passengers = $booking->bookingDetails->map(function ($passenger) {
                return [
                    'name' => $passenger->passenger_name ?? '',
                    'age' => $passenger->age ?? '',
                    'gender' => $passenger->gender ?? '',
                    'yatra_reg_id' => $passenger->yatra_reg_id ?? '',
                    'mobile_no' => $passenger->mobile_no ?? '',
                    'government_id' => $passenger->government_id ?? '',
                    'government_id_type' => $passenger->government_id_type ?? '',
                    'passenger_booking_status' => $passenger->passenger_booking_status ?? '',
                ];
            });

            // Format booking response
            $formattedBooking = [
                'id' => $booking->id,
                'operator_name' => $booking->operator_name ?? '',
                'booking_id' => $booking->booking_id ?? '',
                'transaction_id' => $booking->transaction_id ?? '',
                'group_id' => $booking->group_id ?? '',
                'booking_status' => $booking->status ?? '',
                'last_update_time' => $booking->last_update_time ?? '',
                'booking_type' => $booking->booking_type ?? '',
                'booking_date' => $booking->booking_date ?? '',
                'journey' => $booking->journey ?? '',
                'journey_start' => $journey_start,
                'journey_end' => $journey_end,
                'boarding_date' => $booking->boarding_date ?? '',
                'time_slot' => $booking->time_slot ?? '',
                'return_type' => $booking->return_type ?? '',
                'total_amount' => $booking->total_amount ?? '',
                'booking_base_fare' => $booking->booking_base_fare ?? '',
                'booking_base_fare_tax' => $booking->booking_base_fare_tax ?? '',
                'booking_convenience_fee' => $booking->booking_convenience_fee ?? '',
                'booking_convenience_fee_tax' => $booking->booking_convenience_fee_tax ?? '',
                'passengers' => $passengers,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Booking found successfully.',
                'booking' => $formattedBooking,
                'orverweghtcharages' => $extraweightbooking ?? [],
                'transiction' => $transictionData ?? [],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 200);
        }
    }

}
