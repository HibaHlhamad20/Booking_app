<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $guarded=[];

    public function booking() {
    return $this->belongsTo(Booking::class);
   }

   public function apartment() {
    return $this->belongsTo(Apartment::class);
   }

   public function user() {
    return $this->belongsTo(User::class);
   }

}
