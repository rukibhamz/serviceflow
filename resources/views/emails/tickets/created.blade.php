<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p>A new ticket has been created: #{{ $ticket->ulid }} - {{ $ticket->subject }}</p>
    <p><strong>Status:</strong> {{ $ticket->status }}</p>
    <p><strong>Priority:</strong> {{ $ticket->priority }}</p>
    @if($ticket->description)
        <p><strong>Description:</strong></p>
        <p>{{ $ticket->description }}</p>
    @endif
</body>
</html>
