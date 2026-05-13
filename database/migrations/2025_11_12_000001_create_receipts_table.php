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
        Schema::create('receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('store_name')->nullable();
            $table->string('receipt_no')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->integer('total_items')->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_payment', 12, 2)->default(0);
            $table->decimal('dpp', 12, 2)->default(0);
            $table->decimal('ppn', 12, 2)->default(0);
            $table->json('response')->nullable();
            $table->string('status')->default('created');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
