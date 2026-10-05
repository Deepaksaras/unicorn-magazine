<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('profile_type', 50)->comment('entrepreneur, billionaire, executive, other');
            $table->string('company_name', 191)->nullable();
            $table->string('designation', 191)->nullable();
            $table->string('industry', 191)->nullable();
            $table->decimal('net_worth', 15, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->text('biography')->nullable();
            $table->string('website', 191)->nullable();
            $table->string('linkedin_url', 191)->nullable();
            $table->string('twitter_url', 191)->nullable();
            $table->string('profile_image')->nullable();
            $table->json('social_links')->nullable();
            $table->json('achievements')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('profile_type');
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
