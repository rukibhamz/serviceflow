<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p><strong>SLA Breach Alert</strong></p>
    <p>Ticket #{{ $timer->ticket->ulid }} - {{ $timer->ticket->subject }} has breached its SLA.</p>
    <p><strong>Breach Type:</strong> {{ $timer->type }}</p>
    <p><strong>Status:</strong> {{ $timer->ticket->status }}</p>
    <p><strong>Priority:</strong> {{ $timer->ticket->priority }}</p>
    <p><a href="{{ url('/tickets/' . $timer->ticket->ulid) }}">View Ticket</a></p>
</body>
</html>
