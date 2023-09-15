<?php

namespace App\Http\Resources\Requests;

use App\Statuses\RequestStatus;
use App\Statuses\RequestType;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray($request)
    {
        if ($this->type == RequestType::VACATION && $this->status == RequestStatus::REJECTED) {

            return [
                'id' => $this->id,
                "type" => $this->type,
                "status" => $this->status,
                "reason" => $this->reason,
                'reject_reason' => $this->reject_reason,
                "start_time" => $this->start_time,
                "end_time" => $this->end_time,
                "start_date" => $this->start_date,
                "end_date" => $this->end_date,
                'payment_type' => intval($this->payment_type),
                'vacation_type' => intval($this->vacation_type),
                'person' => $this->person !== null ? intval($this->person) : null,
                'dead_person' => $this->dead_person !== null ? intval($this->dead_person) : null,
                'degree_of_kinship' => $this->degree_of_kinship !== null ? intval($this->degree_of_kinship) : null,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
        } elseif ($this->type == RequestType::VACATION && ($this->status == RequestStatus::APPROVEED || $this->status == RequestStatus::PENDING)) {
            return [
                'id' => $this->id,
                "type" => $this->type,
                "status" => $this->status,
                "reason" => $this->reason,
                "start_time" => $this->start_time,
                "end_time" => $this->end_time,
                "start_date" => $this->start_date,
                "end_date" => $this->end_date,
                'payment_type' => intval($this->payment_type),
                'vacation_type' => intval($this->vacation_type),
                'person' => $this->person !== null ? intval($this->person) : null,
                'dead_person' => $this->dead_person !== null ? intval($this->dead_person) : null,
                'degree_of_kinship' => $this->degree_of_kinship !== null ? intval($this->degree_of_kinship) : null,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
        } elseif ($this->type == RequestType::JUSTIFY && $this->status == RequestStatus::REJECTED && $this->date == null) {
            return [
                "id" => $this->id,
                "type" => $this->type,
                "status" => $this->status,
                'justify_type' => intval($this->justify_type),
                "reason" => $this->reason,
                "reject_reason" => $this->reject_reason,
                "start_date" => $this->start_date,
                "end_date" => $this->end_date,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
        } elseif ($this->type == RequestType::JUSTIFY && ($this->status == RequestStatus::APPROVEED || $this->status == RequestStatus::PENDING) && $this->date == null) {
            return [
                "id" => $this->id,
                "type" => $this->type,
                "status" => $this->status,
                'justify_type' => intval($this->justify_type),
                "reason" => $this->reason,
                "start_date" => $this->start_date,
                "end_date" => $this->end_date,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
        } elseif ($this->type == RequestType::JUSTIFY && $this->status == RequestStatus::REJECTED && $this->date != null) {
            return [
                "id" => $this->id,
                "type" => $this->type,
                "status" => $this->status,
                'justify_type' => intval($this->justify_type),
                "reason" => $this->reason,
                "reject_reason" => $this->reject_reason,
                "date" => $this->date,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
        } elseif ($this->type == RequestType::JUSTIFY && ($this->status == RequestStatus::APPROVEED || $this->status == RequestStatus::PENDING) && $this->date != null) {
            return [
                "id" => $this->id,
                "type" => $this->type,
                "status" => $this->status,
                'justify_type' => intval($this->justify_type),
                "reason" => $this->reason,
                "date" => $this->date,
                "attachments" => $this->attachments ? asset($this->attachments) : null,
                'created_at' => $this->created_at->diffForHumans(),
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
        } elseif ($this->type == RequestType::RETIREMENT || $this->type == RequestType::RESIGNATION  && ($this->status == RequestStatus::APPROVEED || $this->status == RequestStatus::PENDING)) {
            return [
                'id' => $this->id,
                'type' => $this->type,
                'date' => $this->date,
                'status' => $this->status,
                "reason" => $this->reason,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
        } elseif ($this->type == RequestType::RETIREMENT || $this->type == RequestType::RESIGNATION   && $this->status == RequestStatus::REJECTED) {
            return [
                'id' => $this->id,
                'type' => $this->type,
                'date' => $this->date,
                'status' => $this->status,
                "reason" => $this->reason,
                'reject_reason' => $this->reject_reason,
                'created_at' => $this->created_at->diffForHumans(),
                "attachments" => $this->attachments ? asset($this->attachments) : null,
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
}
