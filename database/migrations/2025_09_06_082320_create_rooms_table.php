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
        Schema::create('rooms', function (Blueprint $table) {
            $table->integer('RoomId', true);
            $table->integer('CinemaId')->index('idx_rooms_cinema');
            $table->string('Name', 200);
            $table->integer('SeatCount')->default(0);
            $table->enum('RoomType', ['2D', '3D', '4DX', 'IMAX'])->nullable();
            $table->enum('Status', ['Active', 'Inactive', 'Maintenance'])->default('Active');
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
        Schema::dropIfExists('rooms');
    }
};
