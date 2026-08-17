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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained('trains')->onDelete('cascade');
            $table->string('seat_number', 10);
            $table->string('coach', 10);
            $table->string('seat_class', 30)->default('SHOVON_CHAIR'); // 'AC_BERTH', 'SNIGDHA', 'SHOVON_CHAIR', 'SHOVON', etc.
            $table->timestamps();

            // Ensure unique seat identifier within a specific coach of a train
            $table->unique(['train_id', 'coach', 'seat_number'], 'unique_train_coach_seat');
            $table->index(['train_id', 'seat_class']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
