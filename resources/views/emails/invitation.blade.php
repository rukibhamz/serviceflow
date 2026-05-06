<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:sans-serif;background:#f3f4f6;margin:0;padding:20px}
.card{background:#fff;border-radius:12px;padding:32px;max-width:480px;margin:0 auto;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.btn{display:inline-block;background:#1a4fa0;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;margin:20px 0}
.footer{color:#9ca3af;font-size:12px;margin-top:24px}</style>
</head>
<body>
<div class="card">
    <h2 style="margin:0 0 8px;color:#111827">You've been invited to {{ $brandName }}</h2>
    <p style="color:#6b7280;margin:0 0 16px">
        You've been invited to join <strong>{{ $brandName }}</strong> as a
        <strong>{{ ucfirst($invitation->role) }}</strong>.
    </p>
    <p style="color:#6b7280">Click the button below to accept your invitation and set up your account.</p>
    <a href="{{ url('/invite/' . $invitation->token) }}" class="btn">Accept Invitation</a>
    <p style="color:#9ca3af;font-size:13px">This invitation expires on {{ $invitation->expires_at->format('d M Y') }}.</p>
    <div class="footer">If you weren't expecting this invitation, you can safely ignore this email.</div>
</div>
</body>
</html>
