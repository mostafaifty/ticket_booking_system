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
        Schema::create('trains', function (Blueprint $table) {
            $table->id();
            $table->string('train_number', 20)->unique()->index();
            $table->string('train_name');
            $table->string('train_type')->default('Intercity');
            $table->unsignedInteger('total_seats')->default(0);
            $table->string('status')->default('active')->index(); // 'active', 'inactive', 'maintenance'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trains');
    }
};
