<?php

namespace App\Http\Resources\Alerts;


use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this != null) {
            return [
                'id' => $this->id,
                'email' => $this->email,
                'content' => $this->content,
                'created_at' => $this->created_at->diffForHumans(),
            ];
        } else return [];
    }
}
