<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'name' => $this->name,
            'prev_day_close_price' => $this->prev_day_close_price,
            'current_price' => $this->current_price,
            'change' => $this->change,
            'percent_change' => $this->percent_change_24h ?? $this->percent_change,
            'last_updated' => $this->last_updated,
        ];
    }
}
