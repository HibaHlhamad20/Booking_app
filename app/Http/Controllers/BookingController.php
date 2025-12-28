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

    public function updateBooking (Request $request,$id)
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

        if ($booking->new_to <= $booking->new_from) {
            return response()->json([
                'message' => 'Please enter a valid date'
            ], 400);
        }

        if ($request->new_from !==null && $request->new_to ==null) {
           $booking->new_from=$request->new_from;
           $booking->new_from = Carbon::parse($booking->new_from);
           $days = $booking->new_from->diffInDays($booking->to)+1;
           $pricePerDay = $booking->apartment->price_per_day;
           $booking->new_total_price = $days * $pricePerDay;
        }

        if ($request->new_to !==null && $request->new_from ==null) {
           $booking->new_to=$request->new_to;
           $booking->new_to = Carbon::parse($booking->new_to);
           $days = $booking->from->diffInDays($booking->new_to)+1;
           $pricePerDay = $booking->apartment->price_per_day;
           $booking->new_total_price = $days * $pricePerDay;
        }

        if ($request->new_to !==null && $request->new_from !==null) {
           $booking->new_from=$request->new_from;
           $booking->new_to=$request->new_to;
           $booking->new_from = Carbon::parse($booking->new_from);
           $booking->new_to = Carbon::parse($booking->new_to);
           $days = $booking->new_from->diffInDays($booking->new_to)+1;
           $pricePerDay = $booking->apartment->price_per_day;
           $booking->new_total_price = $days * $pricePerDay;
        }

        if ($request->new_guests !==null) {
           $booking->new_guests=$request->new_guests;
        }

        $booking->update_status='pending';
        $booking->save();
        return response()->json($booking, 200);
    }

}
