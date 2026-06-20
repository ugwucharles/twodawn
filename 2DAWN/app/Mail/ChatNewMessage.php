<?php

namespace App\Mail;

use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChatNewMessage extends Mailable
{
    use Queueable, SerializesModels;

    public Conversation $conversation;
    public ChatMessage $message;

    public function __construct(Conversation $conversation, ChatMessage $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject('New chat message')
            ->view('emails.chat_new');
    }
}
