<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type', 50)->default('pdf');
            $table->integer('file_size')->nullable()->comment('in bytes');
            $table->string('report_type', 50)->comment('annual, quarterly, research, market');
            $table->date('report_date')->nullable();
            $table->integer('download_count')->default(0);
            $table->boolean('is_premium')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('slug');
            $table->index('report_type');
            $table->index('is_premium');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
