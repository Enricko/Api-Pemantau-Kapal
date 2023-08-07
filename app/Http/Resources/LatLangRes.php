<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LatLangRes extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'call_sign' => $this->call_sign,
            'series_id' => $this->series_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
