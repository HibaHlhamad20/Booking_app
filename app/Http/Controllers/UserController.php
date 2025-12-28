<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $table = 'users';
    public function getUserBookings ()
    {
        $bookings= Auth::user()->bookings;
        return response()->json($bookings, 200);
    }

     public function pendingBookings()
    {
        $owner = Auth::user();

        if ($owner->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookings = Booking::where('status', 'pending')
            ->whereHas('apartment', function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            })
            ->with(['user', 'apartment'])
            ->get();

        return response()->json($bookings);
    }

    public function approveBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);

        if ($booking->apartment->owner_id !==  Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking already processed'], 400);
        }

        $booking->status = 'approved';
        $booking->save();

        return response()->json([
            'message' => 'Booking approved successfully',
            'booking' => $booking
        ]);
    }

    public function rejectBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);

        if ($booking->apartment->owner_id !==  Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking already processed'], 400);
        }

        $booking->status = 'rejected';
        $booking->save();

        return response()->json([
            'message' => 'Booking rejected successfully',
            'booking' => $booking
        ]);
    }   

    public function pendingUpdatedBookings()
    {
        $owner = Auth::user();

        if ($owner->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookings = Booking::where('update_status', 'pending')
            ->whereHas('apartment', function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            })
            ->with(['user', 'apartment'])
            ->get();

        return response()->json($bookings);
    }

    public function approveUpdatedBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);

        if ($booking->apartment->owner_id !==  Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->update_status !== 'pending') {
            return response()->json(['message' => 'Booking already processed'], 400);
        }

        if ($booking->new_from !==null && $booking->new_to ==null) {
           $booking->from=$booking->new_from;
           $booking->new_from = Carbon::parse($booking->new_from);
           $booking->total_price = $booking->new_total_price;
        }

        if ($booking->new_to !==null && $booking->new_from ==null) {
           $booking->to=$booking->new_to;
           $booking->new_to = Carbon::parse($booking->new_to);
           $booking->total_price = $booking->new_total_price;
        }

        if ($booking->new_to !==null && $booking->new_from !==null) {
           $booking->from=$booking->new_from;
           $booking->to=$booking->new_to;
           $booking->new_from = Carbon::parse($booking->new_from);
           $booking->new_to = Carbon::parse($booking->new_to);
           $booking->total_price = $booking->new_total_price;
        }

        if ($booking->new_guests !==null) {
           $booking->guests=$booking->new_guests;
        }

        $booking->update_status = 'approved';
        $booking->new_from = null;
        $booking->new_to = null;
        $booking->new_guests =null;
        $booking->new_total_price=null;
        $booking->save();

        return response()->json([
            'message' => 'Booking modification approved successfully',
            'booking' => $booking
        ]);
    }

    public function rejectUpdatedBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);

        if ($booking->apartment->owner_id !==  Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->update_status !== 'pending') {
            return response()->json(['message' => 'Booking already processed'], 400);
        }

        $booking->update_status = 'rejected';
        $booking->save();

        return response()->json([
            'message' => 'Booking rejected successfully',
            'booking' => $booking
        ]);
    }   

    public function addToFavorite ($apartment_id) {
        Apartment::findOrFail($apartment_id);
        Auth::user()->favoriteApartments()->syncWithoutDetaching($apartment_id);
        return response()->json(['message' => 'Apartment added to favorite'], 200);
    }

    public function removeFromFavorite ($apartment_id) {
        Apartment::findOrFail($apartment_id);
        Auth::user()->favoriteApartments()->detach($apartment_id);
        return response()->json(['message' => 'Apartment removed from favorite'], 200);
    }

    public function getFavoriteApartments () {
        $favoriteApartments = Auth::user()->favoriteApartments()->with('mainImage')->get();
        return response()->json($favoriteApartments, 200);
    }

}
