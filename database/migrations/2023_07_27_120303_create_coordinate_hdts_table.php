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
        Schema::create('coordinate_hdts', function (Blueprint $table) {
            $table->id('id_coor_hdt');
            $table->string('call_sign');
            $table->foreign('call_sign')->references('call_sign')->on('kapals')->onDelete('cascade');
            $table->double('heading_degree');
            $table->string('checksum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordinate_hdts');
    }
};
