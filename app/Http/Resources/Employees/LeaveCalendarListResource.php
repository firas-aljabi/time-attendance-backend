<?php

namespace App\Http\Resources\Employees;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveCalendarListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->date != null) {

            return [
                'id' => $this->id,
                "date" => $this->date,
                'user' => $this->whenLoaded('user', function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'image' => $this->user->image ? asset($this->user->image) : null,
                        'position' => $this->user->position,
                    ];
                }),

            ];
        } elseif ($this->date == null) {
            return [
                'id' => $this->id,
                "start_date" => $this->start_date,
                "end_date" => $this->end_date,
                'user' => $this->whenLoaded('user', function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'image' => $this->user->image ? asset($this->user->image) : null,
                        'position' => $this->user->position,
                    ];
                }),

            ];
        }
    }

    // public function toArray($request)
    // {
    //     if ($this->date != null) {
    //         return [
    //             'id' => $this->id,
    //             "date" => $this->date,
    //             'user' => $this->whenLoaded('user', function () {
    //                 return [
    //                     'id' => $this->user->id,
    //                     'name' => $this->user->name,
    //                     'email' => $this->user->email,
    //                     'image' => $this->user->image ? asset($this->user->image) : null,
    //                     'position' => $this->user->position,
    //                 ];
    //             }),
    //         ];
    //     } elseif ($this->start_date != null && $this->end_date != null) {
    //         return [
    //             'id' => $this->id,
    //             "start_date" => $this->start_date,
    //             "end_date" => $this->end_date,
    //             'user' => $this->whenLoaded('user', function () {
    //                 return [
    //                     'id' => $this->user->id,
    //                     'name' => $this->user->name,
    //                     'email' => $this->user->email,
    //                     'image' => $this->user->image ? asset($this->user->image) : null,
    //                     'position' => $this->user->position,
    //                 ];
    //             }),
    //         ];
    //     } else {
    //         return [];
    //     }
    // }
}
