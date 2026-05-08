<!DOCTYPE html>
<html>
<head><meta charset="utf-8">
<style>
body{font-family:sans-serif;background:#f3f4f6;margin:0;padding:20px}
.card{background:#fff;border-radius:12px;padding:32px;max-width:520px;margin:0 auto;box-shadow:0 1px 3px rgba(0,0,0,.1)}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
.risk-high{background:#fee2e2;color:#dc2626}
.risk-medium{background:#fef3c7;color:#d97706}
.risk-low{background:#dcfce7;color:#16a34a}
.btn{display:inline-block;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;margin:4px}
.btn-approve{background:#16a34a;color:#fff}
.btn-reject{background:#dc2626;color:#fff}
.meta{background:#f9fafb;border-radius:8px;padding:16px;margin:16px 0;font-size:13px}
.meta dt{color:#6b7280;font-weight:500;margin-bottom:2px}
.meta dd{color:#111827;margin:0 0 10px}
.footer{color:#9ca3af;font-size:12px;margin-top:24px;border-top:1px solid #e5e7eb;padding-top:16px}
</style>
</head>
<body>
<div class="card">
    <h2 style="margin:0 0 4px;color:#111827">CAB Approval Required</h2>
    <p style="color:#6b7280;margin:0 0 20px;font-size:14px">{{ $brandName }} — Change Advisory Board</p>

    <p style="color:#374151;font-size:14px">
        You have been assigned as an approver for the following change request.
        Please review the details and approve or reject below.
    </p>

    <dl class="meta">
        <dt>Change Request</dt>
        <dd><strong>{{ $approver->ticket->subject }}</strong></dd>

        <dt>Requested By</dt>
        <dd>{{ $approver->ticket->requester?->name ?? 'Unknown' }}</dd>

        <dt>Change Type</dt>
        <dd>{{ ucfirst($approver->ticket->change_type ?? 'Normal') }}</dd>

        <dt>Risk Level</dt>
        <dd>
            <span class="badge risk-{{ $approver->ticket->risk_level ?? 'low' }}">
                {{ ucfirst($approver->ticket->risk_level ?? 'Low') }}
            </span>
        </dd>

        @if($approver->ticket->scheduled_at)
        <dt>Scheduled For</dt>
        <dd>{{ \Carbon\Carbon::parse($approver->ticket->scheduled_at)->format('d M Y H:i') }}</dd>
        @endif

        @if($approver->ticket->description)
        <dt>Description</dt>
        <dd style="white-space:pre-wrap">{{ \Illuminate\Support\Str::limit($approver->ticket->description, 300) }}</dd>
        @endif
    </dl>

    <div style="text-align:center;margin:24px 0">
        <a href="{{ url('/change-approval/' . $approver->token) }}" class="btn btn-approve">
            ✅ Review & Approve
        </a>
        <a href="{{ url('/change-approval/' . $approver->token) }}" class="btn btn-reject">
            ❌ Review & Reject
        </a>
    </div>

    <p style="font-size:12px;color:#9ca3af;text-align:center">
        Or <a href="{{ url('/change-approval/' . $approver->token) }}" style="color:#4f46e5">view full details</a>
        to add a comment with your decision.
    </p>

    <div class="footer">
        You are receiving this because you were assigned as a CAB approver.
        This request was submitted via {{ $brandName }}.
    </div>
</div>
</body>
</html>
