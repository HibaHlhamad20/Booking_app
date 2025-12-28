<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
 
    public $sender_id;
    public $receiver_id;
    public $message;

    public function __construct($sender_id, $receiver_id, Message $message)
    {
    $this->sender_id = $sender_id;
    $this->receiver_id = $receiver_id;
    $this->message = $message;
    }
    
    public function broadcastWith() {
        
        return [
            'id' => $this->message->id,
            'sender_id'=>$this->sender_id,
            'message'=>$this->message->message,
            'receiver_id'=>$this->receiver_id,
            'created_at' => $this->message->created_at->toDateTimeString()
        ];
    }
   
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
     public function broadcastOn() {
        return new PrivateChannel('chat.' .$this->receiver_id);
    }
}
