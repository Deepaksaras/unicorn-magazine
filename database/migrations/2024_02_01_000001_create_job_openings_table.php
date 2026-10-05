<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_openings')) {
            return;
        }

        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->string('department', 100)->nullable();
            $table->string('employment_type', 100)->nullable()->comment('Full Time, Part Time, Contract, Internship');
            $table->string('location', 191)->nullable();
            $table->text('summary')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('requirements')->nullable();
            $table->string('apply_url', 191)->nullable();
            $table->integer('position')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();
            $table->index('status');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
