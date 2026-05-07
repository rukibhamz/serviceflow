<!DOCTYPE html>
<html>
<head><meta charset="utf-8">
<style>
body{font-family:sans-serif;background:#f3f4f6;margin:0;padding:20px}
.card{background:#fff;border-radius:12px;padding:32px;max-width:520px;margin:0 auto;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.approved{background:#dcfce7;border-left:4px solid #16a34a;padding:12px 16px;border-radius:6px;color:#15803d;font-weight:600}
.rejected{background:#fee2e2;border-left:4px solid #dc2626;padding:12px 16px;border-radius:6px;color:#dc2626;font-weight:600}
.footer{color:#9ca3af;font-size:12px;margin-top:24px;border-top:1px solid #e5e7eb;padding-top:16px}
</style>
</head>
<body>
<div class="card">
    <h2 style="margin:0 0 4px;color:#111827">Change Request {{ $decision === 'approved' ? 'Approved' : 'Rejected' }}</h2>
    <p style="color:#6b7280;margin:0 0 20px;font-size:14px">{{ $brandName }}</p>

    <div class="{{ $decision }}">
        {{ $decision === 'approved' ? '✅ Approved' : '❌ Rejected' }} by {{ $approverName }}
    </div>

    <p style="color:#374151;font-size:14px;margin-top:16px">
        Your change request <strong>{{ $ticket->subject }}</strong> has been
        <strong>{{ $decision }}</strong>.
    </p>

    @if($comment)
    <div style="background:#f9fafb;border-radius:8px;padding:16px;margin:16px 0;font-size:13px">
        <p style="color:#6b7280;font-weight:500;margin:0 0 6px">Approver Comment</p>
        <p style="color:#111827;margin:0;white-space:pre-wrap">{{ $comment }}</p>
    </div>
    @endif

    <div class="footer">
        This is an automated notification from {{ $brandName }}.
    </div>
</div>
</body>
</html>
