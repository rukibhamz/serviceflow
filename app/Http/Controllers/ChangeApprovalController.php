<?php

namespace App\Http\Controllers;

use App\Mail\ChangeApprovalDecisionMail;
use App\Models\ChangeApprover;
use App\Services\Change\ChangeApprovalWorkflow;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ChangeApprovalController extends Controller
{
    public function __construct(
        private readonly ChangeApprovalWorkflow $workflow,
        private readonly SettingService $settings,
    ) {}

    /**
     * Show the approval decision page (from email link).
     */
    public function show(string $token): View|RedirectResponse
    {
        $approver = ChangeApprover::where('token', $token)
            ->with(['ticket.requester', 'user'])
            ->firstOrFail();

        if (! $approver->isPending()) {
            return view('change.approval-already-decided', compact('approver'));
        }

        return view('change.approval-form', compact('approver'));
    }

    /**
     * Quick approve via email link (no comment required).
     */
    public function quickApprove(string $token): RedirectResponse
    {
        return $this->decide($token, 'approved', null);
    }

    /**
     * Quick reject via email link (no comment required).
     */
    public function quickReject(string $token): RedirectResponse
    {
        return $this->decide($token, 'rejected', null);
    }

    /**
     * Submit decision from the approval form (with optional comment).
     */
    public function submit(Request $request, string $token): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'comment'  => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->decide($token, $validated['decision'], $validated['comment']);
    }

    /**
     * In-app approve (admin/agent clicking Approve in the ticket view).
     */
    public function inAppDecide(Request $request, int $approverId): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'comment'  => ['nullable', 'string', 'max:2000'],
        ]);

        $approver = ChangeApprover::with('ticket')->findOrFail($approverId);

        $this->recordDecision($approver, $validated['decision'], $validated['comment']);

        return back()->with('success', "Change request {$validated['decision']}.");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function decide(string $token, string $decision, ?string $comment): RedirectResponse
    {
        $approver = ChangeApprover::where('token', $token)
            ->with('ticket')
            ->firstOrFail();

        if (! $approver->isPending()) {
            return redirect('/')->with('status', 'You have already submitted your decision.');
        }

        $this->recordDecision($approver, $decision, $comment);

        return view('change.approval-thankyou', ['approver' => $approver, 'decision' => $decision])
            ->toResponse(request());
    }

    private function recordDecision(ChangeApprover $approver, string $decision, ?string $comment): void
    {
        $approver->update([
            'decision'   => $decision,
            'comment'    => $comment,
            'decided_at' => now(),
        ]);

        $ticket    = $approver->ticket;
        $brandName = $this->settings->get('brand_name', config('app.name', 'ServiceFlow'));

        // Notify the requester of the decision
        if ($ticket->requester?->email) {
            Mail::to($ticket->requester->email)->send(new ChangeApprovalDecisionMail(
                ticket: $ticket,
                decision: $decision,
                approverName: $approver->user->name,
                comment: $comment,
                brandName: $brandName,
            ));
        }

        // Check if all approvers have decided
        $allDecided  = $ticket->changeApprovers()->whereNull('decision')->doesntExist();
        $anyRejected = $ticket->changeApprovers()->where('decision', 'rejected')->exists();

        if ($allDecided) {
            if ($anyRejected) {
                $this->workflow->reject($ticket);
            } else {
                $this->workflow->approve($ticket);
            }
        }
    }
}
