<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p>A new comment has been added to ticket #{{ $ticket->ulid }} - {{ $ticket->subject }}</p>
    <hr>
    <p>{{ $comment->body }}</p>
    <hr>
    <p><a href="{{ url('/tickets/' . $ticket->ulid) }}">View Ticket</a></p>
</body>
</html>
