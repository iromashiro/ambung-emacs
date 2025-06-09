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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['new', 'accepted', 'dispatched', 'delivered', 'canceled'])->default('new');
            $table->text('shipping_address');
            $table->string('phone');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['buyer_id', 'status']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
