<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;

class MessageCreated  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

     /**
     * @var Chat
     */
    protected $message;
    /**
     * Create a new event instance
     * 
     * @param \App\Models\Chat $message
     * 
     * @return void
     */

    /**
     * Create a new event instance.
     */
    public function __construct(Chat $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('Message.User.'.$this->message->user_id . '.Client.' . $this->message->client_id),
        ];
    }
    public function broadcastWith(): array
    {
        return [
            'chat_id'=>$this->message->id,
            'message'=>$this->message
        ];

    }
    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.created';
    }

}
