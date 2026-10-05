<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100)->default('general');
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string')->comment('string, integer, boolean, json, text');
            $table->string('label', 191)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('group');
            $table->index('key');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
