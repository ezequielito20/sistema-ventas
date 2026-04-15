# UI Refactor Strategy and QA Checklist

## Objective

Refactor the frontend UI progressively to reach a professional SaaS-ready quality level without breaking current business behavior.

This document defines:

- the execution rules,
- the commit strategy,
- risk controls,
- and the QA checklist per module.

## Execution Rules

1. One atomic change per commit.
2. Do not mix UI refactor with business logic changes in the same commit.
3. Keep route contracts, form field names, validation flow, and permissions unchanged unless explicitly approved.
4. Each commit must include:
   - scope,
   - affected files,
   - risk level,
   - validation performed,
   - rollback notes.
5. Move module by module with explicit approval before starting the next step.

## Branch and Commit Workflow

### Suggested branch format

- `refactor/ui-phase-<n>-<topic>`

### Commit message format

- `refactor(ui): <short atomic description>`

Examples:

- `refactor(ui): add design token baseline docs`
- `refactor(ui): extract shared roles form partial`
- `refactor(ui): unify suppliers create/edit css`

## Refactor Phases (High Level)

### Phase 0 - Safety Baseline

- Create strategy and QA documentation.
- Establish smoke checks.
- Confirm module inventory and critical routes.

### Phase 1 - Foundation

- Design tokens.
- Breakpoint policy.
- Shared UI components.
- Unified alerts/confirm wrapper.

## Official Breakpoint Policy

Use only these viewport breakpoints for all new and refactored UI code:

- `xs`: 400px (phone baseline for this project)
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px
- `2xl`: 1536px

Rules:

1. Avoid ad-hoc media queries (`320px`, `387px`, `440px`, etc.) unless strictly required by a hard blocker.
2. Any exception must be documented in the module commit notes with reason and removal plan.
3. Prefer component-level responsive behavior over many page-specific overrides.
4. Keep mobile-first order in CSS/Tailwind usage.

## Visual Foundation Rule (Mandatory)

All new or refactored UI in the system must reuse the same visual primitives:

- Buttons: `ui-btn` + variant (`ui-btn-primary`, `ui-btn-success`, `ui-btn-warning`, `ui-btn-danger`, `ui-btn-ghost`)
- Panels/containers: `ui-panel`, `ui-panel__header`, `ui-panel__body`
- KPI widgets: `ui-widget`, `ui-widget__label`, `ui-widget__value`, `ui-widget__meta`
- Tables: `ui-table-wrap`, `ui-table`
- Badges/status: `ui-badge` + variant (`ui-badge-success`, `ui-badge-warning`, `ui-badge-danger`)
- Pagination: `ui-pagination`, `ui-page-link`, `ui-page-link is-active`

Reference preview routes:

- `admin.ui.notifications.preview`
- `admin.ui.design-system.preview`

### Phase 2 - Simple CRUD modules

- roles, permissions, categories, suppliers, companies.

### Phase 3 - Mid complexity CRUD modules

- users, products, purchases.

### Phase 4 - Complex modules

- orders, dashboard, customers, sales, cash-counts.

### Phase 5 - Final hardening

- accessibility, responsive matrix, cleanup, final documentation.

## Risk Levels

- **Low:** visual-only extraction using existing markup contracts.
- **Medium:** layout component replacement with the same backend data and actions.
- **High:** splitting large views/scripts into partials/modules where interaction complexity is high.

## Global Regression Checklist (Run in every commit)

1. Authentication/login/logout.
2. Main layout navigation and sidebar behavior.
3. Flash messages and confirmations.
4. CRUD base flow in modified module:
   - index load,
   - create submit,
   - edit submit,
   - delete action.
5. Role/permission visibility of actions.
6. Browser console free of new critical JS errors.
7. Responsive sanity check:
   - 320px,
   - 768px,
   - 1024px,
   - 1366px.

## Module QA Checklists

Use this section after each module-focused commit.

### Dashboard

- Widgets render correctly with no broken layout.
- Summary cards maintain alignment across breakpoints.
- Section headers remain visible and actions still work.

### Users

- Index table loads with filters and pagination.
- Create user works and validations display correctly.
- Edit user works and preserves existing values.
- Delete/disable actions keep confirmation behavior.
- Report/export actions (if available) still respond.

### Roles

- Index list and pagination work.
- Create role and permission assignment works.
- Edit role and permission assignment works.
- Delete role confirmation and backend response are correct.

### Permissions

- Index list and filters work.
- Create/edit permission works.
- Delete permission flow works.
- Report view/button (if present) remains functional.

### Categories

- Index, filters, and pagination work.
- Create/edit category works.
- Delete category works.
- Any report or print action still works.

### Suppliers

- Index and search/filter behavior works.
- Create/edit supplier form works.
- Delete supplier flow works.
- Any related table action remains stable.

### Companies

- Company create/edit forms render and submit correctly.
- Existing company data prefill works in edit.
- Validation messages remain visible and understandable.

### Products

- Index table and filters work.
- Create product and edit product preserve image/stock/category behavior.
- Delete product confirmation flow works.
- Report/export actions still work.

### Purchases

- Purchases list and partial list rendering work.
- Create purchase with details lines works.
- Edit purchase updates line items correctly.
- Delete/cancel flow works as expected.

### Customers

- Index loads including filters and pagination.
- Debt/payment history modals still open and operate correctly.
- Create/edit customer flow works.
- Report and detail views still work.

### Sales

- Sales list and filters work.
- Create sale line-item interaction works.
- Edit sale with previous data works.
- Print/report actions still work.

### Cash Counts

- Index loads heavy blocks without visual break.
- Create/edit cash count works.
- Movement-related actions still work.
- Table totals and summary cards are consistent.

### Orders

- Index and show pages render correctly.
- Status/action buttons still work.
- Layout consistency updates do not break data rendering.

## Component Standardization Targets

During refactor, prioritize replacing repeated patterns with shared components:

1. CRUD toolbar actions.
2. Table shell with pagination section.
3. Shared form shell for create/edit pages.
4. Unified delete confirmation modal.
5. Shared alert/toast helper in JS.

## Rollback Strategy

If a commit introduces regression:

1. Stop moving to next module.
2. Reproduce issue with minimal steps.
3. Patch immediately in a follow-up atomic commit.
4. Re-run global regression checklist before continuing.

## Done Criteria for This Refactor Program

1. No critical regressions in CRUD core flows.
2. Reduced UI duplication in create/edit views and CSS.
3. Shared component adoption in all major modules.
4. Consistent responsive behavior across target breakpoints.
5. Stable visual system based on shared tokens and conventions.
