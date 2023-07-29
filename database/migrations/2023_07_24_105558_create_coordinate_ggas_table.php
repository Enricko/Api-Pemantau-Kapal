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
        Schema::create('coordinate_ggas', function (Blueprint $table) {
            $table->id('id_coor_gga');
            $table->string('call_sign');
            $table->foreign('call_sign')->references('call_sign')->on('kapals')->onDelete('cascade');
            $table->string('message_id');
            $table->double('utc_position');
            $table->double('latitude');
            $table->string('direction_latitude');
            $table->double('longitude');
            $table->string('direction_longitude');
            $table->enum('gps_quality_indicator',[
                'Fix not valid','GPS fix',
                'Differential GPS fix (DGNSS), SBAS, OmniSTAR VBS, Beacon, RTX in GVBS mode',
                'Not applicable',
                'RTK Fixed, xFill',
                'RTK Float, OmniSTAR XP/HP, Location RTK, RTX',
                'INS Dead reckoning'
            ]);
            $table->integer('number_sv');
            $table->double('hdop');
            $table->double('orthometric_height');
            $table->string('unit_measure');
            $table->double('geoid_seperation');
            $table->string('geoid_measure');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordinate_ggas');
    }
};
