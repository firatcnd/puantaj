<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->restrictOnDelete();
            // Puantaj oluşturulduğu andaki pozisyon; personelin pozisyonu sonradan
            // değişse bile geçmiş puantajların bütünlüğü korunur.
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('work_days')->default(0);
            $table->unsignedTinyInteger('leave_days')->default(0);
            $table->unsignedTinyInteger('sick_days')->default(0);
            $table->unsignedTinyInteger('public_holiday_days')->default(0);
            $table->unsignedTinyInteger('weekend_days')->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('undertime_hours', 6, 2)->default(0);
            $table->text('description')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            // "Aynı personel için aynı ay içinde tek puantaj" kuralı soft delete ile
            // çakışmaması için DB unique yerine application-level (FormRequest) doğrulanır.
            $table->index(['personnel_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
