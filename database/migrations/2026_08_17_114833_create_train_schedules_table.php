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
        Schema::create('train_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained('trains')->onDelete('cascade');
            $table->foreignId('departure_station_id')->constrained('stations')->onDelete('restrict');
            $table->foreignId('arrival_station_id')->constrained('stations')->onDelete('restrict');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->date('journey_date')->index();
            $table->decimal('fare', 10, 2)->default(0.00);
            $table->string('status', 20)->default('scheduled')->index(); // 'scheduled', 'delayed', 'departed', 'completed', 'cancelled'
            $table->timestamps();

            // Composite index for rapid search queries
            $table->index(['journey_date', 'departure_station_id', 'arrival_station_id'], 'schedule_search_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_schedules');
    }
};
