<?php

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

        // Mevcut pozisyonlar (Şoför, Host, Muavin) saha pozisyonlarıdır → Operasyon.
        // Migration içinde Eloquent modeli yerine query builder kullanılır; böylece
        // model event'leri (ör. activity log) tetiklenmez ve migration sırasına bağlı
        // hatalar oluşmaz.
        $operasyonId = DB::table('departments')->where('name', 'Operasyon')->value('id')
            ?? DB::table('departments')->insertGetId([
                'name' => 'Operasyon',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('positions')->whereNull('department_id')->update(['department_id' => $operasyonId]);

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
