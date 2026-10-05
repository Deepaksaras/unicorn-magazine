<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->string('section_name', 191);
            $table->text('content')->nullable();
            $table->string('layout', 100)->default('default');
            $table->json('settings')->nullable();
            $table->integer('position')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('page_id');
            $table->index('section_name');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
