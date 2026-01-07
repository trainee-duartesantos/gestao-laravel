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
        Schema::create('entities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('number')->unique();          // incremental interno
            $table->string('nif_normalized', 32)->unique();          // indexável/unique (VAT/NIF normalizado)
            $table->string('name');

            // sensível (cifrado via casts no Model)
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()
                ->constrained('countries')->nullOnDelete();

            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();

            $table->text('notes')->nullable();

            // sensível + hash indexável
            $table->string('email')->nullable();
            $table->char('email_hash', 64)->nullable()->index();

            $table->string('phone')->nullable();
            $table->char('phone_hash', 64)->nullable()->index();

            $table->string('mobile')->nullable();
            $table->char('mobile_hash', 64)->nullable()->index();

            $table->string('website')->nullable();

            $table->boolean('gdpr_consent')->default(false);
            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);

            $table->string('status', 20)->default('active'); // active/inactive

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
