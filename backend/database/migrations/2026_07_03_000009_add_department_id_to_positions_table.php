<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('name')
                ->constrained()->restrictOnDelete();
        });

        // Mevcut pozisyonlar (Şoför, Host, Muavin) saha pozisyonlarıdır → Operasyon
        $operasyon = Department::firstOrCreate(['name' => 'Operasyon']);
        DB::table('positions')->whereNull('department_id')->update(['department_id' => $operasyon->id]);

        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
