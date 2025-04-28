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
use App\Imports\BookingImport;
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
                Excel::import(new BookingImport, $request->file('excel'));
                $message = "Booking data imported successfully";
            }else{
                $message = "hello";
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
