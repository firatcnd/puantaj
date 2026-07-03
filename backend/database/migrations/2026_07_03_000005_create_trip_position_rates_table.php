<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_position_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->decimal('rate', 10, 2);
            $table->timestamps();

            // Aynı sefer + pozisyon için ikinci kez ücret tanımlanamaz (iş kuralı, DB seviyesinde garanti).
            $table->unique(['trip_id', 'position_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_position_rates');
    }
};
