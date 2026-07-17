<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Generated post-insert as CMD-<padded id>; nullable so the row can
            // be inserted first, then stamped (NULLs are exempt from UNIQUE).
            $table->string('number')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            // References the app users table without importing the Customers
            // module — Orders queries by user_id only.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('customer_name');
            $table->string('phone');
            // Address snapshots (Core\DataObjects\AddressData shape) frozen at
            // checkout time, so an order is unaffected by later address edits.
            $table->json('billing');
            $table->json('shipping');
            // All money as integer minor units (bani); exposed via MoneyCast.
            $table->unsignedBigInteger('items_subtotal');
            $table->string('shipping_code');
            $table->string('shipping_label');
            $table->unsignedBigInteger('shipping_total');
            $table->string('payment_code');
            $table->unsignedBigInteger('total');
            $table->string('awb')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
