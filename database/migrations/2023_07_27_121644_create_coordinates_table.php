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
        Schema::create('coordinates', function (Blueprint $table) {
            $table->id("id_coor");
            $table->string('call_sign');
            $table->foreign('call_sign')->references('call_sign')->on('kapals')->onDelete('cascade');
            $table->bigInteger('series_id');
            $table->unsignedBigInteger('id_coor_gga')->nullable();
            $table->foreign('id_coor_gga')->references('id_coor_gga')->on('coordinate_ggas')->onDelete('cascade');
            $table->unsignedBigInteger('id_coor_hdt')->nullable();
            $table->foreign('id_coor_hdt')->references('id_coor_hdt')->on('coordinate_hdts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordinates');
    }
};
