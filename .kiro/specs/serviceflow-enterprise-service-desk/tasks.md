# Implementation Plan: ServiceFlow Enterprise Service Desk

## Overview

Incremental implementation of ServiceFlow across three phases. Each phase builds on the previous, starting with the Laravel foundation and installer, then Phase 1 (MVP) core modules, Phase 2 (ITSM Maturity), and Phase 3 (Intelligence & Scale). Tasks are ordered so each step integrates into the running application before moving forward.

## Tasks

---

### Phase 0 — Foundation & Installer

- [x] 1. Scaffold Laravel 11 project and install core dependencies
  - Initialise Laravel 11 project targeting PHP 8.2+
  - Add Composer packages: `livewire/livewire ^3.0`, `spatie/laravel-permission ^6.0`, `spatie/laravel-activitylog ^4.0`, `spatie/laravel-media-library ^11.0`, `spatie/laravel-backup ^8.0`, `teamtnt/tntsearch ^3.0`, `barryvdh/laravel-dompdf ^2.0`, `maatwebsite/excel ^3.1`, `laravel/socialite ^5.0`, `laravel/reverb ^1.0`
  - Configure Tailwind CSS, Alpine.js, and Vite asset pipeline
  - Set queue driver to `database`, cache driver to `file` in default `.env.example`
  - _Requirements: Platform foundation_

- [x] 2. Define core database schema migrations
  - [x] 2.1 Create migrations for `users`, `teams`, `team_user` tables
    - `users`: id, name, email, password, role, avatar, timezone, locale, timestamps
    - `teams`: id, name, description, timestamps
    - `team_user`: pivot with team_id, user_id
    - _Requirements: 1.1, 1.2_
  - [x] 2.2 Create migrations for `tickets`, `ticket_comments`, `ticket_tags`, `ticket_attachments`
    - `tickets`: id, subject, description, status, priority, type, requester_id, assignee_id, team_id, source, merged_into_id, closed_at, timestamps
    - `ticket_comments`: id, ticket_id, user_id, body, is_internal, timestamps
    - _Requirements: 2.1, 2.2_
  - [x] 2.3 Create migrations for `sla_policies`, `sla_timers`
    - `sla_policies`: id, name, priority, response_hours, resolve_hours, business_hours, timestamps
    - `sla_timers`: id, ticket_id, sla_policy_id, first_response_at, resolved_at, breached, timestamps
    - _Requirements: 3.1, 3.2_
  - [x] 2.4 Create migrations for `knowledge_articles`, `article_categories`
    - `knowledge_articles`: id, title, body, category_id, author_id, status, slug, timestamps
    - `article_categories`: id, name, parent_id, timestamps
    - _Requirements: 5.1_
  - [x] 2.5 Create migrations for `email_threads`, `csat_surveys`
    - `email_threads`: id, ticket_id, message_id, in_reply_to, from_address, raw_headers, timestamps
    - `csat_surveys`: id, ticket_id, requester_id, token, rating, comment, submitted_at, timestamps
    - _Requirements: 4.1, 6.3_
  - [x] 2.6 Create migrations for `assets`, `automations`, `automation_logs`
    - `assets`: id, name, type, serial_number, assigned_to, status, purchased_at, timestamps
    - `automations`: id, name, trigger, conditions, actions, active, timestamps
    - _Requirements: 8.1, 7.1_

- [x] 3. Implement roles, permissions, and base models
  - Publish and run `spatie/laravel-permission` migrations
  - Seed roles: `admin`, `manager`, `agent`, `end_user`
  - Assign permissions per design: admin (`*`), manager (`tickets.*`, `reports.*`, `settings.view`), agent (`tickets.view`, `tickets.update`, `tickets.comment`), end_user (`portal.tickets.*`)
  - Create Eloquent models with relationships: `User`, `Team`, `Ticket`, `TicketComment`, `SlaPolicy`, `SlaTimer`, `KnowledgeArticle`, `ArticleCategory`, `EmailThread`, `CsatSurvey`, `Asset`, `Automation`
  - Register `spatie/laravel-activitylog` on `Ticket` and `Asset` models
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 4. Build the web installer
  - [x] 4.1 Implement `EnvironmentChecker` — verify PHP version, required extensions, directory writability
    - _Requirements: Installer_
  - [x] 4.2 Implement `DatabaseInstaller` — run migrations and seeders programmatically via `Artisan::call`
    - _Requirements: Installer_
  - [x] 4.3 Implement `CpanelPipeInstaller` — generate `.forward` file content and pipe script path instructions
    - _Requirements: 4.2, Installer_
  - [x] 4.4 Implement `InstallerController` with multi-step Blade wizard (environment check → DB config → admin account → finish)
    - Lock installer behind `app.installed` config flag; redirect away once installed
    - _Requirements: Installer_

- [x] 5. Checkpoint — Installer and schema baseline
  - Ensure all migrations run cleanly, roles/permissions seed correctly, and installer wizard completes without errors. Ask the user if questions arise.

---

### Phase 1 — MVP Modules

#### Ticket Management

- [ ] 6. Implement `TicketStatusMachine` and `TicketService`
  - [x] 6.1 Implement `TicketStatusMachine` — enforce valid transitions: `open → in_progress → pending → resolved → closed`, re-open path `resolved/closed → open`
    - Throw `InvalidStatusTransitionException` for illegal moves
    - _Requirements: 2.3_
  - [x] 6.2 Write property test for status transition validity
    - **Property 2: Status Transition Validity**
    - **Validates: Requirements 2.3**
    - Generate random sequences of status strings; assert only transitions in the allowed graph are accepted and all others throw
  - [x] 6.3 Implement `CreateTicketAction` — validate input, persist ticket, fire `TicketCreated` event
    - Enforce: subject non-empty, valid priority, valid type, requester exists
    - _Requirements: 2.1_
  - [-] 6.4 Write property test for ticket creation invariants
    - **Property 1: Ticket Creation Invariants**
    - **Validates: Requirements 2.1**
    - Generate arbitrary valid ticket payloads; assert ticket persists with correct fields and `TicketCreated` event is dispatched
  - [x] 6.5 Implement `MergeTicketsAction` — move comments and attachments from source to target, set `merged_into_id`, close source ticket
    - _Requirements: 2.6_
  - [~] 6.6 Write property test for ticket merge completeness
    - **Property 12: Ticket Merge Completeness**
    - **Validates: Requirements 2.6**
    - Generate two tickets with random comment counts; after merge assert all comments on target, source status is `closed`, `merged_into_id` set
  - [x] 6.7 Implement `TicketObserver` — hook `created`, `updated`, `deleted` to activity log and event dispatch
    - _Requirements: 2.1, 2.3_

- [x] 7. Build Livewire ticket UI components
  - [x] 7.1 Implement `TicketListComponent` — paginated, filterable (status, priority, assignee, team, date range), sortable ticket list for agents
    - _Requirements: 2.2, 2.4_
  - [x] 7.2 Implement `TicketResource` Livewire component — ticket detail view with inline comment editor, status/priority/assignee controls, activity timeline, merge action
    - _Requirements: 2.2, 2.3, 2.5, 2.6_
  - [x] 7.3 Implement queue/triage view — unassigned ticket queue with bulk-assign and bulk-status-update actions
    - _Requirements: 2.4_

- [x] 8. Checkpoint — Ticket management end-to-end
  - Ensure ticket CRUD, status transitions, merge, and list/detail Livewire components all function. Ask the user if questions arise.

#### SLA Engine

- [ ] 9. Implement `SlaService` and `BusinessHoursCalculator`
  - [x] 9.1 Implement `BusinessHoursCalculator` — calculate elapsed business minutes between two timestamps given a schedule (weekday hours, excluded holidays)
    - _Requirements: 3.3_
  - [~] 9.2 Write property test for business hours exclusion
    - **Property 6: Business Hours Exclusion from SLA**
    - **Validates: Requirements 3.3**
    - Generate random timestamp pairs spanning weekends/holidays; assert calculated business minutes never include out-of-hours time
  - [x] 9.3 Implement `SlaService::assignPolicy` — on ticket creation, match ticket priority to `SlaPolicy`, create `SlaTimer` record
    - _Requirements: 3.1_
  - [~] 9.4 Write property test for SLA assignment on ticket creation
    - **Property 3: SLA Assignment on Ticket Creation**
    - **Validates: Requirements 3.1**
    - For any valid ticket creation, assert exactly one `SlaTimer` is created linked to the correct policy
  - [x] 9.5 Implement `SlaService::recordFirstResponse` — stamp `first_response_at` on `SlaTimer` when first agent comment is posted
    - _Requirements: 3.2_
  - [~] 9.6 Write property test for first response stops SLA timer
    - **Property 4: First Response Stops SLA Timer**
    - **Validates: Requirements 3.2**
    - Post first agent comment on any open ticket; assert `first_response_at` is set and subsequent comments do not overwrite it
  - [x] 9.7 Implement `SlaService::checkBreach` — compare elapsed business hours against policy thresholds, set `breached = true` when exceeded
    - _Requirements: 3.4_
  - [~] 9.8 Write property test for SLA breach detection
    - **Property 5: SLA Breach Detection**
    - **Validates: Requirements 3.4**
    - Generate timers where elapsed time exceeds threshold; assert `breached` flag is set; generate timers within threshold and assert not breached
  - [x] 9.9 Implement `SlaEscalationJob` — scheduled job that calls `SlaService::checkBreach` for all open timers and dispatches `SlaBreached` event
    - Register in `Console/Kernel` schedule (every minute)
    - _Requirements: 3.4, 3.5_

- [x] 10. Checkpoint — SLA engine
  - Ensure SLA policies assign on ticket creation, first-response stamps correctly, breach detection fires. Ask the user if questions arise.

#### Email Integration

- [ ] 11. Implement inbound email pipeline
  - [x] 11.1 Implement `EmailParser` — parse raw RFC 2822 email into structured DTO (from, subject, body, attachments, message-id, in-reply-to)
    - _Requirements: 4.1_
  - [x] 11.2 Implement `EmailToTicketAction` — if `in-reply-to` matches existing `EmailThread.message_id`, append comment; otherwise create new ticket
    - Store `EmailThread` record for every inbound message
    - _Requirements: 4.1, 4.2_
  - [~] 11.3 Write property test for inbound email round-trip
    - **Property 7: Inbound Email Round-Trip**
    - **Validates: Requirements 4.1, 4.2**
    - Generate arbitrary valid RFC 2822 email strings; assert parsing produces correct DTO fields and `EmailToTicketAction` creates or threads correctly
  - [x] 11.4 Implement `IngestEmailCommand` (`email:ingest`) — read raw email from stdin, call `EmailParser` then `EmailToTicketAction`
    - _Requirements: 4.2_
  - [x] 11.5 Implement cPanel pipe script stub (`scripts/pipe.php`) and document `.forward` configuration
    - _Requirements: 4.2_

- [ ] 12. Implement outbound email (`TicketMailer`)
  - [x] 12.1 Implement `TicketMailer` — send ticket created, comment added, status changed, and SLA breach notifications using Laravel Mail
    - Include `Message-ID` and `In-Reply-To` headers for threading
    - _Requirements: 4.3_
  - [~] 12.2 Write property test for outbound email threading
    - **Property 8: Outbound Email Threading**
    - **Validates: Requirements 4.3**
    - For any sequence of outbound mails on the same ticket, assert each mail after the first carries `In-Reply-To` matching the previous `Message-ID`

- [x] 13. Checkpoint — Email integration
  - Ensure inbound pipe creates/threads tickets, outbound mails carry correct threading headers. Ask the user if questions arise.

#### Knowledge Base

- [ ] 14. Implement `ArticleService` and `ArticleSearchService`
  - [x] 14.1 Implement `ArticleService` — CRUD for `KnowledgeArticle` with draft/published status, slug generation, category assignment
    - _Requirements: 5.1, 5.2_
  - [~] 14.2 Implement `ArticleSearchService` using TNTSearch — index articles on save, full-text search returning ranked results
    - _Requirements: 5.3_
  - [~] 14.3 Write property test for knowledge article search round-trip
    - **Property 9: Knowledge Article Search Round-Trip**
    - **Validates: Requirements 5.3**
    - Index a generated set of articles; for each article assert a search on a unique term from its title returns that article in results
  - [~] 14.4 Build Livewire knowledge base UI — article list, category tree, article editor (Markdown), search bar with live results
    - _Requirements: 5.1, 5.2, 5.3_

#### Self-Service Portal

- [ ] 15. Implement `PortalController` and `ServiceCatalogueService`
  - [~] 15.1 Implement `PortalController` — public-facing routes for end users: submit ticket, view own tickets, search knowledge base
    - Apply `end_user` permission gate; support guest token access via `GuestTicketToken`
    - _Requirements: 6.1, 6.2_
  - [~] 15.2 Implement `ServiceCatalogueService` — define service catalogue items with custom fields, map to ticket type/priority on submission
    - _Requirements: 6.1_
  - [~] 15.3 Implement `GuestTicketToken` — generate signed URL token for unauthenticated ticket status lookup
    - _Requirements: 6.2_
  - [~] 15.4 Implement `CsatService` — send CSAT survey email on ticket close, record rating via tokenised URL, enforce one survey per ticket per requester
    - _Requirements: 6.3_
  - [~] 15.5 Write property test for CSAT survey uniqueness and idempotence
    - **Property 11: CSAT Survey Uniqueness and Idempotence**
    - **Validates: Requirements 6.3**
    - Submit CSAT rating multiple times for the same ticket/requester; assert only one `csat_surveys` record exists and rating reflects last valid submission

- [~] 16. Checkpoint — Self-service portal and knowledge base
  - Ensure portal ticket submission, guest token lookup, KB search, and CSAT flow work end-to-end. Ask the user if questions arise.

---

### Phase 2 — ITSM Maturity

#### Change & Problem Management

- [~] 17. Implement Change Management module
  - Extend `tickets` table with `change_type`, `risk_level`, `cab_approval_required`, `scheduled_at` columns via migration
  - Implement `ChangeApprovalWorkflow` — route change tickets through CAB approval state (`pending_approval → approved/rejected`)
  - Add Livewire change calendar view
  - _Requirements: 7.1_

- [~] 18. Implement Problem Management module
  - Add `problem_id` FK on `tickets` for linking incidents to problems
  - Implement `ProblemService` — aggregate linked incidents, track root cause, known error status
  - _Requirements: 7.2_

#### Automation Engine

- [ ] 19. Implement `AutomationEngine` core
  - [~] 19.1 Implement `TriggerRegistry` — register event-based triggers (`ticket.created`, `ticket.updated`, `comment.added`, `sla.breached`)
    - _Requirements: 8.1_
  - [~] 19.2 Implement `ConditionEvaluator` — evaluate JSON condition trees (field comparisons, AND/OR logic) against a ticket context object
    - _Requirements: 8.2_
  - [~] 19.3 Write property test for automation condition fidelity
    - **Property 10: Automation Condition Fidelity**
    - **Validates: Requirements 8.2**
    - Generate random condition trees and matching/non-matching ticket contexts; assert evaluator returns true iff context satisfies all conditions
  - [~] 19.4 Implement `ActionExecutor` — execute automation actions: assign ticket, change status, add comment, send notification, trigger webhook
    - _Requirements: 8.3_
  - [~] 19.5 Wire `AutomationEngine` into event listeners — on each registered trigger event, load active automations, evaluate conditions, execute matching actions, log to `automation_logs`
    - _Requirements: 8.1, 8.2, 8.3_
  - [~] 19.6 Build Livewire automation builder UI — visual rule editor for triggers, conditions, and actions
    - _Requirements: 8.4_

- [~] 20. Checkpoint — Automation engine
  - Ensure automations trigger correctly on events, conditions evaluate accurately, actions execute and log. Ask the user if questions arise.

#### IT Asset Management

- [ ] 21. Implement `AssetService` and `AssetImporter`
  - [~] 21.1 Implement `AssetService` — CRUD for `Asset` model, assign/unassign to users, track status transitions
    - _Requirements: 9.1, 9.2_
  - [~] 21.2 Implement `AssetImporter` — bulk import assets from CSV using `maatwebsite/excel`, validate rows, report errors
    - _Requirements: 9.3_
  - [~] 21.3 Write property test for asset assignment round-trip
    - **Property 13: Asset Assignment Round-Trip**
    - **Validates: Requirements 9.2**
    - Assign and unassign assets to random users; assert `assigned_to` reflects current state and activity log records each change
  - [~] 21.4 Build Livewire asset management UI — asset list, detail view, assignment panel, import wizard
    - _Requirements: 9.1, 9.2, 9.3_

- [~] 22. Checkpoint — Asset management
  - Ensure asset CRUD, CSV import, and assignment tracking work correctly. Ask the user if questions arise.

---

### Phase 3 — Intelligence & Scale

#### Reporting & Analytics

- [ ] 23. Implement `ReportBuilder` and `ReportExporter`
  - [~] 23.1 Implement `ReportBuilder` — query builder for standard reports: ticket volume, SLA compliance, agent performance, CSAT scores, asset inventory
    - _Requirements: 10.1, 10.2_
  - [~] 23.2 Implement `ReportExporter` — export report results to PDF (`barryvdh/laravel-dompdf`) and Excel (`maatwebsite/excel`)
    - _Requirements: 10.3_
  - [~] 23.3 Write property test for report export validity
    - **Property 14: Report Export Validity**
    - **Validates: Requirements 10.3**
    - Generate datasets of known size; export to PDF and Excel; assert exported row count matches input dataset count and no export throws an exception
  - [~] 23.4 Build `DashboardWidget` Livewire components — real-time counters (open tickets, SLA breaches, unassigned), chart widgets using Chart.js
    - _Requirements: 10.1_

- [~] 24. Implement AI Assist (Phase 3)
  - Implement `AiAssistService` — integrate configurable LLM API (OpenAI-compatible) for ticket summarisation, suggested KB articles, and draft reply generation
  - Add AI assist panel to `TicketResource` Livewire component
  - _Requirements: 11.1, 11.2_

#### Multi-Tenancy

- [ ] 25. Implement multi-tenancy layer
  - [~] 25.1 Implement `TenantResolver` — resolve tenant from subdomain or path prefix, load tenant config
    - _Requirements: 12.1_
  - [~] 25.2 Implement `TenantScope` — global Eloquent scope applying `tenant_id` filter to all tenant-scoped models
    - Add `tenant_id` column to all core tables via migration
    - _Requirements: 12.2_
  - [~] 25.3 Implement `TenantProvisioner` — create tenant record, run tenant-scoped seeders, provision subdomain config
    - _Requirements: 12.3_
  - [~] 25.4 Build MSP/hosting provider admin panel — tenant list, provision new tenant, suspend/activate tenant
    - _Requirements: 12.4_

- [~] 26. Checkpoint — Multi-tenancy
  - Ensure tenant isolation is enforced at the query level, provisioning creates a usable tenant, and the admin panel lists tenants correctly. Ask the user if questions arise.

---

### Cross-Cutting Concerns

- [~] 27. Implement Laravel Reverb WebSocket integration
  - Broadcast `TicketUpdated`, `CommentAdded`, `SlaBreached` events over Reverb channels
  - Subscribe in Livewire components for real-time UI updates without page refresh
  - _Requirements: 2.5, 3.5_

- [~] 28. Implement backup and health monitoring
  - Configure `spatie/laravel-backup` for daily database + storage backup with configurable destination
  - Implement `/health` endpoint returning JSON status of DB, queue, cache, and disk
  - _Requirements: Platform operations_

- [~] 29. Final checkpoint — Full test suite and static analysis
  - Run PHPUnit feature and unit test suite; assert all tests pass
  - Run Pest PHP property-based tests (100 iterations each for all 14 properties)
  - Run PHPStan at level 8; resolve all reported issues
  - Run Laravel Pint; apply all code style fixes
  - Ensure all tests pass, ask the user if questions arise.

---

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- Each task references specific requirements for traceability
- Phases 2 and 3 depend on Phase 1 being complete and stable
- Property tests use Pest PHP with 100 iterations each
- All 14 correctness properties from the design document are covered by property test sub-tasks
- Checkpoints ensure incremental validation at each phase boundary
