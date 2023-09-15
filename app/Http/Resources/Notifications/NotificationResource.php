<?php

namespace App\Http\Resources\Notifications;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "notifier_id" => $this->notifier_id,
            "post_id" => $this->post_id,
            "user_id" => $this->user_id,
            "message" => $this->message,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
