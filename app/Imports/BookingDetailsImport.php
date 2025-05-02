<?php

namespace App\Imports;

use App\Models\{BookingDetail, Booking};
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class BookingDetailsImport implements ToModel, WithHeadingRow
{
    // Store skipped booking IDs
    private $skippedBookingIds = [];

    public function model(array $row)
    {
        // Check if booking_id exists in the Booking table
        $existingBooking = Booking::where('booking_id', $row['booking_id'])->first();

        // If not exists, skip and store booking_id
        if (!$existingBooking) {
            $this->skippedBookingIds[] = $row['booking_id'];
            return null; // Skip this row
        }

        // Create new BookingDetail
        return new BookingDetail([
            'operator_name'              => $row['operator_name'],
            'booking_id'                 => $row['booking_id'],
            'transaction_id'             => $row['transaction_id'],
            'group_id'                   => $row['group_id'],
            'booking_status'             => $row['booking_status'],
            'passenger_booking_status'   => $row['passenger_booking_status'],
            'last_update_time'           => Carbon::parse($row['last_update_time']),
            'booking_type'               => $row['booking_type'],
            'booking_date'               => Carbon::parse($row['booking_date']),
            'journey'                    => $row['journey'],
            'boarding_date'              => Carbon::parse($row['boarding_date']),
            'time_slot'                  => $row['time_slot'],
            'return_type'                => $row['return_type'],
            'passenger_name'             => $row['passenger_name'],
            'gender'                     => $row['gender'],
            'government_id_type'         => $row['government_id_type'],
            'government_id'              => $row['government_id'],
            'mobile_no'                  => $row['mobile_no'],
            'yatra_reg_id'               => $row['yatra_reg_id'],
            'booker_mobile_number'       => $row['booker_mobile_number'],
            'created_at'                 => $row['created_at'] ?? now(),
            'updated_at'                 => $row['updated_at'] ?? now(),
        ]);
    }

    // Public method to retrieve skipped booking IDs as comma-separated string
    public function getSkippedBookingIds()
    {
        return implode(',', $this->skippedBookingIds);
    }
}
