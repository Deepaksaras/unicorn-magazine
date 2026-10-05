<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertising_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('email', 191);
            $table->string('phone', 20)->nullable();
            $table->string('company', 191)->nullable();
            $table->string('website', 191)->nullable();
            $table->text('message')->nullable();
            $table->string('budget', 50)->nullable();
            $table->string('duration', 50)->nullable();
            $table->string('placement_interest', 191)->nullable();
            $table->string('enquiry_status', 50)->default('new')->comment('new, contacted, converted, rejected');
            $table->unsignedTinyInteger('is_read')->default(0)->comment('0=Unread, 1=Read');
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('email');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertising_enquiries');
    }
};
