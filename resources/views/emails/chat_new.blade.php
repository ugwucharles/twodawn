New message from {{ $conversation->name ?? 'Guest' }} ({{ $conversation->email ?? 'no email' }})

Conversation: {{ route('admin.chat.show', $conversation) }}

"{{ $message->body }}"

--
2DAWN Chat