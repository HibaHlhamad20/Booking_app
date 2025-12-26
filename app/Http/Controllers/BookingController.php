<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function addBooking (StoreBookingRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::user()->id;
        $validatedData['status'] = 'pending';
        $from = Carbon::parse($validatedData['from']);
        $to = Carbon::parse($validatedData['to']);
        $days = $from->diffInDays($to)+1;
        $apartment = Apartment::findOrFail($request->apartment_id);
        $pricePerDay = $apartment->price_per_day;
        $validatedData['total_price'] = $days * $pricePerDay;
        $booking = Booking::create($validatedData);  
        return response()->json($booking, 200);
    }

    public function cancelBooking ($id)
    {
        $user_id=Auth::user()->id;
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($booking->status === 'rejected') {
            return response()->json([
                'message' => 'Rejected booking cannot be cancelled'
            ], 400);
        }

        if (now()->toDateString() >= $booking->from) {
            return response()->json([
                'message' => 'Cannot cancel booking after it has started'
            ], 400);
        }
        $booking->status = 'cancelled';
        $booking->save();
        return response()->json($booking, 200);
    }

    public function updateBooking (UpdateBookingRequest $request,$id)
    {
        $user_id=Auth::user()->id;
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be updated'
            ], 400);
        }

        if (now()->toDateString() >= $booking->from) {
            return response()->json([
                'message' => 'Cannot update booking after it has started'
            ], 400);
        }


        $booking->update($request->validated());
        return response()->json($booking, 200);
    }

}
