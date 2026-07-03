<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timesheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->restrictOnDelete();
            $table->date('duty_date');
            $table->unsignedSmallInteger('trip_count');
            // Ücret snapshot: sefer ücreti sonradan güncellense bile
            // onaylanmış puantaj tutarları değişmez.
            $table->decimal('unit_rate', 10, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index('duty_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_entries');
    }
};
