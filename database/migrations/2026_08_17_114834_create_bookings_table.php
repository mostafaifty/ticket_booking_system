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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('train_schedule_id')->constrained('train_schedules')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats')->onDelete('cascade');
            $table->string('booking_code', 30)->unique()->index();
            $table->dateTime('booking_date')->index();
            $table->decimal('total_fare', 10, 2);
            $table->string('status', 20)->default('confirmed')->index(); // 'confirmed', 'pending', 'cancelled', 'refunded'
            $table->timestamps();

            // Index for querying schedule seat bookings by status
            $table->index(['train_schedule_id', 'seat_id', 'status'], 'schedule_seat_status_index');
            $table->index(['user_id', 'status']);
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
