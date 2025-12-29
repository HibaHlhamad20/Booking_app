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
        'from' => 'required_without:new_from|date',
        'to' => 'required_without:new_to|date|after:from',
        'guests' => 'required|integer',
        'new_from' => 'nullable|date',
        'new_to' => 'nullable|date|after:from'
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