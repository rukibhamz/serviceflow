# Implementation Plan: QA Comprehensive Audit for ServiceFlow


## Overview

This plan converts the QA Comprehensive Audit design into a series of incremental Pest PHP test-writing tasks. Each task produces one or more test files that build on the shared infrastructure established in Task 1. Tasks 2–13 (unit tests) and Tasks 14–27 (feature tests) are independent of each other and can run in parallel after Task 1 completes. Task 28 (QA report setup) depends on all other tasks.

The implementation language is **PHP 8.2** with **Pest PHP v3** as the test runner, **SQLite in-memory** as the test database, and **Laravel fakes** for side-effect isolation.

---

## Task Dependency Graph

```json
{
  "waves": [
    { "wave": 1, "tasks": ["1"] },
    { "wave": 2, "tasks": ["2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27"] },
    { "wave": 3, "tasks": ["28"] }
  ],
  "dependencies": {
    "2":  ["1"],
    "3":  ["1"],
    "4":  ["1"],
    "5":  ["1"],
    "6":  ["1"],
    "7":  ["1"],
    "8":  ["1"],
    "9":  ["1"],
    "10": ["1"],
    "11": ["1"],
    "12": ["1"],
    "13": ["1"],
    "14": ["1"],
    "15": ["1"],
    "16": ["1"],
    "17": ["1"],
    "18": ["1"],
    "19": ["1"],
    "20": ["1"],
    "21": ["1"],
    "22": ["1"],
    "23": ["1"],
    "24": ["1"],
    "25": ["1"],
    "26": ["1"],
    "27": ["1"],
    "28": ["2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27"]
  }
}
```

---

## Tasks

- [x] 1. Set up test infrastructure
  - Create `tests/TestCase.php` extending Laravel's base `TestCase` with `actingAsRole(string $role, ?Tenant $tenant = null): static`, `setTenant(Tenant $tenant): static`, and `withoutTenantScope(): static` helpers as specified in the design.
  - Create `tests/Pest.php` with global `uses()` declarations: `uses(Tests\TestCase::class)->in('Feature')` and `uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature')`.
  - Update `phpunit.xml` to add `<testsuite name="Unit">` and `<testsuite name="Feature">` directory entries, coverage report targets (`clover.xml`, HTML), and JUnit XML logging output as specified in the design.
  - Create Eloquent model factories for all models referenced in the test suite: `TenantFactory` (states: `active`, `suspended`), `TicketFactory` (states: `open`, `closed`, `withSlaTimers`, `withAttachments`, `change`, `problem`), `SlaPolicyFactory` (states: `default`, `forPriority`), `SlaTimerFactory` (states: `active`, `breached`, `stopped`), `SlaPauseFactory` (states: `active`, `resumed`), `TeamFactory` (state: `withInboundEmail`), `AutomationFactory` (states: `active`, `inactive`), `AutomationLogFactory` (states: `success`, `error`), `KnowledgeArticleFactory` (states: `draft`, `published`, `archived`), `EmailThreadFactory`, `ChangeApproverFactory` (states: `approved`, `rejected`, `pending`), `AssetFactory` (states: `active`, `retired`, `withTicket`), `CsatSurveyFactory` (states: `pending`, `responded`), and `ServiceCatalogueItemFactory` (states: `active`, `inactive`). Extend the existing `UserFactory` with `admin`, `agent`, `manager`, `team_lead`, and `end_user` states.
  - Ensure all factories accept a `for($tenant)` relationship so tenant-scoped records can be created cleanly.
  - _Requirements: All (infrastructure prerequisite)_


- [ ] 2. Write Ticket Status Machine unit tests
  - Create `tests/Unit/Tickets/TicketStatusMachineTest.php` with example-based tests covering all valid and invalid transitions defined in `TicketStatusMachine::TRANSITIONS`.
  - Assert that `canTransition` returns `true` for every edge in the transition map and `false` for every pair not in the map.
  - Assert that `transition` throws `InvalidStatusTransitionException` for invalid targets and does not persist the ticket.
  - Assert that transitioning to `pending` creates an open `SlaPause` record with `paused_at` set for all active non-breached SLA timers.
  - Assert that transitioning away from `pending` sets `resumed_at` on the active `SlaPause` and extends `due_at` by the pause duration.
  - Assert that transitioning to `closed` sets `closed_at` and stops all open SLA timers.
  - Assert that `validStatuses` returns exactly `['open', 'in_progress', 'pending', 'resolved', 'closed']`.
  - Create `tests/Unit/Tickets/TicketUlidUniquenessTest.php` with a property test (100 iterations) asserting that a batch of N tickets all have distinct, non-null, non-empty `ulid` values.
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 1.8_

- [ ] 3. Write SLA Service unit tests
  - Create `tests/Unit/Sla/SlaServiceTest.php` covering `assignPolicy`, `recordFirstResponse`, and `checkBreach`.
  - Assert that `assignPolicy` creates `SlaTimer` records for both `response` and `resolution` types with `due_at` in the future.
  - Assert that a priority/type-specific policy is selected over the default when one exists.
  - Assert that the default policy (`is_default = true`) is used when no specific policy matches.
  - Assert that no `SlaTimer` records are created and no exception is thrown when no policy exists.
  - Assert that `recordFirstResponse` with a non-internal comment sets `stopped_at` on the `response` timer.
  - Assert that `recordFirstResponse` with an internal comment does not stop the `response` timer.
  - Create `tests/Unit/Sla/SlaBreachDetectionTest.php` asserting that `checkBreach` sets `breached = true` and dispatches `SlaBreached` for overdue timers, returns `false` without re-dispatching for already-breached timers, and returns `false` for stopped timers.
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9_

- [ ] 4. Write BusinessHoursCalculator property-based tests
  - Create `tests/Unit/Sla/BusinessHoursCalculatorTest.php` with property tests (100 iterations each) covering all five properties defined in the design.
  - Property 3 (Monotonicity): for any valid schedule and positive N, `addBusinessMinutes(start, N, schedule) >= start`.
  - Property 4 (Output Validity): result of `addBusinessMinutes` satisfies `isBusinessTime(result, schedule) == true`.
  - Property 5 (Round-Trip): `elapsedBusinessMinutes(start, addBusinessMinutes(start, N, schedule), schedule) == N`.
  - Property 6 (Non-Negativity): `elapsedBusinessMinutes(start, end, schedule) >= 0` for any start/end pair.
  - Assert that `isBusinessTime` returns `false` for timestamps on configured holidays.
  - Assert that `isBusinessTime` returns `false` for timestamps outside the configured day/time window.
  - Assert that `elapsedBusinessMinutes` returns `0` when `end <= start`.
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

- [ ] 5. Write Automation Engine unit tests
  - Create `tests/Unit/Automation/ConditionEvaluatorTest.php` covering all supported field operators and logical operators.
  - Assert that an empty conditions array returns `true`.
  - Assert that `operator = AND` returns `true` only when all conditions match.
  - Assert that `operator = OR` returns `true` when at least one condition matches.
  - Assert correct boolean results for all ten field operators: `equals`, `not_equals`, `contains`, `not_contains`, `starts_with`, `ends_with`, `greater_than`, `less_than`, `is_null`, `is_not_null`.
  - Assert that `custom_fields.` dot-notation fields are correctly extracted from the ticket's `custom_fields` JSON.
  - Add a property test (100 iterations, Property 7 — AND Metamorphic Negation): for any AND tree that evaluates to `true`, negating any single condition causes the result to become `false`.
  - Create `tests/Unit/Automation/ActionExecutorTest.php` covering `assign_ticket`, `change_status`, `add_comment`, and `trigger_webhook` actions.
  - Assert that `assign_ticket` updates `assignee_id`; `change_status` invokes `StatusMachine::transition`; `add_comment` creates a `TicketComment` with `user_id = null`; `trigger_webhook` sends an HTTP request via `Http::fake`.
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8, 6.9, 6.10, 6.11, 6.12, 6.13, 6.14_


- [ ] 6. Write Email Parser unit tests
  - Create `tests/Unit/Email/EmailParserTest.php` with example-based tests for all RFC 2822 parsing scenarios.
  - Assert that a well-formed email produces a `ParsedEmail` with non-empty `fromAddress`, `subject`, and non-null `body`.
  - Assert that `Message-ID` and `In-Reply-To` headers are parsed with angle brackets stripped.
  - Assert that `base64` and `quoted-printable` encoded bodies are correctly decoded.
  - Assert that `text/html` bodies have HTML tags stripped.
  - Assert that RFC 2047 encoded-word subjects decode to valid UTF-8 strings.
  - Create `tests/Unit/Email/EmailParserRobustnessTest.php` with property tests (100 iterations, Property 8 — Robustness): `EmailParser::parse` never throws for any input string including empty strings and malformed headers.
  - Add a property test (Property 9 — fromAddress Invariant): for any well-formed email with a valid `From:` header, `fromAddress` matches the RFC 5321 email address format.
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.8, 7.9_

- [ ] 7. Write Change Approval Workflow unit tests
  - Create `tests/Unit/Change/ChangeApprovalWorkflowTest.php` covering all workflow transitions and model methods.
  - Assert that `submitForApproval` on a `cab_approval_required = true` ticket sets status to `pending_approval`.
  - Assert that `submitForApproval` on a `cab_approval_required = false` ticket throws `LogicException`.
  - Assert that `approve` transitions `pending_approval` → `approved`.
  - Assert that `reject` transitions `pending_approval` → `rejected`.
  - Assert that `schedule` transitions `approved` → `scheduled` and sets `scheduled_at` to the provided datetime.
  - Assert that `transition` with a status not in `TRANSITIONS[current]` throws `InvalidStatusTransitionException`.
  - Assert that `Ticket::isFullyApproved` returns `true` only when all `ChangeApprover` records have `decision = approved`.
  - Assert that `Ticket::hasRejection` returns `true` when at least one `ChangeApprover` has `decision = rejected`.
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_

- [ ] 8. Write Policy unit tests
  - Create `tests/Unit/Policies/TicketPolicyTest.php` asserting correct boolean results for `viewAny`, `view`, `create`, `update`, and `delete` across all five roles.
  - Assert `viewAny` returns the correct boolean for `admin`, `manager`, `team_lead`, `agent`, and `end_user`.
  - Assert `view` returns `true` for the ticket's requester and assignee, and `false` for unrelated users of the same tenant.
  - Assert `create` returns `true` for authenticated users and `false` for guests.
  - Assert `update` returns `true` for agents and managers and `false` for end users who are not the requester.
  - Assert `delete` returns `true` only for admin-role users.
  - Create `tests/Unit/Policies/ArticlePolicyTest.php`, `tests/Unit/Policies/AssetPolicyTest.php`, and `tests/Unit/Policies/CommentPolicyTest.php` asserting equivalent role-based access controls for their respective resources.
  - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

- [ ] 9. Write Knowledge Base unit tests
  - Create `tests/Unit/Knowledge/ArticleServiceTest.php` covering the full article lifecycle.
  - Assert that `create` persists a `KnowledgeArticle` and an initial `KnowledgeArticleVersion`.
  - Assert that `create` with a duplicate title generates a unique slug (e.g. appended with `-2`).
  - Assert that `update` creates a new `KnowledgeArticleVersion` capturing the updated title and body.
  - Assert that `publish` sets status to `published` and `archive` sets status to `archived`.
  - Assert that `create` by a non-admin user without a valid team category throws `ValidationException`.
  - Assert that `incrementViewCount` increases `view_count` by exactly 1 without updating `updated_at`.
  - Assert that `vote` with `helpful = true` increases `helpful_votes` by 1 and leaves `unhelpful_votes` unchanged.
  - Create `tests/Unit/Knowledge/ArticleSlugTest.php` with a property test (100 iterations, Property 12 — Slug Invariant): `generateUniqueSlug` produces a slug matching `^[a-z0-9-]+$` that is unique within the tenant.
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.7, 12.8, 12.9_

- [ ] 10. Write cross-cutting invariant property tests
  - Create `tests/Unit/Invariants/TicketInvariantsTest.php` with property tests (100 iterations each) asserting: all ticket `status` values are in the valid vocabulary; all `priority` values are in the valid vocabulary; all `ulid` values are non-null, non-empty, and unique (Properties 11, 13).
  - Create `tests/Unit/Invariants/SlaTimerInvariantsTest.php` asserting: `stopped_at >= due_at` never holds when `breached = false`; `resumed_at >= paused_at` always holds when `resumed_at` is not null (Property 14).
  - Create `tests/Unit/Invariants/AutomationLogInvariantsTest.php` asserting: every `AutomationLog.result` is either `success` or begins with `error:` (Property 16).
  - Create `tests/Unit/Invariants/TenantInvariantsTest.php` asserting: every `Tenant.subdomain` matches `^[a-z0-9-]+$` and is unique (Property 17).
  - Create `tests/Unit/Invariants/ChangeApproverInvariantsTest.php` asserting: `isFullyApproved` is equivalent to all approvers having `decision = approved`; soft-deleting a ticket removes it from standard queries but it remains retrievable via `withTrashed()` (Property 15, Req 29.12).
  - _Requirements: 29.1, 29.2, 29.3, 29.4, 29.5, 29.7, 29.8, 29.9, 29.10, 29.11, 29.12_


- [ ] 11. Write Arch and Coding Standards unit tests
  - Create `tests/Unit/Arch/CodingStandardsTest.php` using Pest's `arch()` plugin to assert: all PHP files in `app/` declare `strict_types=1`; no `var_dump`, `dd`, `dump`, or `print_r` calls exist in `app/`; all Livewire component classes extend `Livewire\Component` and are in the `App\Livewire` namespace; all database queries in Livewire components use Eloquent query builder methods and not raw SQL strings.
  - Create `tests/Unit/Arch/StaticStructureTest.php` using `arch()` to assert: all service class constructors declare typed parameters and return types on all public methods; all Eloquent model `$fillable` arrays do not include `id`, `created_at`, or `updated_at`; all event classes implement a constructor accepting the relevant model and expose it as a public property; all listener classes implement `handle(Event $event): void` with the correct event type hint; no controller method accesses `$request->input()` or `$request->get()` without prior validation.
  - _Requirements: 21.2, 21.3, 21.4, 21.5, 21.6, 21.7, 22.2, 22.3, 22.4, 22.5, 22.6_

- [ ] 12. Write Reports unit tests
  - Create `tests/Unit/Reports/ReportBuilderTest.php` covering `ReportBuilder` query logic.
  - Assert that `ticketVolume` groups tickets by the specified dimension (status, priority, type) with correct counts.
  - Assert that a date range filter includes only tickets created within the range.
  - Assert that all report queries are scoped to the current tenant.
  - Assert that `ReportExporter::toCsv` produces a valid CSV string with a header row and one data row per result.
  - Assert that `ReportExporter::toPdf` produces a non-empty binary string beginning with `%PDF`.
  - _Requirements: 24.1, 24.2, 24.3, 24.4, 24.5_

- [ ] 13. Write Ticket HTTP feature tests
  - Create `tests/Feature/Http/TicketControllerTest.php` covering all `TicketController` endpoints.
  - Assert that a valid `store` request persists a `Ticket` with `status = open`, non-empty `ulid`, and correct `requester_id`.
  - Assert that creating a `problem` or `change` ticket without `team_id` returns a validation error for `team_id`.
  - Assert that creating a `change` ticket without `change_approver_ids` returns a validation error for `change_approver_ids`.
  - Assert that `store` with file attachments stores each file in the `attachments` media collection via Spatie MediaLibrary (use `Storage::fake`).
  - Assert that `updateStatus` with a valid transition returns HTTP 204 and updates the ticket status.
  - Assert that `updateStatus` with an invalid transition throws `InvalidStatusTransitionException` and leaves the ticket status unchanged.
  - Assert that `MergeTicketsAction` sets `merged_into_id` on the source ticket and sets its status to `closed`.
  - Assert that closing a ticket sets `closed_at` and stops all open SLA timers.
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.9_

- [-] 14. Write Auth and Security HTTP feature tests
  - Create `tests/Feature/Http/AuthControllerTest.php` asserting: valid credentials regenerate the session and redirect to the role-appropriate dashboard; invalid credentials redirect back with an `email` error; incorrect `current_password` returns a validation error without changing the password.
  - Create `tests/Feature/Http/InvitationControllerTest.php` asserting: a valid invitation token creates the user account and deletes the invitation record; an expired token rejects registration with an appropriate error.
  - Create `tests/Feature/Security/RoleAccessControlTest.php` asserting: unauthenticated requests to protected routes redirect to login or return HTTP 401; `end_user` role receives HTTP 403 on admin-only routes; `agent` role receives HTTP 403 on manager-only routes.
  - Assert that `EnsureAppIsInstalled` middleware redirects to the installer before installation and redirects to home after installation.
  - Assert that `SocialAuthController` creates or links a `SocialAccount` without creating duplicate user records.
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7, 10.8, 10.9, 10.10, 10.12_

- [-] 15. Write Tenancy feature tests
  - Create `tests/Feature/Tenancy/TenantScopeTest.php` asserting: the generated SQL includes `WHERE tenant_id = ?` for any tenant-scoped Eloquent query; all models using `TenantScope` set `tenant_id` automatically on creation; a property test (100 iterations, Property 10) asserting that a query under Tenant A's context returns zero records belonging to Tenant B.
  - Create `tests/Feature/Tenancy/TenantResolverTest.php` asserting: `resolve` with a valid subdomain returns the correct `Tenant` and sets it as current; `resolve` for an inactive tenant returns `null` and sets no current tenant.
  - Create `tests/Feature/Tenancy/TenantProvisionerTest.php` asserting: `provision` with a duplicate subdomain throws `InvalidArgumentException` and creates no tenant record; `provision` with valid data creates the tenant, admin user, and four default SLA policies in a single transaction; `suspend` sets `is_active = false` and prevents resolution by `TenantResolver`.
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9_


- [-] 16. Write Email-to-Ticket feature tests
  - Create `tests/Feature/Email/EmailToTicketTest.php` covering all `EmailToTicketAction` scenarios.
  - Assert that a `ParsedEmail` whose `inReplyTo` matches an existing `EmailThread.message_id` appends a `TicketComment` to the existing ticket and creates no new ticket.
  - Assert that a `ParsedEmail` with no matching `inReplyTo` creates a new `Ticket` with `source = email`.
  - Assert that when the sender email does not exist in `users`, a new `User` record is created with `role = end_user`.
  - Assert that when the `To` header matches a team's `inbound_email`, the created ticket has the correct `team_id`.
  - Assert that an email with an empty subject sets the ticket subject to `(No Subject)`.
  - Assert that an `EmailThread` record is created for every processed email regardless of whether a new ticket or comment was created.
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

- [-] 17. Write Event and Listener feature tests
  - Create `tests/Feature/Events/EventListenerTest.php` using both fake-based and real-dispatch approaches as specified in the design.
  - Assert that `TicketCreated` is listened to by `AssignSlaPolicy`, `RunAutomationEngine`, and `SendTicketCreatedMail` using `Event::assertListening`.
  - Assert that `AssignSlaPolicy` creates two `SlaTimer` records when `TicketCreated` is dispatched with a real listener run.
  - Assert that `RecordFirstResponse` stops the `response` SLA timer when `CommentAdded` is dispatched with a non-internal comment.
  - Assert that `RunAutomationEngine` is invoked when `TicketUpdated` is dispatched.
  - Assert that `SendSlaBreachedMail` queues a mail when `SlaBreached` is dispatched.
  - Assert that all event-listener bindings in `EventServiceProvider` are resolvable from the container.
  - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5, 16.6, 16.7, 16.8, 16.9_

- [-] 18. Write Livewire Ticket component feature tests
  - Create `tests/Feature/Livewire/Tickets/TicketListComponentTest.php` asserting: renders a paginated list scoped to the current tenant; updating `search` filters by subject/ulid and resets page to 1; updating `statusFilter` shows only matching tickets; `sortBy` with the current column toggles sort direction; `sortBy` with a new column resets `sortDir` to `asc`.
  - Create `tests/Feature/Livewire/Tickets/CreateTicketTest.php` asserting: valid submission creates a `Ticket` and emits a success message; missing required fields display validation errors.
  - Create `tests/Feature/Livewire/Tickets/TicketKanbanTest.php` asserting: tickets are grouped by status column with correct counts per column.
  - Create `tests/Feature/Livewire/Tickets/TicketTriageQueueTest.php` asserting: unassigned tickets are displayed in priority order.
  - Create `tests/Feature/Livewire/Tickets/TicketResourceTest.php` asserting: ticket details, comments, SLA timers, and attachments are all displayed for a specific ticket.
  - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6, 13.7, 13.8, 13.9, 13.10_

- [-] 19. Write Livewire Admin component feature tests
  - Create `tests/Feature/Livewire/Admin/AdminDashboardTest.php` asserting: aggregate metrics (open ticket count, SLA breach count, agent workload) are displayed.
  - Create `tests/Feature/Livewire/Admin/UserManagerTest.php` asserting: a paginated list of users with roles and active status is displayed.
  - Create `tests/Feature/Livewire/Admin/TeamManagerTest.php` asserting: creating a team persists a `Team` record with the correct `tenant_id`.
  - Create `tests/Feature/Livewire/Admin/SlaManagerTest.php` asserting: saving an SLA policy persists it and unsets the previous default policy for the same priority.
  - Create `tests/Feature/Livewire/Admin/BrandingSettingsTest.php` asserting: saving branding data stores `brand_name`, `theme_primary`, and `theme_accent` via `SettingService`.
  - Create `tests/Feature/Livewire/Admin/TenantManagerTest.php` asserting: provisioning a new tenant calls `TenantProvisioner::provision` and displays a success message.
  - Create `tests/Feature/Livewire/Admin/ChangeManagerTest.php` asserting: change tickets in `pending_approval` status are listed for review.
  - Create `tests/Feature/Livewire/Admin/ProblemManagerTest.php` asserting: problem tickets are listed with their linked incident counts.
  - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7, 14.8_

- [-] 20. Write Livewire Portal and auxiliary component feature tests
  - Create `tests/Feature/Livewire/Portal/UserDashboardTest.php` asserting: only tickets where the authenticated end user is the requester are displayed.
  - Create `tests/Feature/Livewire/Portal/ArticleEditorTest.php` asserting: saving calls `ArticleService::create` or `ArticleService::update` with the correct data.
  - Create `tests/Feature/Livewire/Portal/ArticleListTest.php` asserting: only published articles are visible to end users.
  - Create `tests/Feature/Livewire/Portal/AutomationBuilderTest.php` asserting: saving an automation rule persists it with the correct `trigger_event`, `conditions`, and `actions` JSON.
  - Create `tests/Feature/Livewire/Portal/ChangeCalendarTest.php` asserting: scheduled change tickets appear on their `scheduled_at` date.
  - Create `tests/Feature/Livewire/Portal/AssetListTest.php` asserting: assets are scoped to the current tenant.
  - Create `tests/Feature/Livewire/Portal/LoginComponentTest.php` asserting: valid credentials authenticate the user and redirect to the appropriate dashboard.
  - Create `tests/Feature/Livewire/Portal/DashboardWidgetsTest.php` asserting: widget data reflects the current tenant's tickets and metrics.
  - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6, 15.7, 15.8_


- [-] 21. Write Security feature tests
  - Create `tests/Feature/Security/CsrfProtectionTest.php` asserting: all POST/PUT/PATCH/DELETE routes are protected by `VerifyCsrfToken`; a POST without a valid CSRF token returns HTTP 419; successful login calls `session()->regenerate()`; session cookies are configured with `HttpOnly` and `SameSite` attributes; logout invalidates the session.
  - Create `tests/Feature/Security/IdorProtectionTest.php` asserting: a Tenant A user accessing a Tenant B ticket by direct URL receives HTTP 404; a Tenant A agent accessing a Tenant B knowledge article receives HTTP 404; a `ChangeApproval` token cannot be reused after a decision is recorded; a portal user requesting another user's CSAT survey token receives HTTP 403 or 404; all route model bindings for tenant-scoped models implicitly apply `TenantScope`.
  - Create `tests/Feature/Security/XssProtectionTest.php` asserting: ticket subjects containing `<script>` tags are HTML-encoded in rendered views; knowledge article bodies are sanitised before portal output; Livewire component properties bound to user input are not rendered unescaped; `EmailParser::parse` strips HTML tags from stored body fields.
  - Create `tests/Feature/Security/InputValidationTest.php` asserting: `subject` exceeding 255 characters returns a validation error; invalid `priority` values return a validation error; non-integer `response_minutes` returns a validation error; passwords shorter than 8 characters return a validation error; `theme_primary` with a non-hex value returns a validation error; subdomains with uppercase or special characters are slugified or rejected; file upload endpoints validate MIME type and file size.
  - _Requirements: 10.11, 17.1, 17.2, 17.3, 17.4, 17.5, 17.6, 17.7, 17.8, 17.9, 18.1, 18.2, 18.3, 18.4, 18.5, 19.1, 19.2, 19.3, 19.4, 19.5, 20.1, 20.2, 20.3, 20.4, 20.5_

- [-] 22. Write Asset Management feature tests
  - Create `tests/Feature/Assets/AssetManagementTest.php` covering all `AssetService` and `AssetImporter` scenarios.
  - Assert that `AssetService::create` persists an asset with the correct `tenant_id`.
  - Assert that `AssetImporter` processes a valid CSV and returns an `ImportResult` with the correct success count.
  - Assert that `AssetImporter` skips invalid rows and includes the error count in `ImportResult`.
  - Assert that linking an asset to a ticket creates the `asset_ticket` pivot record.
  - Assert that `AssetList` Livewire component renders assets filterable by name, type, and status.
  - _Requirements: 23.1, 23.2, 23.3, 23.4, 23.5_

- [-] 23. Write Reports feature tests
  - Create `tests/Feature/Reports/ReportBuilderFeatureTest.php` asserting: `ticketVolume` groups by status with correct counts; a date range filter includes only tickets within the range; all report queries are scoped to the current tenant.
  - Create `tests/Feature/Reports/ReportExporterTest.php` asserting: `toCsv` produces a valid CSV string with a header row and one data row per result; `toPdf` produces a non-empty binary string beginning with `%PDF`.
  - _Requirements: 24.1, 24.2, 24.3, 24.4, 24.5_

- [ ] 24. Write Portal and CSAT feature tests
  - Create `tests/Feature/Portal/CsatTest.php` covering portal and CSAT scenarios.
  - Assert that `PortalController` renders only the authenticated user's tickets.
  - Assert that a guest can access a ticket via a guest token URL without authentication.
  - Assert that `CsatService::send` creates a `CsatSurvey` record and queues a `CsatSurveyMail` to the requester.
  - Assert that submitting a CSAT survey response updates the `CsatSurvey` record with the rating and invalidates the token.
  - Assert that `ServiceCatalogueItemController` returns only active items scoped to the current tenant.
  - _Requirements: 25.1, 25.2, 25.3, 25.4, 25.5_

- [ ] 25. Write Installer and Console Commands feature tests
  - Create `tests/Feature/Installer/InstallerControllerTest.php` asserting: the installer index displays environment checker results before installation; valid database credentials run migrations and create `install.lock`; invalid credentials return an error without running migrations; accessing the installer route after installation redirects to home.
  - Create `tests/Feature/Installer/ConsoleCommandsTest.php` asserting: `PollInboxCommand` calls the email polling service and processes new messages; `IngestEmailCommand` with piped email input calls `EmailParser::parse` and `EmailToTicketAction::execute`.
  - _Requirements: 10.7, 10.8, 27.1, 27.2, 27.3, 27.4, 27.5, 27.6_

- [ ] 26. Write AI Assist feature tests
  - Create `tests/Feature/Ai/AiAssistServiceTest.php` using `Http::fake` to simulate provider responses.
  - Assert that `AiAssistService` returns a non-empty suggestion string for a valid ticket subject and description.
  - Assert that when the AI provider returns an error response, `AiAssistService` catches the exception and returns a fallback empty string.
  - Assert that the request payload does not include sensitive fields such as passwords or API keys from the application configuration.
  - _Requirements: 28.1, 28.2, 28.3_

- [ ] 27. Write Performance feature tests
  - Create `tests/Feature/Performance/TicketQueryPerformanceTest.php` asserting: the ticket list endpoint with 1,000 tickets responds in under 500ms; the `TicketListComponent` query uses eager loading for `requester`, `assignee`, and `team` relationships to prevent N+1 queries; all ticket list queries filter on indexed columns.
  - Create `tests/Feature/Performance/BusinessHoursPerformanceTest.php` asserting: `addBusinessMinutes` with 10,000 minutes against a standard 5-day schedule completes in under 100ms.
  - Create `tests/Feature/Performance/AutomationPerformanceTest.php` asserting: `AutomationEngine::process` with 50 active automations completes in under 1,000ms.
  - Create `tests/Feature/Performance/ReportPerformanceTest.php` asserting: `ReportBuilder` over 10,000 tickets executes without a memory limit error and returns within 2,000ms.
  - _Requirements: 26.1, 26.2, 26.3, 26.4, 26.5, 26.6_

- [ ] 28. Configure QA report output
  - Verify that `phpunit.xml` is configured to output JUnit XML to `storage/test-reports/junit.xml` and HTML coverage to `storage/test-reports/coverage/`.
  - Add a `composer.json` script `"test:coverage"` that runs Pest with `--coverage --min=70` to enforce the 70% overall line coverage threshold.
  - Add a `composer.json` script `"test:services"` that runs Pest with `--coverage --min=80` scoped to `app/Services/` to enforce the 80% services coverage threshold.
  - Add a `composer.json` script `"test:actions"` that runs Pest with `--coverage --min=90` scoped to `app/Actions/` to enforce the 90% actions coverage threshold.
  - Add a `composer.json` script `"qa"` that sequentially runs `pint --test`, `phpstan analyse --level=6`, and `pest --coverage` to produce the full QA report in a single command.
  - _Requirements: 21.1, 22.1, 30.1, 30.2, 30.3, 30.4, 30.5, 30.6, 30.7, 30.8_

---

## Notes

- All tasks depend on Task 1 (infrastructure). Tasks 2–27 are independent of each other and can run in parallel once Task 1 is complete.
- Task 28 depends on all other tasks being complete.
- Property-based tests use Pest's `->repeat(100)` helper with PHP's `random_int`/`random_bytes` — no external PBT library is required.
- MySQL-specific tests should be tagged `@group mysql` and skipped in the default SQLite run.
- Use `Event::fake()`, `Mail::fake()`, `Http::fake()`, and `Storage::fake()` in all feature tests to prevent real side effects.
- The `actingAsRole()` helper from `TestCase` should be used in all feature tests that require an authenticated user.
