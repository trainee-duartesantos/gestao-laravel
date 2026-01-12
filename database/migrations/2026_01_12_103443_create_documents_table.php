<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // proposal | order_customer | order_supplier | invoice_supplier
            $table->string('type', 30)->index();

            $table->unsignedBigInteger('number')->index();

            $table->foreignId('entity_id')
                ->constrained('entities')
                ->restrictOnDelete();

            $table->date('date');
            $table->date('due_date')->nullable(); // faturas

            // valores calculados
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // draft | closed | paid
            $table->string('status', 20)->default('draft')->index();

            $table->timestamps();

            $table->unique(['type', 'number']); // numeração independente por tipo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
