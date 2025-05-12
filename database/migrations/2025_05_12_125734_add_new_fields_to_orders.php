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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('beantrager')->nullable();
            $table->string('sign')->nullable();
            $table->string('geburtsdatum')->nullable();

            // Fields for "versicherter" (insured person)
            $table->json('versicherter')->nullable(); // Stores versicherter as a JSON

            // Fields for "address" (the address of the insured person)
            $table->json('address')->nullable(); // Stores address as a JSON

            // Fields for "antragsteller" (applicant)
            $table->json('antragsteller')->nullable(); // Stores antragsteller as a JSON

            // Insurance details
            $table->string('insuranceType')->nullable();
            $table->string('insuranceProvider')->nullable(); // Store insurance provider as a JSON
            $table->string('insuranceNumber')->nullable();

            // Pflegegrad (Care level)
            $table->string('pflegegrad')->nullable();

            // Boolean fields for provider change and request for bed pads
            $table->boolean('changeProvider')->default(false);
            $table->boolean('requestBedPads')->default(false);

            // Delivery and application details
            $table->string('deliveryAddress')->nullable();
            $table->string('applicationReceipt')->nullable();
            $table->string('awarenessSource')->nullable();

            // Consultation check (integer field for tracking checkboxes or other values)
            $table->integer('consultation_check')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'beantrager',
                'sign',
                'geburtsdatum',
                'versicherter',
                'address',
                'antragsteller',
                'insuranceType',
                'insuranceProvider',
                'insuranceNumber',
                'pflegegrad',
                'changeProvider',
                'requestBedPads',
                'deliveryAddress',
                'applicationReceipt',
                'awarenessSource',
                'consultation_check',
            ]);
        });
    }
};
