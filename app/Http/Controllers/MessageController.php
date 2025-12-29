<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    
    public function storeMessage (Request $request) {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);
        
        $message = Message::create([
            'sender_id' => Auth::user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);
        event(new MessageSent(Auth::id(), $request->receiver_id, $message));

        return response()->json($message, 200);
    }

 public function getChatMessages($receiver_id)
{
    $messages = Message::where(function ($query) use ($receiver_id) {
        $query->where('sender_id', Auth::user()->id)
              ->where('receiver_id', $receiver_id);
    })
    ->orWhere(function ($query) use ($receiver_id) {
        $query->where('sender_id', $receiver_id)
              ->where('receiver_id', Auth::user()->id);
    })
    ->orderBy('created_at')
    ->get();

    return response()->json($messages);
}

}
