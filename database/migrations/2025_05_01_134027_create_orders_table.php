<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            /*  products => { koala_product_id : { amount : int } }  */
            $table->json('products');
            $table->json('dispatch_months')->nullable();
            $table->boolean('reusable_bed_protection')->default(false);

            $table->timestamps();
            $table->unique('user_id');   // one active order per user
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
