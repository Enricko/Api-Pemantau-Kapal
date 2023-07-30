<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KapalRes extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "call_sign"=> $this->call_sign,
            "flag"=> $this->flag,
            "kelas"=> $this->class,
            "builder"=> $this->builder,
            "year_built"=> $this->year_built,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at
        ];
    }
}
