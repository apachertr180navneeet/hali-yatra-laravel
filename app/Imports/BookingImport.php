<?php

namespace App\Imports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class BookingImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Check if booking_id already exists in the database
        $existingBooking = Booking::where('booking_id', $row['booking_id'])->first();

        // If booking_id exists, return the existing record, otherwise create a new one
        if ($existingBooking) {
            return $existingBooking; // Skip inserting, returning the existing record
        }

        // If booking_id does not exist, create a new record
        return new Booking([
            'booking_id' => $this->nullOrString($row['booking_id']),
            'operator_name' => $this->nullOrString($row['operator_name']),
            'flown_by' => $this->nullOrString($row['flown_by']),
            'transaction_id' => $this->nullOrString($row['transaction_id']),
            'booking_type' => $this->nullOrString($row['booking_type']),
            'booking_date' => $this->parseDate($row['booking_date']),
            'boarding_date' => $this->parseDate($row['boarding_date']),
            'return_type' => $this->nullOrString($row['return_type']),
            'journey' => $this->nullOrString($row['journey']),
            'time_slot' => $this->nullOrString($row['time_slot']),
            'no_of_passengers' => $this->nullOrZero($row['no_of_passengers']),
            'total_amount' => $this->nullOrZero($row['total_amount']),
            'booking_base_fare' => $this->nullOrZero($row['booking_base_fare']),
            'booking_base_fare_tax' => $this->nullOrZero($row['booking_base_fare_tax']),
            'booking_convenience_fee' => $this->nullOrZero($row['booking_convenience_fee']),
            'booking_convenience_fee_tax' => $this->nullOrZero($row['booking_convenience_fee_tax']),
            'status' => $this->nullOrString($row['status']),
            'pg_name' => $this->nullOrString($row['pg_name']),
            'cancellation_date' => $this->parseDate($row['cancellationdate']),
            'remarks' => $this->nullOrString($row['remarks']),
            'refund_amount' => $this->nullOrZero($row['refund_amount']),
        ]);
    }

    private function parseDate($date)
    {
        if (empty($date) || $date == '?') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function nullOrZero($value)
    {
        return ($value === null || trim($value) === '') ? 0 : $value;
    }

    private function nullOrString($value)
    {
        return ($value === null || trim($value) === '') ? null : $value;
    }
}
