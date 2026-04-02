<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\CreateTicketAction;
use App\Models\CsatSurvey;
use App\Models\Ticket;
use App\Services\Knowledge\ArticleSearchService;
use App\Services\Portal\CsatService;
use App\Services\Portal\GuestTicketToken;
use App\Services\Portal\ServiceCatalogueService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct(
        private readonly CreateTicketAction $createTicketAction,
        private readonly ArticleSearchService $articleSearchService,
        private readonly ServiceCatalogueService $catalogueService,
        private readonly GuestTicketToken $guestToken,
        private readonly CsatService $csatService,
    ) {}

    /**
     * Portal dashboard — authenticated end_user sees their open tickets + KB search.
     */
    public function index()
    {
        $user = auth()->user();

        $openTickets = Ticket::where('requester_id', $user->id)
            ->whereNotIn('status', ['closed', 'resolved'])
            ->latest()
            ->take(5)
            ->get();

        return view('portal.index', compact('openTickets'));
    }

    /**
     * List the authenticated user's own tickets (paginated).
     */
    public function tickets()
    {
        $tickets = Ticket::where('requester_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('portal.tickets.index', compact('tickets'));
    }

    /**
     * Show a single ticket — user must be the requester or have a valid signed URL token.
     */
    public function showTicket(Request $request, Ticket $ticket)
    {
        $ticket->load('watchers');
        $user = auth()->user();

        // Authenticated requester check
        if ($user && $ticket->requester_id === $user->id) {
            return view('portal.tickets.show', compact('ticket'));
        }

        // Signed URL guest token check
        if ($this->guestToken->validate($request, $ticket)) {
            return view('portal.tickets.show', compact('ticket'));
        }

        abort(403, 'You do not have permission to view this ticket.');
    }

    /**
     * Show the ticket submission form.
     */
    public function createTicket()
    {
        // All users except the current user — for tagging colleagues/supervisors
        $colleagues = \App\Models\User::where('id', '!=', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('portal.tickets.create', compact('colleagues'));
    }

    /**
     * Validate and create a ticket, then redirect to the ticket view.
     */
    public function storeTicket(Request $request)
    {
        $data = $request->validate([
            'subject'      => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'priority'     => ['required', 'string', 'in:low,medium,high,critical'],
            'type'         => ['required', 'string', 'in:incident,service_request,problem,change'],
            'tagged_users' => ['nullable', 'array'],
            'tagged_users.*' => ['integer', 'exists:users,id'],
        ]);

        $data['source'] = 'web';

        $ticket = $this->createTicketAction->execute($data, auth()->user());

        // Save tagged colleagues as watchers (exclude the requester themselves)
        if (!empty($data['tagged_users'])) {
            $watcherIds = collect($data['tagged_users'])
                ->filter(fn ($id) => $id != auth()->id())
                ->unique()
                ->values();
            $ticket->watchers()->syncWithoutDetaching($watcherIds);
        }

        return redirect()->route('portal.tickets.show', $ticket->ulid)
            ->with('success', 'Your ticket has been submitted successfully.');
    }

    /**
     * Search knowledge base — returns JSON results for live search (AJAX).
     */
    public function searchKb(Request $request)
    {
        $query = $request->string('q')->trim()->toString();

        if ($request->wantsJson() || $request->ajax()) {
            if ($query === '') {
                return response()->json(['results' => []]);
            }

            $articles = $this->articleSearchService->search($query, 10);

            $results = $articles->map(fn ($article) => [
                'id'    => $article->id,
                'title' => $article->title,
                'slug'  => $article->slug,
                'url'   => route('agent.knowledge.show', $article->slug),
            ]);

            return response()->json(['results' => $results]);
        }

        // HTML page — search and render results
        $articles = $query !== '' ? $this->articleSearchService->search($query, 20) : collect();

        return view('portal.kb.search', compact('query', 'articles'));
    }

    // ── Service Catalogue ─────────────────────────────────────────────────────

    public function catalogue()
    {
        $items = $this->catalogueService->all();

        return view('portal.catalogue.index', compact('items'));
    }

    public function catalogueShow(string $id)
    {
        $item = $this->catalogueService->find($id);

        abort_if($item === null, 404);

        return view('portal.catalogue.show', compact('item'));
    }

    public function catalogueSubmit(Request $request, string $id)
    {
        $item = $this->catalogueService->find($id);

        abort_if($item === null, 404);

        $request->validate(['subject' => ['required', 'string', 'max:255']]);

        $ticketData = $this->catalogueService->mapToTicketData($id, $request->all());

        $ticket = $this->createTicketAction->execute($ticketData, auth()->user());

        return redirect()->route('portal.tickets.show', $ticket->ulid)
            ->with('success', 'Your request has been submitted.');
    }

    // ── CSAT ──────────────────────────────────────────────────────────────────

    /**
     * Quick 1-click rating via tokenised URL (no form needed).
     */
    public function csatRate(string $token, int $rating)
    {
        $this->csatService->recordRating($token, $rating);

        return view('portal.csat.thankyou');
    }

    /**
     * Show the detailed feedback form.
     */
    public function csatFeedback(string $token)
    {
        $survey = CsatSurvey::where('token', $token)->firstOrFail();

        return view('portal.csat.feedback', compact('survey'));
    }

    /**
     * Store the detailed feedback form submission.
     */
    public function csatStoreFeedback(Request $request, string $token)
    {
        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->csatService->recordRating($token, $data['rating'], $data['comment'] ?? null);

        return view('portal.csat.thankyou');
    }
}