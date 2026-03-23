# ServiceFlow Enterprise Service Desk — Walkthrough

> Last updated: all 29 tasks complete across all three phases.

---

## Project Overview

ServiceFlow is a full-featured Laravel 11 enterprise service desk built with Livewire 3, Tailwind CSS, and Alpine.js. It covers the full ITSM lifecycle from ticket intake through SLA enforcement, automation, asset management, reporting, AI assist, and multi-tenancy.

---

## Directory Structure

```
serviceflow/
├── app/
│   ├── Actions/          # Single-responsibility action classes
│   ├── Console/Commands/ # Artisan commands (email:ingest)
│   ├── DTOs/             # Data transfer objects (ParsedEmail)
│   ├── Events/           # Domain events (TicketCreated, TicketUpdated, CommentAdded, SlaBreached)
│   ├── Exceptions/       # Custom exceptions (InvalidStatusTransitionException)
│   ├── Http/
│   │   ├── Controllers/  # PortalController, InstallerController, HealthController
│   │   └── Middleware/   # InstallerMiddleware
│   ├── Jobs/             # SlaEscalationJob
│   ├── Listeners/        # Event listeners (SLA, email, automation)
│   ├── Livewire/         # Livewire components (Tickets, Knowledge, Automation, Asset, Dashboard, Admin)
│   ├── Mail/             # Mailable classes
│   ├── Models/           # Eloquent models
│   ├── Observers/        # TicketObserver, KnowledgeArticleObserver
│   ├── Policies/         # Gate policies (Ticket, Article, Asset, Comment)
│   ├── Providers/        # AppServiceProvider (observer + event wiring)
│   ├── Scopes/           # TenantScope (global Eloquent scope)
│   └── Services/         # Domain service classes (see below)
├── config/
│   ├── ai.php            # AI Assist (OpenAI-compatible) config
│   └── permission.php    # Spatie permission config
├── database/
│   ├── migrations/       # ~30 migration files
│   └── seeders/          # RolesAndPermissionsSeeder, DatabaseSeeder
├── docs/
│   └── walkthrough.md    # This file
│   └── cpanel-email-pipe.md
├── resources/views/
│   ├── agent/            # Agent-facing Blade views (tickets, knowledge)
│   ├── emails/           # Email templates
│   ├── installer/        # Multi-step installer wizard
│   ├── livewire/         # Livewire component views
│   └── portal/           # Self-service portal views (tickets, catalogue, CSAT)
├── routes/
│   ├── api.php           # /api/health endpoint
│   ├── channels.php      # Reverb broadcast channel auth
│   ├── console.php       # Scheduled jobs (SLA escalation, backup)
│   └── web.php           # Agent, portal, installer, CSAT routes
├── scripts/
│   └── pipe.php          # cPanel email pipe entry point
└── tests/
    └── Unit/             # 14 property-based tests (Pest PHP, 100 iterations each)
```

---

## Phase Progress

### Phase 0 — Foundation & Installer

| Task | Description | Status |
|------|-------------|--------|
| 1 | Laravel 11 scaffold + dependencies | ✅ |
| 2 | Core database migrations | ✅ |
| 3 | Roles, permissions, base models | ✅ |
| 4 | Web installer (EnvironmentChecker, DatabaseInstaller, CpanelPipeInstaller, wizard) | ✅ |
| 5 | Checkpoint | ✅ |

### Phase 1 — MVP Modules

| Task | Description | Status |
|------|-------------|--------|
| 6 | TicketStatusMachine, CreateTicketAction, MergeTicketsAction, TicketObserver | ✅ |
| 7 | Livewire ticket UI (TicketListComponent, TicketResource, TicketTriageQueue) | ✅ |
| 8 | Checkpoint | ✅ |
| 9 | SlaService, BusinessHoursCalculator, SlaEscalationJob | ✅ |
| 10 | Checkpoint | ✅ |
| 11 | Inbound email pipeline (EmailParser, EmailToTicketAction, IngestEmailCommand) | ✅ |
| 12 | Outbound email (TicketMailer, mail templates) | ✅ |
| 13 | Checkpoint | ✅ |
| 14 | ArticleService, ArticleSearchService, Livewire KB UI | ✅ |
| 15 | PortalController, ServiceCatalogueService, GuestTicketToken, CsatService | ✅ |
| 16 | Checkpoint | ✅ |

### Phase 2 — ITSM Maturity

| Task | Description | Status |
|------|-------------|--------|
| 17 | Change Management (ChangeApprovalWorkflow, Livewire calendar) | ✅ |
| 18 | Problem Management (ProblemService, problem_id FK) | ✅ |
| 19 | AutomationEngine (TriggerRegistry, ConditionEvaluator, ActionExecutor, Livewire builder) | ✅ |
| 20 | Checkpoint | ✅ |
| 21 | AssetService, AssetImporter, Livewire asset UI | ✅ |
| 22 | Checkpoint | ✅ |

### Phase 3 — Intelligence & Scale

| Task | Description | Status |
|------|-------------|--------|
| 23 | ReportBuilder, ReportExporter, DashboardWidgets | ✅ |
| 24 | AiAssistService + TicketResource AI panel | ✅ |
| 25 | TenantResolver, TenantScope, TenantProvisioner, TenantManager admin panel | ✅ |
| 26 | Checkpoint | ✅ |
| 27 | Laravel Reverb WebSocket (TicketUpdated, CommentAdded, SlaBreached broadcast) | ✅ |
| 28 | Backup schedule + /api/health endpoint | ✅ |
| 29 | Final checkpoint (tests, static analysis, code style) | ✅ |

---

## Property-Based Tests

All 14 correctness properties are implemented in `tests/Unit/` using Pest PHP with 100 iterations each.

| # | Property | Test File | Requirement |
|---|----------|-----------|-------------|
| 1 | Ticket Creation Invariants | TicketCreationTest.php | 2.1 |
| 2 | Status Transition Validity | TicketStatusMachineTest.php | 2.3 |
| 3 | SLA Assignment on Ticket Creation | SlaAssignmentTest.php | 3.1 |
| 4 | First Response Stops SLA Timer | SlaFirstResponseTest.php | 3.2 |
| 5 | SLA Breach Detection | SlaBreachDetectionTest.php | 3.4 |
| 6 | Business Hours Exclusion from SLA | BusinessHoursCalculatorTest.php | 3.3 |
| 7 | Inbound Email Round-Trip | InboundEmailRoundTripTest.php | 4.1, 4.2 |
| 8 | Outbound Email Threading | OutboundEmailThreadingTest.php | 4.3 |
| 9 | Knowledge Article Search Round-Trip | ArticleSearchRoundTripTest.php | 5.3 |
| 10 | Automation Condition Fidelity | AutomationConditionTest.php | 8.2 |
| 11 | CSAT Survey Uniqueness and Idempotence | CsatSurveyTest.php | 6.3 |
| 12 | Ticket Merge Completeness | TicketMergeTest.php | 2.6 |
| 13 | Asset Assignment Round-Trip | AssetAssignmentTest.php | 9.2 |
| 14 | Report Export Validity | ReportExportTest.php | 10.3 |

---

## Key Domain Concepts

### Ticket Lifecycle
```
open → in_progress → pending → resolved → closed
                                    ↑
                              (re-open path)
resolved/closed → open
```

### SLA Flow
1. Ticket created → `AssignSlaPolicy` listener → `SlaTimer` record created
2. First agent comment → `RecordFirstResponse` listener → `first_response_at` stamped
3. `SlaEscalationJob` runs every minute → `SlaService::checkBreach` → fires `SlaBreached` if exceeded

### Email Pipeline
- Inbound: cPanel `.forward` → `scripts/pipe.php` → `email:ingest` → `EmailParser` → `EmailToTicketAction`
- Outbound: `TicketMailer` → Laravel Mail with `Message-ID` / `In-Reply-To` threading headers

### Automation Engine
- Triggers: `ticket.created`, `ticket.updated`, `comment.added`, `sla.breached`
- Conditions: JSON condition trees evaluated by `ConditionEvaluator` (AND/OR, field comparisons)
- Actions: assign, change status, add comment, send notification, webhook

### Multi-Tenancy
- `TenantResolver` resolves tenant from subdomain or path prefix
- `TenantScope` global Eloquent scope applies `tenant_id` filter automatically
- `TenantProvisioner` creates tenant + admin user + default SLA policies

---

## Quick Commands

```bash
# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# Run property-based tests
./vendor/bin/pest tests/Unit --bail

# Ingest a raw email from stdin
php artisan email:ingest < /path/to/email.eml

# Run SLA escalation manually
php artisan schedule:run

# Start Reverb WebSocket server
php artisan reverb:start

# Run backup
php artisan backup:run

# Health check
curl http://localhost/api/health
```

---

## Environment Variables

Key `.env` values to configure:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=serviceflow

# Mail
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=support@example.com

# Queue (use database or redis in production)
QUEUE_CONNECTION=database

# AI Assist (OpenAI-compatible)
AI_ASSIST_ENDPOINT=https://api.openai.com/v1
AI_ASSIST_API_KEY=sk-...
AI_ASSIST_MODEL=gpt-4o-mini

# Reverb WebSocket
REVERB_APP_ID=serviceflow
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
```
