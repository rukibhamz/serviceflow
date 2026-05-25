# Requirements Document

## Introduction

This document defines the requirements for a comprehensive QA audit and test suite for **ServiceFlow**, a Laravel/Livewire-based enterprise service desk application. The audit covers all application modules, Livewire components, HTTP controllers, service classes, policies, events, and listeners. It encompasses functional testing, component testing, code integrity analysis, security assessment, integration testing, performance testing, and property-based correctness verification.

The goal is to produce a repeatable, automated test suite that gives the engineering team high confidence in the correctness, security, and reliability of every release.

---

## Glossary

- **ServiceFlow**: The Laravel/Livewire enterprise service desk application under test.
- **Test_Suite**: The collection of PHPUnit/Pest test files produced by this audit.
- **Static_Analyser**: PHPStan or Larastan running at level 6 or higher.
- **Linter**: Laravel Pint (PHP-CS-Fixer preset) enforcing PSR-12 and Laravel conventions.
- **PBT_Library**: Pest's `arch()` plugin or a property-based library such as `eris/eris` used for generative testing.
- **Tenant**: An isolated organisational unit identified by `tenant_id`; all data is scoped to a single tenant.
- **TenantScope**: The global Eloquent scope (`App\Scopes\TenantScope`) that automatically filters queries by the current `tenant_id`.
- **StatusMachine**: `App\Services\Tickets\TicketStatusMachine`  enforces valid ticket status transitions.
- **ChangeWorkflow**: `App\Services\Change\ChangeApprovalWorkflow`  manages the CAB approval sub-graph for change tickets.
- **SlaService**: `App\Services\Sla\SlaService`  assigns SLA policies and records first-response times.
- **BusinessHoursCalculator**: `App\Services\Sla\BusinessHoursCalculator`  computes business-time durations.
- **AutomationEngine**: `App\Services\Automation\AutomationEngine`  processes trigger events and executes matching automation rules.
- **ConditionEvaluator**: `App\Services\Automation\ConditionEvaluator`  evaluates JSON condition trees against a ticket.
- **ActionExecutor**: `App\Services\Automation\ActionExecutor`  executes automation action definitions.
- **EmailParser**: `App\Services\Email\EmailParser`  parses raw RFC 2822 email strings into `ParsedEmail` DTOs.
- **EmailToTicketAction**: `App\Actions\Email\EmailToTicketAction`  converts a parsed email into a ticket or comment.
- **TenantResolver**: `App\Services\Tenant\TenantResolver`  resolves the current tenant from the HTTP request.
- **TenantProvisioner**: `App\Services\Tenant\TenantProvisioner`  creates new tenant records and seeds default data.
- **ArticleService**: `App\Services\Knowledge\ArticleService`  manages knowledge base article lifecycle.
- **TicketPolicy**: `App\Policies\TicketPolicy`  Laravel Gate policy controlling ticket access.
- **ULID**: Universally Unique Lexicographically Sortable Identifier assigned to every ticket.
- **CAB**: Change Advisory Board  the group of approvers for change tickets.
- **SLA**: Service Level Agreement  defines response and resolution time targets.
- **CSAT**: Customer Satisfaction survey sent after ticket resolution.
- **CSRF**: Cross-Site Request Forgery protection token required on all state-mutating HTTP requests.
- **XSS**: Cross-Site Scripting  injection of malicious scripts via user-supplied input.
- **IDOR**: Insecure Direct Object Reference  accessing another tenant's resources by guessing IDs.

---

## Requirements

### Requirement 1: Ticket Management  Functional Testing

**User Story:** As a QA engineer, I want to verify all ticket lifecycle operations, so that I can confirm the core service desk workflow is correct.

#### Acceptance Criteria

1. WHEN a valid ticket creation request is submitted, THE Test_Suite SHALL assert that a `Ticket` record is persisted with `status = open`, a non-empty `ulid`, and the correct `requester_id`.
2. WHEN a ticket of type `problem` or `change` is created without a `team_id`, THE Test_Suite SHALL assert that the controller returns a validation error for `team_id`.
3. WHEN a ticket of type `change` is submitted for approval without `change_approver_ids`, THE Test_Suite SHALL assert that the controller returns a validation error for `change_approver_ids`.
4. WHEN `TicketController::store` is called with file attachments, THE Test_Suite SHALL assert that each attachment is stored in the `attachments` media collection via Spatie MediaLibrary.
5. WHEN `TicketController::updateStatus` is called with a valid status transition, THE Test_Suite SHALL assert that the ticket status is updated and the response is HTTP 204.
6. WHEN `TicketController::updateStatus` is called with an invalid status transition, THE Test_Suite SHALL assert that `InvalidStatusTransitionException` is thrown and the ticket status is unchanged.
7. WHEN `MergeTicketsAction` is executed, THE Test_Suite SHALL assert that the source ticket's `merged_into_id` is set to the target ticket ID and the source ticket status becomes `closed`.
8. THE Test_Suite SHALL assert that every ticket has a unique `ulid` after creation.
9. WHEN a ticket is closed, THE Test_Suite SHALL assert that `closed_at` is populated and all open SLA timers have `stopped_at` set.

### Requirement 2: Ticket Status Machine  Correctness Properties

**User Story:** As a QA engineer, I want to verify the ticket status state machine enforces valid transitions, so that tickets cannot reach illegal states.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that `StatusMachine::canTransition` returns `true` for every edge defined in `TicketStatusMachine::TRANSITIONS` and `false` for every pair not in that map.
2. WHEN `StatusMachine::transition` is called with an invalid target status, THE Test_Suite SHALL assert that `InvalidStatusTransitionException` is thrown and the ticket is not saved.
3. WHEN a ticket transitions to `pending`, THE Test_Suite SHALL assert that all active, non-breached SLA timers have an open `SlaPause` record created with `paused_at` set.
4. WHEN a ticket transitions away from `pending`, THE Test_Suite SHALL assert that the active `SlaPause` record has `resumed_at` set and the SLA timer `due_at` is extended by the pause duration in minutes.
5. WHEN a ticket transitions to `closed`, THE Test_Suite SHALL assert that `closed_at` is set and all open SLA timers are stopped.
6. FOR ALL valid status sequences reachable from `open`, THE Test_Suite SHALL assert that applying `StatusMachine::transition` step-by-step produces the expected final status (round-trip property over the transition graph).
7. THE Test_Suite SHALL assert that `StatusMachine::validStatuses` returns exactly `['open', 'in_progress', 'pending', 'resolved', 'closed']`.

### Requirement 3: Change Approval Workflow  Functional Testing

**User Story:** As a QA engineer, I want to verify the CAB approval workflow, so that change tickets follow the correct approval state machine.

#### Acceptance Criteria

1. WHEN `ChangeApprovalWorkflow::submitForApproval` is called on a ticket with `cab_approval_required = true` and status `open`, THE Test_Suite SHALL assert that the ticket status becomes `pending_approval`.
2. WHEN `ChangeApprovalWorkflow::submitForApproval` is called on a ticket with `cab_approval_required = false`, THE Test_Suite SHALL assert that a `LogicException` is thrown.
3. WHEN `ChangeApprovalWorkflow::approve` is called on a ticket in `pending_approval` status, THE Test_Suite SHALL assert that the ticket status becomes `approved`.
4. WHEN `ChangeApprovalWorkflow::reject` is called on a ticket in `pending_approval` status, THE Test_Suite SHALL assert that the ticket status becomes `rejected`.
5. WHEN `ChangeApprovalWorkflow::schedule` is called on an `approved` ticket, THE Test_Suite SHALL assert that the ticket status becomes `scheduled` and `scheduled_at` is set to the provided datetime.
6. WHEN `ChangeApprovalWorkflow::transition` is called with a status not in `TRANSITIONS[current]`, THE Test_Suite SHALL assert that `InvalidStatusTransitionException` is thrown.
7. THE Test_Suite SHALL assert that `Ticket::isFullyApproved` returns `true` only when all `ChangeApprover` records have `decision = approved`.
8. THE Test_Suite SHALL assert that `Ticket::hasRejection` returns `true` when at least one `ChangeApprover` record has `decision = rejected`.

### Requirement 4: SLA Service  Functional and Property Testing

**User Story:** As a QA engineer, I want to verify SLA policy assignment and breach detection, so that service level commitments are tracked accurately.

#### Acceptance Criteria

1. WHEN `SlaService::assignPolicy` is called for a ticket, THE Test_Suite SHALL assert that `SlaTimer` records are created for both `response` and `resolution` types with `due_at` in the future.
2. WHEN a matching `SlaPolicy` exists for the ticket's priority and type, THE Test_Suite SHALL assert that the specific policy is selected over the default policy.
3. WHEN no matching policy exists, THE Test_Suite SHALL assert that the default policy (`is_default = true`) is used.
4. WHEN no policy exists at all, THE Test_Suite SHALL assert that no `SlaTimer` records are created and no exception is thrown.
5. WHEN `SlaService::recordFirstResponse` is called with a non-internal comment, THE Test_Suite SHALL assert that the `response` SLA timer's `stopped_at` is set.
6. WHEN `SlaService::recordFirstResponse` is called with an internal comment, THE Test_Suite SHALL assert that the `response` SLA timer is not stopped.
7. WHEN `SlaService::checkBreach` is called on a timer whose `due_at` is in the past, THE Test_Suite SHALL assert that `breached` is set to `true` and a `SlaBreached` event is dispatched.
8. WHEN `SlaService::checkBreach` is called on a timer that is already breached, THE Test_Suite SHALL assert that the method returns `false` and no duplicate event is dispatched.
9. WHEN `SlaService::checkBreach` is called on a timer with `stopped_at` set, THE Test_Suite SHALL assert that the method returns `false`.

### Requirement 5: Business Hours Calculator  Property Testing

**User Story:** As a QA engineer, I want to verify the business hours calculator with property-based tests, so that SLA due dates are computed correctly across all schedule configurations.

#### Acceptance Criteria

1. FOR ALL valid schedule configurations and positive minute values, THE Test_Suite SHALL assert that `BusinessHoursCalculator::addBusinessMinutes` returns a timestamp that is greater than or equal to the start time (monotonicity property).
2. FOR ALL valid schedule configurations, THE Test_Suite SHALL assert that the result of `addBusinessMinutes(start, minutes, schedule)` falls within a business window as defined by `isBusinessTime` (output validity property).
3. FOR ALL valid schedule configurations and two timestamps `start` and `end` where `end > start`, THE Test_Suite SHALL assert that `elapsedBusinessMinutes(start, end, schedule) >= 0` (non-negativity invariant).
4. FOR ALL valid schedule configurations, THE Test_Suite SHALL assert that `elapsedBusinessMinutes(start, addBusinessMinutes(start, N, schedule), schedule) == N` for any positive integer N (round-trip property).
5. WHEN `isBusinessTime` is called with a timestamp on a holiday defined in the schedule, THE Test_Suite SHALL assert that it returns `false`.
6. WHEN `isBusinessTime` is called with a timestamp outside the configured day/time window, THE Test_Suite SHALL assert that it returns `false`.
7. WHEN `elapsedBusinessMinutes` is called with `end <= start`, THE Test_Suite SHALL assert that it returns `0`.

### Requirement 6: Automation Engine  Functional and Property Testing

**User Story:** As a QA engineer, I want to verify the automation engine processes triggers, evaluates conditions, and executes actions correctly, so that automated workflows behave as configured.

#### Acceptance Criteria

1. WHEN `AutomationEngine::process` is called with a trigger event, THE Test_Suite SHALL assert that only automations with `trigger_event` matching the event and `is_active = true` are evaluated.
2. WHEN an automation's conditions are satisfied, THE Test_Suite SHALL assert that all configured actions are executed, `run_count` is incremented, `last_run_at` is updated, and an `AutomationLog` record with `result = success` is created.
3. WHEN an automation's conditions are not satisfied, THE Test_Suite SHALL assert that no actions are executed and no `AutomationLog` record is created.
4. WHEN an action execution throws an exception, THE Test_Suite SHALL assert that the exception is caught, an `AutomationLog` record with `result` prefixed by `error:` is created, and remaining automations continue processing.
5. WHEN `ConditionEvaluator::evaluate` is called with an empty conditions array, THE Test_Suite SHALL assert that it returns `true`.
6. WHEN `ConditionEvaluator::evaluate` is called with `operator = AND`, THE Test_Suite SHALL assert that it returns `true` only when all conditions match.
7. WHEN `ConditionEvaluator::evaluate` is called with `operator = OR`, THE Test_Suite SHALL assert that it returns `true` when at least one condition matches.
8. FOR ALL supported field operators (`equals`, `not_equals`, `contains`, `not_contains`, `starts_with`, `ends_with`, `greater_than`, `less_than`, `is_null`, `is_not_null`), THE Test_Suite SHALL assert that `ConditionEvaluator` returns the correct boolean for representative ticket field values.
9. WHEN `ConditionEvaluator` resolves a `custom_fields.` dot-notation field, THE Test_Suite SHALL assert that the nested value is correctly extracted from the ticket's `custom_fields` JSON.
10. WHEN `ActionExecutor` executes an `assign_ticket` action with a valid `assignee_id`, THE Test_Suite SHALL assert that the ticket's `assignee_id` is updated.
11. WHEN `ActionExecutor` executes a `change_status` action, THE Test_Suite SHALL assert that `StatusMachine::transition` is invoked and the ticket status changes.
12. WHEN `ActionExecutor` executes an `add_comment` action, THE Test_Suite SHALL assert that a `TicketComment` record is created with `user_id = null` and the specified body.
13. WHEN `ActionExecutor` executes a `trigger_webhook` action, THE Test_Suite SHALL assert that an HTTP request is sent to the configured URL with the ticket payload.
14. FOR ALL condition trees with `operator = AND`, THE Test_Suite SHALL assert that negating any single condition that was previously true causes the overall result to become `false` (metamorphic property).

### Requirement 7: Email Parser  Property Testing

**User Story:** As a QA engineer, I want to verify the email parser with property-based and round-trip tests, so that inbound emails are reliably converted to structured data.

#### Acceptance Criteria

1. WHEN `EmailParser::parse` is called with a well-formed RFC 2822 email string, THE Test_Suite SHALL assert that the returned `ParsedEmail` has a non-empty `fromAddress`, non-empty `subject`, and non-null `body`.
2. WHEN `EmailParser::parse` is called with an email containing a `Message-ID` header, THE Test_Suite SHALL assert that the `messageId` field equals the header value with angle brackets stripped.
3. WHEN `EmailParser::parse` is called with an email containing an `In-Reply-To` header, THE Test_Suite SHALL assert that the `inReplyTo` field equals the header value with angle brackets stripped.
4. WHEN `EmailParser::parse` is called with a `Content-Transfer-Encoding: base64` body, THE Test_Suite SHALL assert that the body is correctly base64-decoded.
5. WHEN `EmailParser::parse` is called with a `Content-Transfer-Encoding: quoted-printable` body, THE Test_Suite SHALL assert that the body is correctly decoded.
6. WHEN `EmailParser::parse` is called with a `Content-Type: text/html` body, THE Test_Suite SHALL assert that HTML tags are stripped from the returned body.
7. WHEN `EmailParser::parse` is called with RFC 2047 encoded-word headers (e.g. `=?UTF-8?B?...?=`), THE Test_Suite SHALL assert that the decoded subject is a valid UTF-8 string.
8. FOR ALL valid plain-text email strings, THE Test_Suite SHALL assert that parsing produces a `ParsedEmail` whose `fromAddress` is a syntactically valid email address (invariant property).
9. THE Test_Suite SHALL assert that `EmailParser::parse` does not throw an exception for any input string, including empty strings and malformed headers (robustness property).

### Requirement 8: Email-to-Ticket Integration  Functional Testing

**User Story:** As a QA engineer, I want to verify the email-to-ticket pipeline, so that inbound emails correctly create tickets or append comments to existing threads.

#### Acceptance Criteria

1. WHEN `EmailToTicketAction::execute` is called with a `ParsedEmail` whose `inReplyTo` matches an existing `EmailThread.message_id`, THE Test_Suite SHALL assert that a `TicketComment` is appended to the existing ticket and no new ticket is created.
2. WHEN `EmailToTicketAction::execute` is called with a `ParsedEmail` whose `inReplyTo` does not match any `EmailThread`, THE Test_Suite SHALL assert that a new `Ticket` is created with `source = email`.
3. WHEN `EmailToTicketAction::execute` is called and the sender email does not exist in the `users` table, THE Test_Suite SHALL assert that a new `User` record is created with `role = end_user`.
4. WHEN `EmailToTicketAction::execute` is called and the `To` header matches a team's `inbound_email`, THE Test_Suite SHALL assert that the created ticket has the correct `team_id`.
5. WHEN `EmailToTicketAction::execute` is called with an email with an empty subject, THE Test_Suite SHALL assert that the ticket subject is set to `(No Subject)`.
6. WHEN `EmailToTicketAction::execute` is called, THE Test_Suite SHALL assert that an `EmailThread` record is created for every processed email regardless of whether a new ticket or comment was created.

### Requirement 9: Multi-Tenancy Isolation  Security and Functional Testing

**User Story:** As a QA engineer, I want to verify that tenant data isolation is enforced at every layer, so that one tenant cannot access another tenant's data.

#### Acceptance Criteria

1. WHEN `TenantScope` is applied to any Eloquent query, THE Test_Suite SHALL assert that the generated SQL includes a `WHERE tenant_id = ?` clause matching the current tenant.
2. WHEN `TenantResolver::resolve` is called with a request containing a valid subdomain, THE Test_Suite SHALL assert that the correct `Tenant` record is returned and set as current.
3. WHEN `TenantResolver::resolve` is called with a request for an inactive tenant, THE Test_Suite SHALL assert that `null` is returned and no current tenant is set.
4. WHEN a user authenticated as Tenant A attempts to access a ticket belonging to Tenant B via a direct URL, THE Test_Suite SHALL assert that the response is HTTP 404 or HTTP 403.
5. WHEN `TenantProvisioner::provision` is called with a duplicate subdomain, THE Test_Suite SHALL assert that an `InvalidArgumentException` is thrown and no tenant record is created.
6. WHEN `TenantProvisioner::provision` is called with valid data, THE Test_Suite SHALL assert that the new tenant, admin user, and four default SLA policies are created within a single database transaction.
7. WHEN `TenantProvisioner::suspend` is called, THE Test_Suite SHALL assert that `is_active` is set to `false` and the tenant cannot be resolved by `TenantResolver`.
8. THE Test_Suite SHALL assert that all models using `TenantScope` set `tenant_id` automatically on creation when a current tenant is active.
9. FOR ALL tenant-scoped models, THE Test_Suite SHALL assert that a query executed under Tenant A's context returns zero records belonging to Tenant B (cross-tenant isolation property).

### Requirement 10: Authentication and Authorisation  Security Testing

**User Story:** As a QA engineer, I want to verify authentication flows and role-based access controls, so that unauthorised access is prevented.

#### Acceptance Criteria

1. WHEN `AuthController::store` is called with valid credentials, THE Test_Suite SHALL assert that the session is regenerated and the user is redirected to the role-appropriate dashboard.
2. WHEN `AuthController::store` is called with invalid credentials, THE Test_Suite SHALL assert that the response redirects back with an `email` error and no session is created.
3. WHEN `AuthController::updatePassword` is called with an incorrect `current_password`, THE Test_Suite SHALL assert that the password is not changed and a validation error is returned.
4. WHEN an unauthenticated request is made to any protected route, THE Test_Suite SHALL assert that the response is a redirect to the login page or HTTP 401.
5. WHEN a user with role `end_user` attempts to access an admin-only route, THE Test_Suite SHALL assert that the response is HTTP 403 or a redirect.
6. WHEN a user with role `agent` attempts to access a manager-only route, THE Test_Suite SHALL assert that the response is HTTP 403 or a redirect.
7. WHEN `EnsureAppIsInstalled` middleware processes a request to a non-installer route before installation, THE Test_Suite SHALL assert that the request is redirected to the installer.
8. WHEN `EnsureAppIsInstalled` middleware processes a request to the installer route after installation, THE Test_Suite SHALL assert that the request is redirected to the home page.
9. WHEN `InvitationController` processes a valid invitation token, THE Test_Suite SHALL assert that the user account is created and the invitation record is deleted.
10. WHEN `InvitationController` processes an expired invitation token, THE Test_Suite SHALL assert that the registration is rejected with an appropriate error.
11. THE Test_Suite SHALL assert that all state-mutating HTTP routes include CSRF token validation by verifying that requests without a valid CSRF token receive HTTP 419.
12. WHEN `SocialAuthController` handles an OAuth callback, THE Test_Suite SHALL assert that a `SocialAccount` record is created or linked to an existing user without creating duplicate user records.

### Requirement 11: TicketPolicy  Authorisation Testing

**User Story:** As a QA engineer, I want to verify that the TicketPolicy correctly gates all ticket operations, so that access control is enforced consistently.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that `TicketPolicy::viewAny` returns the correct boolean for each role (`admin`, `manager`, `team_lead`, `agent`, `end_user`).
2. THE Test_Suite SHALL assert that `TicketPolicy::view` returns `true` for the ticket's requester and assignee, and `false` for unrelated users of the same tenant.
3. THE Test_Suite SHALL assert that `TicketPolicy::create` returns `true` for authenticated users and `false` for guests.
4. THE Test_Suite SHALL assert that `TicketPolicy::update` returns `true` for agents and managers and `false` for end users who are not the requester.
5. THE Test_Suite SHALL assert that `TicketPolicy::delete` returns `true` only for admin-role users.
6. THE Test_Suite SHALL assert that `ArticlePolicy`, `AssetPolicy`, and `CommentPolicy` enforce equivalent role-based access controls for their respective resources.

### Requirement 12: Knowledge Base  Functional Testing

**User Story:** As a QA engineer, I want to verify knowledge base article lifecycle operations, so that articles are created, versioned, published, and searched correctly.

#### Acceptance Criteria

1. WHEN `ArticleService::create` is called with valid data, THE Test_Suite SHALL assert that a `KnowledgeArticle` record and an initial `KnowledgeArticleVersion` record are created.
2. WHEN `ArticleService::create` is called with a title that already exists, THE Test_Suite SHALL assert that the generated slug is unique (e.g. appended with `-2`).
3. WHEN `ArticleService::update` is called, THE Test_Suite SHALL assert that a new `KnowledgeArticleVersion` record is created capturing the updated title and body.
4. WHEN `ArticleService::publish` is called, THE Test_Suite SHALL assert that the article status becomes `published`.
5. WHEN `ArticleService::archive` is called, THE Test_Suite SHALL assert that the article status becomes `archived`.
6. WHEN `ArticleService::create` is called by a non-admin user without a valid team category, THE Test_Suite SHALL assert that a `ValidationException` is thrown.
7. WHEN `ArticleService::incrementViewCount` is called, THE Test_Suite SHALL assert that `view_count` increases by exactly 1 without updating `updated_at`.
8. WHEN `ArticleService::vote` is called with `helpful = true`, THE Test_Suite SHALL assert that `helpful_votes` increases by 1 and `unhelpful_votes` is unchanged.
9. FOR ALL article titles, THE Test_Suite SHALL assert that `generateUniqueSlug` produces a slug that is URL-safe and unique within the tenant (invariant property).

### Requirement 13: Livewire Component Testing  Ticket Components

**User Story:** As a QA engineer, I want to verify all Livewire ticket components render correctly and respond to user interactions, so that the agent interface is reliable.

#### Acceptance Criteria

1. WHEN `TicketListComponent` is rendered, THE Test_Suite SHALL assert that it displays a paginated list of tickets scoped to the current tenant.
2. WHEN the `search` property of `TicketListComponent` is updated, THE Test_Suite SHALL assert that the ticket list is filtered to show only tickets whose `subject` or `ulid` contains the search term, and the page is reset to 1.
3. WHEN the `statusFilter` property of `TicketListComponent` is updated, THE Test_Suite SHALL assert that only tickets with the matching status are displayed.
4. WHEN `TicketListComponent::sortBy` is called with the current sort column, THE Test_Suite SHALL assert that the sort direction toggles between `asc` and `desc`.
5. WHEN `TicketListComponent::sortBy` is called with a new column, THE Test_Suite SHALL assert that `sortBy` is updated and `sortDir` is reset to `asc`.
6. WHEN `CreateTicket` component is submitted with valid data, THE Test_Suite SHALL assert that a `Ticket` record is created and a success message is emitted.
7. WHEN `CreateTicket` component is submitted with missing required fields, THE Test_Suite SHALL assert that validation errors are displayed for each missing field.
8. WHEN `TicketKanban` component is rendered, THE Test_Suite SHALL assert that tickets are grouped by status column and each column displays the correct ticket count.
9. WHEN `TicketTriageQueue` component is rendered, THE Test_Suite SHALL assert that unassigned tickets are displayed in priority order.
10. WHEN `TicketResource` component is rendered for a specific ticket, THE Test_Suite SHALL assert that the ticket details, comments, SLA timers, and attachments are displayed.

### Requirement 14: Livewire Component Testing  Admin Components

**User Story:** As a QA engineer, I want to verify all admin Livewire components, so that administrative operations function correctly.

#### Acceptance Criteria

1. WHEN `AdminDashboard` component is rendered, THE Test_Suite SHALL assert that it displays aggregate metrics including open ticket count, SLA breach count, and agent workload.
2. WHEN `UserManager` component is rendered, THE Test_Suite SHALL assert that it displays a paginated list of users with their roles and active status.
3. WHEN `TeamManager` component creates a team, THE Test_Suite SHALL assert that a `Team` record is persisted with the correct `tenant_id`.
4. WHEN `SlaManager` component saves an SLA policy, THE Test_Suite SHALL assert that the policy is persisted and the previous default policy for the same priority is unset.
5. WHEN `BrandingSettings` component saves branding data, THE Test_Suite SHALL assert that `brand_name`, `theme_primary`, and `theme_accent` are stored via `SettingService`.
6. WHEN `TenantManager` component provisions a new tenant, THE Test_Suite SHALL assert that `TenantProvisioner::provision` is called and a success message is displayed.
7. WHEN `ChangeManager` component is rendered, THE Test_Suite SHALL assert that change tickets in `pending_approval` status are listed for review.
8. WHEN `ProblemManager` component is rendered, THE Test_Suite SHALL assert that problem tickets are listed with their linked incident counts.

### Requirement 15: Livewire Component Testing  Portal and Other Components

**User Story:** As a QA engineer, I want to verify portal and auxiliary Livewire components, so that end-user-facing features work correctly.

#### Acceptance Criteria

1. WHEN `Portal\UserDashboard` component is rendered for an authenticated end user, THE Test_Suite SHALL assert that only tickets where the user is the requester are displayed.
2. WHEN `Knowledge\ArticleEditor` component saves an article, THE Test_Suite SHALL assert that `ArticleService::create` or `ArticleService::update` is called with the correct data.
3. WHEN `Knowledge\ArticleList` component is rendered, THE Test_Suite SHALL assert that only published articles are visible to end users.
4. WHEN `Automation\AutomationBuilder` component saves an automation rule, THE Test_Suite SHALL assert that the rule is persisted with the correct `trigger_event`, `conditions`, and `actions` JSON.
5. WHEN `Change\ChangeCalendar` component is rendered, THE Test_Suite SHALL assert that scheduled change tickets appear on their `scheduled_at` date.
6. WHEN `Asset\AssetList` component is rendered, THE Test_Suite SHALL assert that assets are scoped to the current tenant.
7. WHEN `Auth\Login` component is submitted with valid credentials, THE Test_Suite SHALL assert that the user is authenticated and redirected to the appropriate dashboard.
8. WHEN `Dashboard\DashboardWidgets` component is rendered, THE Test_Suite SHALL assert that widget data reflects the current tenant's tickets and metrics.

### Requirement 16: Event and Listener Integration Testing

**User Story:** As a QA engineer, I want to verify that domain events trigger the correct listeners and produce the expected side effects, so that the event-driven architecture is reliable.

#### Acceptance Criteria

1. WHEN a `TicketCreated` event is dispatched, THE Test_Suite SHALL assert that `AssignSlaPolicy`, `RunAutomationEngine`, and `SendTicketCreatedMail` listeners are invoked.
2. WHEN `AssignSlaPolicy` listener handles `TicketCreated`, THE Test_Suite SHALL assert that `SlaService::assignPolicy` is called with the new ticket.
3. WHEN `RunAutomationEngine` listener handles `TicketCreated`, THE Test_Suite SHALL assert that `AutomationEngine::process` is called with trigger event `ticket.created`.
4. WHEN `SendTicketCreatedMail` listener handles `TicketCreated`, THE Test_Suite SHALL assert that a `TicketCreatedMail` is queued to the ticket requester's email address.
5. WHEN a `TicketUpdated` event is dispatched, THE Test_Suite SHALL assert that `RunAutomationEngine` is invoked with trigger event `ticket.updated`.
6. WHEN a `CommentAdded` event is dispatched, THE Test_Suite SHALL assert that `RecordFirstResponse` and `SendCommentAddedMail` listeners are invoked.
7. WHEN `RecordFirstResponse` listener handles `CommentAdded` with a non-internal comment, THE Test_Suite SHALL assert that `SlaService::recordFirstResponse` is called.
8. WHEN a `SlaBreached` event is dispatched, THE Test_Suite SHALL assert that `SendSlaBreachedMail` listener sends a `SlaBreachedMail` to the ticket assignee or team.
9. THE Test_Suite SHALL assert that all event-listener bindings declared in `EventServiceProvider` are registered and resolvable from the service container.

### Requirement 17: Security Assessment  Injection and Input Validation

**User Story:** As a QA engineer, I want to verify that all user inputs are validated and sanitised, so that injection attacks are prevented.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that all HTTP controller methods use Laravel's `$request->validate()` or `Validator::make()` before accessing request data.
2. WHEN `TicketController::store` is called with a `subject` exceeding 255 characters, THE Test_Suite SHALL assert that a validation error is returned and no record is created.
3. WHEN `TicketController::store` is called with an invalid `priority` value, THE Test_Suite SHALL assert that a validation error is returned.
4. WHEN `AdminController::saveSlaPolicy` is called with a non-integer `response_minutes`, THE Test_Suite SHALL assert that a validation error is returned.
5. WHEN `AdminController::storeUser` is called with a password shorter than 8 characters, THE Test_Suite SHALL assert that a validation error is returned.
6. THE Test_Suite SHALL assert that the `TicketListComponent` search query uses parameterised Eloquent `where` clauses and does not concatenate raw user input into SQL.
7. WHEN `BrandingSettings` receives a `theme_primary` value that is not a valid hex colour, THE Test_Suite SHALL assert that a validation error is returned.
8. WHEN `AdminController::provisionTenant` is called with a subdomain containing uppercase letters or special characters, THE Test_Suite SHALL assert that the subdomain is slugified or a validation error is returned.
9. THE Test_Suite SHALL assert that file upload endpoints validate MIME type and file size before storing attachments.

### Requirement 18: Security Assessment  XSS Prevention

**User Story:** As a QA engineer, I want to verify that user-supplied content is escaped in all views, so that cross-site scripting attacks are prevented.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that all Blade templates use `{{ }}` (escaped output) rather than `{!! !!}` (unescaped output) when rendering user-supplied ticket subjects, descriptions, and comments.
2. WHEN a ticket subject containing `<script>alert(1)</script>` is stored and rendered in the ticket list view, THE Test_Suite SHALL assert that the script tag is HTML-encoded in the response body.
3. WHEN a knowledge article body is rendered in the portal, THE Test_Suite SHALL assert that any HTML in the body is sanitised before output.
4. THE Test_Suite SHALL assert that Livewire component properties bound to user input are not rendered unescaped in any template.
5. WHEN `EmailParser::parse` processes an HTML email body, THE Test_Suite SHALL assert that `strip_tags` is applied and no HTML tags appear in the stored `body` field.

### Requirement 19: Security Assessment  CSRF and Session Security

**User Story:** As a QA engineer, I want to verify CSRF protection and session security, so that cross-site request forgery attacks are prevented.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that all POST, PUT, PATCH, and DELETE routes are protected by the `VerifyCsrfToken` middleware.
2. WHEN a POST request is submitted without a valid CSRF token, THE Test_Suite SHALL assert that the response is HTTP 419.
3. WHEN `AuthController::store` successfully authenticates a user, THE Test_Suite SHALL assert that `$request->session()->regenerate()` is called to prevent session fixation.
4. THE Test_Suite SHALL assert that session cookies are configured with `HttpOnly` and `SameSite` attributes in the application configuration.
5. WHEN a user logs out, THE Test_Suite SHALL assert that the session is invalidated and the session token is regenerated.

### Requirement 20: Security Assessment  Insecure Direct Object Reference (IDOR)

**User Story:** As a QA engineer, I want to verify that resource access is gated by ownership and tenant scope, so that IDOR vulnerabilities are prevented.

#### Acceptance Criteria

1. WHEN an authenticated user requests a ticket by ID that belongs to a different tenant, THE Test_Suite SHALL assert that the response is HTTP 404 (due to `TenantScope`) rather than HTTP 403.
2. WHEN an authenticated agent requests a knowledge article belonging to a different tenant, THE Test_Suite SHALL assert that the response is HTTP 404.
3. WHEN `ChangeApprovalController` processes an approval token, THE Test_Suite SHALL assert that the token is validated against the `ChangeApprover` record and cannot be reused after a decision is recorded.
4. WHEN a portal user requests another user's CSAT survey token, THE Test_Suite SHALL assert that the response is HTTP 403 or HTTP 404.
5. THE Test_Suite SHALL assert that all route model bindings for tenant-scoped models implicitly apply `TenantScope`, preventing cross-tenant ID guessing.

### Requirement 21: Code Integrity  Static Analysis

**User Story:** As a QA engineer, I want to run static analysis on the entire codebase, so that type errors, undefined variables, and dead code are detected before runtime.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that running `./vendor/bin/phpstan analyse --level=6` (or Larastan equivalent) against the `app/` directory produces zero errors.
2. THE Test_Suite SHALL assert that all service class constructors declare typed parameters and return types on all public methods.
3. THE Test_Suite SHALL assert that all Eloquent model `$fillable` arrays do not include `id`, `created_at`, or `updated_at`.
4. THE Test_Suite SHALL assert that all event classes implement a constructor that accepts the relevant model and expose it as a public property.
5. THE Test_Suite SHALL assert that all listener classes implement `handle(Event $event): void` with the correct event type hint.
6. THE Test_Suite SHALL assert that `ConditionEvaluator::evaluate` and `ActionExecutor::execute` have no unreachable `default` branches that silently swallow unknown inputs without logging.
7. THE Test_Suite SHALL assert that no controller method accesses `$request->input()` or `$request->get()` without prior validation.

### Requirement 22: Code Integrity  Coding Standards

**User Story:** As a QA engineer, I want to enforce coding standards across the codebase, so that the code is consistent and maintainable.

#### Acceptance Criteria

1. THE Test_Suite SHALL assert that running `./vendor/bin/pint --test` against the `app/` directory produces zero formatting violations.
2. THE Test_Suite SHALL assert that all PHP files in `app/` declare `strict_types=1`.
3. THE Test_Suite SHALL assert that no `var_dump`, `dd`, `dump`, or `print_r` calls exist in any production code file under `app/`.
4. THE Test_Suite SHALL assert that all Livewire component classes extend `Livewire\Component` and are located in the `App\Livewire` namespace.
5. THE Test_Suite SHALL assert that all service classes are registered in `AppServiceProvider` or resolved via Laravel's automatic binding, with no manual `new` instantiation in controllers.
6. THE Test_Suite SHALL assert that all database queries in Livewire components use Eloquent query builder methods and not raw SQL strings.

### Requirement 23: Asset Management  Functional Testing

**User Story:** As a QA engineer, I want to verify asset management operations, so that IT assets are tracked and linked to tickets correctly.

#### Acceptance Criteria

1. WHEN `AssetService` creates an asset, THE Test_Suite SHALL assert that the asset record is persisted with the correct `tenant_id`.
2. WHEN `AssetImporter` processes a valid CSV file, THE Test_Suite SHALL assert that all rows are imported as `Asset` records and an `ImportResult` with the correct success count is returned.
3. WHEN `AssetImporter` processes a CSV file with invalid rows, THE Test_Suite SHALL assert that valid rows are imported, invalid rows are skipped, and the `ImportResult` contains the error count.
4. WHEN an asset is linked to a ticket, THE Test_Suite SHALL assert that the `asset_ticket` pivot record is created.
5. WHEN `AssetList` Livewire component is rendered, THE Test_Suite SHALL assert that assets are filterable by name, type, and status.

### Requirement 24: Reports  Functional Testing

**User Story:** As a QA engineer, I want to verify the report builder and exporter, so that management reports are accurate and exportable.

#### Acceptance Criteria

1. WHEN `ReportBuilder` generates a ticket volume report, THE Test_Suite SHALL assert that the result contains the correct count of tickets grouped by the specified dimension (status, priority, or type).
2. WHEN `ReportBuilder` generates a report with a date range filter, THE Test_Suite SHALL assert that only tickets created within the range are included.
3. WHEN `ReportExporter` exports a report to CSV, THE Test_Suite SHALL assert that the output is a valid CSV string with a header row and one data row per result.
4. WHEN `ReportExporter` exports a report to PDF, THE Test_Suite SHALL assert that the output is a non-empty binary string beginning with the PDF magic bytes `%PDF`.
5. THE Test_Suite SHALL assert that all report queries are scoped to the current tenant.

### Requirement 25: Portal and CSAT  Functional Testing

**User Story:** As a QA engineer, I want to verify portal features and CSAT survey delivery, so that end-user interactions are handled correctly.

#### Acceptance Criteria

1. WHEN `PortalController` renders the portal dashboard, THE Test_Suite SHALL assert that only the authenticated user's tickets are displayed.
2. WHEN a guest accesses a ticket via a guest token URL, THE Test_Suite SHALL assert that the ticket details are visible without authentication.
3. WHEN `CsatService` sends a CSAT survey, THE Test_Suite SHALL assert that a `CsatSurvey` record is created and a `CsatSurveyMail` is queued to the requester.
4. WHEN a CSAT survey response is submitted, THE Test_Suite SHALL assert that the `CsatSurvey` record is updated with the rating and the token is invalidated.
5. WHEN `ServiceCatalogueItemController` lists catalogue items, THE Test_Suite SHALL assert that only active items scoped to the current tenant are returned.

### Requirement 26: Performance and Regression Testing

**User Story:** As a QA engineer, I want to define performance baselines and regression checks, so that performance regressions are detected before release.

#### Acceptance Criteria

1. WHEN the ticket list endpoint is requested with 1,000 tickets in the database, THE Test_Suite SHALL assert that the response time is under 500ms (measured via Laravel's `withoutExceptionHandling` and response timing).
2. WHEN `BusinessHoursCalculator::addBusinessMinutes` is called with 10,000 minutes against a standard 5-day schedule, THE Test_Suite SHALL assert that the computation completes in under 100ms.
3. WHEN `AutomationEngine::process` is called with 50 active automations, THE Test_Suite SHALL assert that all automations are evaluated and the total execution time is under 1,000ms.
4. WHEN `ReportBuilder` generates a report over 10,000 tickets, THE Test_Suite SHALL assert that the query executes without a memory limit error and returns within 2,000ms.
5. THE Test_Suite SHALL assert that the `TicketListComponent` query uses eager loading for `requester`, `assignee`, and `team` relationships to prevent N+1 queries.
6. THE Test_Suite SHALL assert that all database queries in the ticket list path use indexed columns (`status`, `priority`, `assignee_id`, `team_id`, `created_at`) for filtering and sorting.
7. WHEN the test suite is run against a known-good baseline commit, THE Test_Suite SHALL assert that all previously passing tests continue to pass (regression gate).

### Requirement 27: Installer  Functional Testing

**User Story:** As a QA engineer, I want to verify the installer flow, so that fresh deployments complete successfully.

#### Acceptance Criteria

1. WHEN `InstallerController` renders the installer index before installation, THE Test_Suite SHALL assert that the environment checker results are displayed.
2. WHEN `InstallerController` processes the database installation step with valid credentials, THE Test_Suite SHALL assert that migrations are run and the `install.lock` file is created.
3. WHEN `InstallerController` processes the database installation step with invalid credentials, THE Test_Suite SHALL assert that an error is returned and no migrations are run.
4. WHEN the application is already installed and a request is made to the installer route, THE Test_Suite SHALL assert that the response redirects to the home page.
5. WHEN `PollInboxCommand` is executed, THE Test_Suite SHALL assert that it calls the email polling service and processes any new messages.
6. WHEN `IngestEmailCommand` is executed with piped email input, THE Test_Suite SHALL assert that `EmailParser::parse` and `EmailToTicketAction::execute` are called.

### Requirement 28: AI Assist Service  Functional Testing

**User Story:** As a QA engineer, I want to verify the AI assist service integration, so that AI-generated suggestions are handled gracefully.

#### Acceptance Criteria

1. WHEN `AiAssistService` is called with a ticket subject and description, THE Test_Suite SHALL assert that it returns a non-empty suggestion string.
2. WHEN the AI provider returns an error response, THE Test_Suite SHALL assert that `AiAssistService` catches the exception and returns a fallback empty string rather than propagating the error.
3. WHEN `AiAssistService` is called, THE Test_Suite SHALL assert that the request payload does not include sensitive fields such as passwords or API keys from the application configuration.

### Requirement 29: Correctness Properties  Cross-Cutting Invariants

**User Story:** As a QA engineer, I want to define cross-cutting correctness properties that hold across the entire system, so that fundamental invariants are continuously verified.

#### Acceptance Criteria

1. FOR ALL tickets in the database, THE Test_Suite SHALL assert that `status` is one of `['open', 'in_progress', 'pending', 'resolved', 'closed', 'pending_approval', 'approved', 'rejected', 'scheduled']` (domain invariant).
2. FOR ALL tickets in the database, THE Test_Suite SHALL assert that `priority` is one of `['low', 'medium', 'high', 'critical', 'urgent']` (domain invariant).
3. FOR ALL tickets in the database, THE Test_Suite SHALL assert that `ulid` is non-null, non-empty, and unique (uniqueness invariant).
4. FOR ALL `SlaTimer` records, THE Test_Suite SHALL assert that `stopped_at >= due_at` is never true when `breached = false` (SLA timer consistency invariant).
5. FOR ALL `SlaPause` records, THE Test_Suite SHALL assert that `resumed_at >= paused_at` when `resumed_at` is not null (pause duration non-negativity invariant).
6. FOR ALL `KnowledgeArticle` records, THE Test_Suite SHALL assert that `slug` is unique within the tenant and matches the pattern `^[a-z0-9-]+$` (slug format invariant).
7. FOR ALL `AutomationLog` records, THE Test_Suite SHALL assert that `result` is either `success` or begins with `error:` (log result format invariant).
8. FOR ALL `Tenant` records, THE Test_Suite SHALL assert that `subdomain` matches the pattern `^[a-z0-9-]+$` and is unique (subdomain format invariant).
9. THE Test_Suite SHALL assert that creating a ticket, then reading it back by ID, returns a record with identical `subject`, `priority`, `type`, and `requester_id` (create-read round-trip property).
10. THE Test_Suite SHALL assert that updating a ticket's `priority` and then reading it back returns the updated value (update-read round-trip property).
11. FOR ALL `ChangeApprover` records linked to a ticket, THE Test_Suite SHALL assert that `Ticket::isFullyApproved` is equivalent to `changeApprovers.all(decision == 'approved')` (model method consistency property).
12. THE Test_Suite SHALL assert that soft-deleting a ticket removes it from standard queries but it remains retrievable via `withTrashed()` (soft-delete round-trip property).

### Requirement 30: QA Report Generation

**User Story:** As a QA engineer, I want the test suite to produce a structured QA report, so that stakeholders can review test results, coverage metrics, and identified issues.

#### Acceptance Criteria

1. WHEN the Test_Suite is executed, THE Test_Suite SHALL produce a JUnit-compatible XML report at `storage/test-reports/junit.xml`.
2. WHEN the Test_Suite is executed with coverage enabled, THE Test_Suite SHALL produce an HTML coverage report at `storage/test-reports/coverage/` with line and branch coverage metrics.
3. THE Test_Suite SHALL assert that overall line coverage of the `app/` directory is at least 70%.
4. THE Test_Suite SHALL assert that line coverage of `app/Services/` is at least 80%.
5. THE Test_Suite SHALL assert that line coverage of `app/Actions/` is at least 90%.
6. WHEN the Test_Suite completes, THE Test_Suite SHALL output a summary listing: total tests, passed, failed, skipped, and execution time.
7. WHEN any test fails, THE Test_Suite SHALL include the failing test name, the assertion that failed, and the actual vs. expected values in the report.
8. THE Test_Suite SHALL assert that the static analysis report (`phpstan`) and linting report (`pint`) are included as separate sections in the QA report.

