<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The `reference` correlation id (a UUID from the OrderDraft). Unlike `number`
     * — stamped after insert from the auto-increment id — it is known before the
     * row exists, so it is the key Checkout hands to Payments and the public URL
     * token for the confirmation page. Unique so firstOrCreate() is idempotent.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('reference')->nullable()->unique()->after('number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn('reference');
        });
    }
};
