<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationCheckNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $notifier;
    protected $user;


    public function __construct(User $notifier)
    {
        $this->notifier = $notifier;

        $notification = new Notification();
        $notification->user_id = $notifier->id;
        $notification->company_id = $notifier->company_id;
        $notification->notifier_id = $notifier->id;
        $notification->message =  "You are checked out of the company, you have exceeded the company domain";
        $notification->save();
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notifications.' . $this->notifier->id);
    }
    public function broadcastWith()
    {
        $notify = Notification::where('notifier_id', $this->notifier->id)->first();
        $unread_notifiy = Notification::where('read_at', null)->count();
        return [
            "data" => $notify,
            "unread_notifiy" => $unread_notifiy
        ];
    }
}
