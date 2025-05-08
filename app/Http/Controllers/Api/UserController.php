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
use Illuminate\Support\Facades\Storage;
use Mail,Hash,File,DB,Helper,Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    public function list() 
    {
        try {
            $query = User::query();

            // Filter for operators and paginate
            $operators = $query->where('role', 'operator')->orderBy('created_at', 'desc')->paginate(10);

            // Check if empty
            if ($operators->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Operator not found',
                ], 200);
            }

            // Modify each operator data
            $operators->getCollection()->transform(function ($operator) {
                // Convert nulls to empty strings and append URL to avatar
                return collect($operator)->map(function ($value, $key) use ($operator) {
                    if ($key === 'avatar') {
                        return $value ? url('storage/' . $value) : '';
                    }
                    return $value === null ? "" : $value;
                });
            });

            return response()->json([
                'status' => true,
                'message' => 'Operators found successfully.',
                'paymenttype' => $operators,
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
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|digits_between:10,15|unique:users,phone',
            'address'  => 'nullable|string|max:255',
            'profile'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            // Upload avatar if present
            $avatarPath = null;
            if ($request->hasFile('profile')) {
                $avatarPath = $request->file('profile')->store('avatars', 'public');
            }
            
            // Create the user with default password
            $user = User::create([
                'full_name'  => $request->name,
                'email'      => $request->email,
                'phone'      => $request->mobile,
                'address'    => $request->address,
                'password'   => Hash::make('123456789'),
                'created_by' => Auth::id(),
                'role'       => 'operator',
                'avatar'     => $avatarPath, // assuming `avatar` column exists in users table
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'User created successfully.',
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::select('id', 'full_name', 'email', 'phone', 'address', 'avatar')
                        ->where('id', $request->id)
                        ->first();

            $userArray = collect($user)->map(function ($value, $key) {
                if ($key === 'avatar' && $value !== null) {
                    return asset('storage/avatars/' . $value); // Only one full path
                }
                return $value === null ? "" : $value;
            });

            return response()->json([
                'status' => true,
                'message' => 'User found successfully.',
                'user' => $userArray,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request)
    {
        // Validate request input
        $validator = Validator::make($request->all(), [
            'id'        => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $request->id,
            'mobile'     => 'required|digits_between:10,15|unique:users,phone,' . $request->id,
            'address'   => 'nullable|string|max:255',
            'profile'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            // Find user
            $user = User::findOrFail($request->id);

            // Handle avatar upload and delete old one
            if ($request->hasFile('profile')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $avatarPath = $request->file('profile')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Update fields
            $user->full_name  = $request->name;
            $user->email      = $request->email;
            $user->phone      = $request->mobile;
            $user->address    = $request->address;
            $user->updated_by = Auth::id();
            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'User updated successfully.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function delete(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // Delete the payment type
        try {
            $paymenttype = User::where('id',$request->id)->first();
            $paymenttype->delete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully.',
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
            $query = User::onlyTrashed();

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
            $paymenttype = User::withTrashed()->where('id',$request->id)->first();
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
            $paymenttype = User::withTrashed()->where('id',$request->id)->first();
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
            $paymenttype = User::where('id',$request->id)->first();
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
}
