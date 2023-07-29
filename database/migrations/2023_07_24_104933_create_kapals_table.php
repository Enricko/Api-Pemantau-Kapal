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
        Schema::create('kapals', function (Blueprint $table) {
            $table->string('call_sign')->primary();
            $table->string('flag');
            $table->string('class');
            $table->string('builder');
            $table->string('year_built');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kapals');
    }
};
