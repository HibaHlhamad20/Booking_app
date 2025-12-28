<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained('apartments')->cascadeOnDelete();
            $table->date('from');
            $table->date('to');
            $table->integer('guests');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
            ->default('pending');
            $table->decimal('total_price', 10, 2);
            $table->enum('update_status', ['pending', 'approved', 'rejected'])
            ->nullable();
            $table->date('new_from')->nullable();
            $table->date('new_to')->nullable();
            $table->integer('new_guests')->nullable();
            $table->decimal('new_total_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
