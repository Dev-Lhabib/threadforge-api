<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blueprint_id')->constrained()->cascadeOnDelete();
            $table->string('hook_propose', 280)->nullable();
            $table->json('body_points')->nullable();
            $table->unsignedTinyInteger('technical_readability_score')->nullable();
            $table->json('suggested_hashtags')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
