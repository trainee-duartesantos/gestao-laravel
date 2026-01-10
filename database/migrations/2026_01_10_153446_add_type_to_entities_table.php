<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            // nullable durante a transição, depois podemos tornar NOT NULL
            $table->string('type', 20)->nullable()->after('is_supplier')->index();
        });

        // Backfill do type a partir de is_customer/is_supplier
        DB::table('entities')
            ->where('is_customer', 1)
            ->where('is_supplier', 1)
            ->update(['type' => 'both']);

        DB::table('entities')
            ->where('is_customer', 1)
            ->where('is_supplier', 0)
            ->update(['type' => 'client']);

        DB::table('entities')
            ->where('is_customer', 0)
            ->where('is_supplier', 1)
            ->update(['type' => 'supplier']);

        // Se existirem registos com ambos a 0, deixamos null por agora
        // (ou podes forçar 'client' por defeito, se fizer sentido no teu projeto)
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
