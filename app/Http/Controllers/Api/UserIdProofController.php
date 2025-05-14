<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{
    User,
    UserIdProof,
};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail,Hash,File,DB,Helper,Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class UserIdProofController extends Controller
{
    public function list() 
    {
        try {
            $query = UserIdProof::query();

            // Order by latest and paginate
            $paymenttype = $query->orderBy('user_id_proof_order', 'asc')->paginate(10);

            // Check if empty
            if ($paymenttype->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Id Proof not found',
                ], 200);
            }

            // Replace nulls with empty strings
            $paymenttype->getCollection()->transform(function ($paymenttype) {
                return collect($paymenttype)->map(function ($value) {
                    return $value === null ? "" : $value;
                });
            });

            return response()->json([
                'status' => true,
                'message' => 'Ticket Type found successfully.',
                'paymenttype' => $paymenttype,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_id_proof', 'name')->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Create a new payment type
        try {
            $paymenttype = UserIdProof::create([
                'name' => $request->name,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Id Proof created successfully.',
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function get(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:ticket_type,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Get the payment type
        try {
            $paymenttype = UserIdProof::where('id',$request->id)->first();
            return response()->json([
                'status' => true,
                'message' => 'User Id Proof found successfully.',
                'paymenttype' => $paymenttype,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function update(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_id_proof,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Update the payment type
        try {
            $paymenttype = UserIdProof::where('id',$request->id)->first();
            $paymenttype->update([
                'name' => $request->name,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Id Proof found updated successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
    
    public function delete(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:payment_type,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Delete the payment type
        try {
            $paymenttype = UserIdProof::where('id',$request->id)->first();
            $paymenttype->delete();

            return response()->json([
                'status' => true,
                'message' => 'Payment Type deleted successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function trashed()
    {
        try {
            $query = UserIdProof::onlyTrashed();

            // Order by latest and paginate
            $paymenttype = $query->orderBy('created_at', 'desc')->paginate(10);

            // Check if empty
            if ($paymenttype->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment Type not found',
                ], 200);
            }

            // Replace nulls with empty strings
            $paymenttype->getCollection()->transform(function ($paymenttype) {
                return collect($paymenttype)->map(function ($value) {
                    return $value === null ? "" : $value;
                });
            });

            return response()->json([
                'status' => true,
                'message' => 'Payment Type found successfully.',
                'paymenttype' => $paymenttype,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
    public function restore(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:payment_type,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Restore the payment type
        try {
            $paymenttype = UserIdProof::withTrashed()->where('id',$request->id)->first();
            $paymenttype->restore();

            return response()->json([
                'status' => true,
                'message' => 'Payment Type restored successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function forceDelete(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:payment_type,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Force delete the payment type
        try {
            $paymenttype = UserIdProof::withTrashed()->where('id',$request->id)->first();
            $paymenttype->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Payment Type deleted successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
    public function status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:payment_type,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Update the payment type status
        try {
            $paymenttype = UserIdProof::where('id',$request->id)->first();
            $paymenttype->update([
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment Type status updated successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function order(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'order' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $orders = array_map('trim', explode(',', $request->order));

        // Update the payment type order
        try {
            foreach ($orders as $key => $id) {
                $orderkey = $key+1;
                UserIdProof::where('id', $id)->update(['user_id_proof_order' => $orderkey]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment Type order updated successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
}
