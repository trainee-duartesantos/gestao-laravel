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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('entity_id')
                ->constrained('entities')
                ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name')->nullable();

            // sensível + hash indexável
            $table->string('email')->nullable();
            $table->char('email_hash', 64)->nullable()->index();

            $table->string('phone')->nullable();
            $table->char('phone_hash', 64)->nullable()->index();

            $table->string('role')->nullable(); // mais tarde podemos ligar a tabela roles/funções
            $table->boolean('gdpr_consent')->default(false);

            $table->string('status', 20)->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
