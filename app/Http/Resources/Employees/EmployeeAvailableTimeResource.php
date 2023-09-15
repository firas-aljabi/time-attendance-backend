<?php

namespace App\Http\Resources\Employees;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAvailableTimeResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => intval($this->user_id),
            'hourly_annual' => intval($this->hourly_annual),
            'daily_annual' => intval($this->daily_annual),
        ];
    }
}
