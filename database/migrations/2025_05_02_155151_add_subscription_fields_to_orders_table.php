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
            /* status life‑cycle: active | paused | cancelled */
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->nullable()
                    ->after('user_id');
            }

            /* date the last box was dispatched (nullable until the first shipment) */
            if (!Schema::hasColumn('orders', 'last_dispatch')) {
                $table->date('last_dispatch')->nullable()->after('status');
            }

            /* optional delivery address shortcut – useful for multi‑residence users */
            if (!Schema::hasColumn('orders', 'residence')) {
                $table->string('residence')->nullable()->after('last_dispatch');
            }

            /* typo guard – keep the original flag but add a canonical one */
            if (!Schema::hasColumn('orders', 'reuseable_bed_protection')) {
                $table->boolean('reuseable_bed_protection')
                    ->default(false)
                    ->after('dispatch_months');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_dispatch', 'residence', 'reuseable_bed_protection']);
        });
    }
};
