<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'apartment_id' => 'required|exists:apartments,id',
        'from' => 'required_without:new_from|date|after_or_equal:today',
        'to' => 'required_without:new_to|date|after:from',
        'guests' => 'required|integer',
        'new_from' => 'nullable|date|after_or_equal:today',
        'new_to' => 'nullable|date|after:new_from'
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {

        $from = $this->from;
        $to = $this->to;
        $apartmentId = $this->apartment_id;

        if (!$from || !$to || !$apartmentId) {
            return;
        }

        // التحقق من عدم وجود حجز سابق لنفس المستخدم على نفس الشقة في نفس الفترة
        $userConflict = Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('from', [$from, $to])
                    ->orWhereBetween('to', [$from, $to])
                    ->orWhere(function ($q) use ($from, $to) {
                        $q->where('from', '<=', $from)
                            ->where('to', '>=', $to);
                    });
            })
            ->exists();

        if ($userConflict) {
            return response()->json([
                'message' => 'لديك حجز سابق على هذه الشقة في نفس الفترة'
            ], 400);
        }
        
        $conflictingBooking = Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['approved'])
            ->where(function ($query) use ($from, $to) {
                $query->where('from', '<', $to)
                      ->where('to', '>', $from);
            })
            ->first();

        if ($conflictingBooking) {
            $validator->errors()->add(
                'from',
                "This apartment is booked from {$conflictingBooking->from} to {$conflictingBooking->to}. Please select a different date."
            );
        }
    });
}

}