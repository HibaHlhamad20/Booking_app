<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Rating;
use Auth;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function addRating(Request $request, $booking_id) {
        $user_id=Auth::user()->id;
        $validated = $request->validate([
         'rate' => 'required|integer|between:1,5'
        ]);
        $booking = Booking::findOrFail($booking_id);
        if ($booking->user_id !== $user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        if (now()->toDateString() < $booking->from) {
            return response()->json([
                'message' => 'Cannot rete the apartment befor the booking starts'
            ], 400);
        }

        $rating=Rating::create([
            'user_id'=>$user_id,
            'apartment_id'=>$booking->apartment_id,
            'rate' =>$validated['rate']
        ]);

        $apartment = Apartment::find($booking->apartment_id);
        $apartment->average_rate = $apartment->ratings()->avg('rate');
        $apartment->save();

        return response()->json($rating, 200);
    }
}
