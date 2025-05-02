<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    PaymentType,
};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use Carbon\Carbon;
use App\Imports\{
    BookingImport,
    BookingDetailsImport
};
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    
    public function bookingExcelImport(Request $request)
    {
        $request->validate([
            'excel' => 'required|mimes:csv,xlsx,xls',
            'type'=>'required|in:booking,userbybooking',
        ]);

        try {
            if($request->type == "booking"){
                // Step 1: Create object manually
                $import = new BookingImport();

                // Step 2: Pass object into import
                Excel::import($import, $request->file('excel'));

                // Step 3: Get duplicate count
                $duplicateCount = $import->getDuplicateCount();

                // Step 4: Create message
                if ($duplicateCount > 0) {
                    $message = "Booking data imported successfully. $duplicateCount duplicate entries were skipped.";
                } else {
                    $message = "Booking data imported successfully. No duplicate entries found.";
                }
            }else{
                $import = new BookingDetailsImport(); // Create instance

                // Perform import
                Excel::import($import, $request->file('excel'));

                // Get skipped booking IDs
                $skippedIds = $import->getSkippedBookingIds();

                // Prepare message
                $message = "Booking Detail data imported successfully.";

                // if (!empty($skippedIds)) {
                //     $message .= " Skipped booking IDs: $skippedIds";
                // }
            }

            return response()->json([
                'status' => true,
                'message' => $message,
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Excel validation failed.',
                'errors' => $e->failures(), // This will give row and column errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while importing the file: ' . $e->getMessage(),
            ], 500);
        }
    }

}
