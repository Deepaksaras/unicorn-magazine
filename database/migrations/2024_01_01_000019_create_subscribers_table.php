<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->string('name', 191)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('source', 100)->nullable()->comment('website, social, referral');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_reason', 191)->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Unsubscribed, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('email');
            $table->index('source');
            $table->index('subscribed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
