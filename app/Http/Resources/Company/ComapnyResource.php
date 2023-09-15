<?php

namespace App\Http\Resources\Company;

use App\Http\Resources\Admin\EmployeeResource;
use App\Http\Resources\Location\LocationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ComapnyResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'commercial_record' => $this->commercial_record ? asset($this->commercial_record) : null,
            'start_commercial_record' => $this->start_commercial_record,
            'end_commercial_record' => $this->end_commercial_record,
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'admin' => $this->whenLoaded('admin', function () {
                return [
                    'id' => $this->admin->id,
                    'name' => $this->admin->name,
                ];
            }),
        ];
    }
}