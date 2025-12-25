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
        Schema::create('news', function (Blueprint $table) {
            $table->integer('NewsId', true);
            $table->integer('UserId')->nullable()->index('userid');
            $table->string('Title', 500)->index('idx_news_title');
            $table->string('Slug')->unique('slug');
            $table->text('Content')->nullable();
            $table->string('ImageUrl', 500)->nullable();
            $table->enum('Status', ['Draft', 'Published', 'Hidden'])->default('Draft')->index('idx_news_status');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
