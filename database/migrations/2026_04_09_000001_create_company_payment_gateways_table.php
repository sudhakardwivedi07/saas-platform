<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('gateway')->default('stripe'); // stripe, paypal, etc.
            $table->string('mode')->default('test'); // test, live
            $table->string('status')->default('inactive'); // active, inactive
            $table->string('live_key')->nullable();
            $table->text('live_secret')->nullable();
            $table->text('live_webhook_secret')->nullable();
            $table->string('test_key')->nullable();
            $table->text('test_secret')->nullable();
            $table->text('test_webhook_secret')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payment_gateways');
    }
};
