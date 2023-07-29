<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoorRes extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_coor'=>$this->id_coor,
            'call_sign'=>$this->call_sign,
            'series_id'=>$this->series_id,
            'coor_hdt' => [
                'id_coor_hdt'=>$this->id_coor_hdt,
                'heading_degree'=>$this->heading_degree,
                'checksum'=>$this->checksum
            ],
            'coor_gga' => [
                'id_coor_gga'=>$this->id_coor_gga,
                'utc_position'=>$this->utc_position,
                'latitude'=>$this->latitude,
                'direction_latitude'=>$this->direction_latitude,
                'longitude'=>$this->longitude,
                'direction_longitude'=>$this->direction_longitude,
                'gps_quality_indicator'=>$this->gps_quality_indicator,
                'number_sv'=>$this->number_sv,
                'hdop'=>$this->hdop,
                'orthometric_height'=>$this->orthometric_height,
                'unit_measure'=>$this->unit_measure,
                'geoid_seperation'=>$this->geoid_seperation,
                'geoid_measure'=>$this->geoid_measure,
            ],
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
