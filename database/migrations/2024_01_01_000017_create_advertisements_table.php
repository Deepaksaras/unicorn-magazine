<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('advertisement_placements')->onDelete('cascade');
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('url', 191)->nullable();
            $table->string('alt_text', 191)->nullable();
            $table->string('target', 50)->default('_blank');
            $table->integer('impression_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('placement_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
