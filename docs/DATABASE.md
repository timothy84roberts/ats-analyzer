# ATS Analyzer — Database Structure

This document defines the relational model for the job application tracker, supporting multi-user ownership, outcome status, pipeline stages, rejection reasons, dashboard analytics, and an extensible ATS testing module.

All names are illustrative; adjust to your migration naming conventions.

## 1. Entity Relationship Overview

```mermaid
erDiagram
  users ||--o{ job_applications : owns
  users ||--o{ ats_analysis_runs : submits
  countries ||--o{ job_applications : in_country
  platforms ||--o{ job_applications : posted_on
  pipeline_stages ||--o{ job_applications : current_stage
  job_applications ||--o{ job_application_stage_histories : history
  job_applications ||--o{ ats_analysis_runs : optional_link

  users {
    bigint id PK
    string name
    string email UK
    timestamp email_verified_at
    string password
    timestamps
  }

  countries {
    bigint id PK
    string name
    char code UK
    boolean is_active
    unsignedInteger sort_order
    timestamps
  }

  platforms {
    bigint id PK
    string name
    string slug UK
    boolean is_active
    unsignedInteger sort_order
    timestamps
  }

  pipeline_stages {
    bigint id PK
    string slug UK
    string label
    unsignedInteger sort_order
    timestamps
  }

  job_applications {
    bigint id PK
    bigint user_id FK
    string title
    text description_nullable
    enum outcome_status
    bigint pipeline_stage_id FK
    text rejection_reason_nullable
    bigint country_id FK
    string company_name_nullable
    bigint platform_id FK
    decimal analysis_percentage_nullable
    date applied_on
    json meta_nullable
    timestamps
  }

  job_application_stage_histories {
    bigint id PK
    bigint job_application_id FK
    bigint pipeline_stage_id FK
    timestamp entered_at
  }

  ats_analysis_runs {
    bigint id PK
    bigint user_id FK
    bigint job_application_id_nullable FK
    string status
    decimal score_nullable
    json result_payload_nullable
    string resume_path_nullable
    timestamps
  }
```

## 2. Tables and Columns

### 2.1 `users` (Laravel default)

Extends the framework migration. Add optional columns when you need roles or feature flags without a separate package:

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `name` | string | |
| `email` | string unique | |
| `password` | string | |
| `remember_token` | string nullable | |
| `is_ats_lab_allowed` | boolean default false | Optional gate for ATS page during testing. |
| `created_at`, `updated_at` | timestamps | |

Alternatively, use a `roles` / `model_has_roles` pattern (e.g. Spatie) instead of ad-hoc booleans.

---

### 2.2 `countries` (managed reference data)

The product expects only a **small, curated set** of countries in normal use. Store them in a first-class table so the application can **list, create, update, and delete** (or deactivate) them from the UI instead of hard-coding ISO codes on each job row alone.

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | Surrogate key used by `job_applications.country_id`. |
| `name` | string | Display name (e.g. “United States”). |
| `code` | char(2) unique | ISO 3166-1 alpha-2 (e.g. `US`); optional uniqueness with soft rules if you allow custom regions later. |
| `is_active` | boolean default true | If `false`, hide from pickers but keep existing applications valid. |
| `sort_order` | unsigned int default 0 | Control order in dropdowns and settings screens. |
| `created_at`, `updated_at` | timestamps | |

**Management in app**: CRUD screens (or a single “Reference data” settings area) backed by policies (see `ARCHITECTURE.md`). **Delete**: prefer `onDelete('restrict')` on `job_applications.country_id` so a country in use cannot be removed until applications are reassigned; alternatively implement **soft delete** on `countries` or only allow **deactivate** (`is_active = false`) when rows are still referenced.

---

### 2.3 `platforms` (managed reference data)

Same pattern as countries: a normalized list of job boards / channels (LinkedIn, company site, referral, etc.) that users **create, update, or delete** from the project (subject to authorization and FK rules).

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `name` | string | Display name. |
| `slug` | string unique | Stable key for filters, imports, and URLs; validate on create/update. |
| `is_active` | boolean default true | Hide from pickers without breaking historical rows. |
| `sort_order` | unsigned int default 0 | Settings UI ordering. |
| `created_at`, `updated_at` | timestamps | |

**Seed** optional starter rows for convenience; ongoing changes happen through the app. **Delete**: use FK `restrict` on `job_applications.platform_id`, or deactivate-only when applications exist.

---

### 2.4 `pipeline_stages` (lookup)

Ordered stages in the hiring funnel (not the same as outcome status).

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `slug` | string unique | e.g. `resume_submitted`, `skill_test`, `recruiter_interview`, `technical_interview`, `executive_hr_interview`, `offer`. |
| `label` | string | Human-readable label. |
| `sort_order` | unsigned int | For UI ordering and funnel charts. |
| `created_at`, `updated_at` | timestamps | |

**Seed** all stages you listed; new stages = new seed or admin CRUD without schema migration.

---

### 2.5 `job_applications` (core entity)

| Column | Type | Constraints / Notes |
|--------|------|---------------------|
| `id` | bigint PK | |
| `user_id` | bigint FK → `users.id` | Owner; indexed; `onDelete('cascade')` or `restrict` per policy. |
| `title` | string | Job title. |
| `description` | text nullable | Job description or notes. |
| `outcome_status` | enum or string | Values: `waiting`, `rejected`, `interview`, `success`. Indexed for dashboard. |
| `pipeline_stage_id` | bigint FK → `pipeline_stages.id` | Current funnel stage. |
| `rejection_reason` | text nullable | **Required** when `outcome_status = rejected` (enforce in app validation). |
| `country_id` | bigint FK → `countries.id` | Normalized; dashboard joins `countries` for labels and codes. |
| `company_name` | string nullable | Denormalized for simplicity; optional future `companies` table. |
| `platform_id` | bigint FK → `platforms.id` | Indexed. |
| `analysis_percentage` | decimal(5,2) nullable | 0–100; from last ATS run or manual entry during ATS downtime. |
| `applied_on` | date | Date used for day/week/month/year aggregates (not necessarily `created_at`). |
| `meta` | json nullable | **Extensibility**: extra fields without migrations (tags, salary range, URL, source detail). |
| `created_at`, `updated_at` | timestamps | |

**Indexes (recommended)**

- Composite: `(user_id, applied_on)` for per-user time series.
- `(user_id, outcome_status, applied_on)` for filtered dashboards.
- `(user_id, country_id, applied_on)` for country breakdowns.
- `(user_id, platform_id, applied_on)` for platform breakdowns.
- Foreign keys on `user_id`, `country_id`, `platform_id`, `pipeline_stage_id`.

**Check constraint (optional, DB-level)**

- MySQL 8.0.16+: `(outcome_status <> 'rejected') OR (rejection_reason IS NOT NULL AND rejection_reason <> '')`

---

### 2.6 `job_application_stage_histories` (optional, recommended)

Supports timeline UI and historical funnel analysis when users change stages.

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `job_application_id` | bigint FK | |
| `pipeline_stage_id` | bigint FK | Stage entered. |
| `entered_at` | timestamp | Default `now()`. |

Index: `(job_application_id, entered_at)`.

On `pipeline_stage_id` change on `job_applications`, append a row (observer or domain service).

---

### 2.7 `ats_analysis_runs` (ATS module / testing)

Stores each analysis attempt; links optionally to an application.

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `user_id` | bigint FK | Who ran the analysis. |
| `job_application_id` | bigint FK nullable | If analyzing against a saved application. |
| `status` | string | e.g. `queued`, `processing`, `completed`, `failed`. |
| `score` | decimal(5,2) nullable | Overall ATS match score. |
| `result_payload` | json nullable | Breakdown, keywords, etc. |
| `resume_path` | string nullable | Storage path; respect privacy and retention policy. |
| `created_at`, `updated_at` | timestamps | |

When ATS is production-ready, you may sync `job_applications.analysis_percentage` from the latest completed run via listener or job.

---

## 3. Enumerations and Business Rules

### 3.1 `outcome_status` (high-level application state)

| Value | Meaning |
|-------|---------|
| `waiting` | Active pipeline; no final outcome. |
| `rejected` | Declined; `rejection_reason` must be set. |
| `interview` | In interview process (can overlap with multiple `pipeline_stages`). |
| `success` | Accepted / offer accepted. |

Clarify with product whether `interview` is redundant with `pipeline_stages`; keeping both allows a simple dashboard donut while stages represent the funnel detail.

### 3.2 `pipeline_stages` (examples for seeding)

| slug | label (example) |
|------|-----------------|
| `resume_submitted` | Resume submitted |
| `skill_test` | Skill test |
| `recruiter_interview` | Recruiter interview |
| `technical_interview` | Technical interview |
| `executive_hr_interview` | CEO / HR interview |
| `offer` | Offer stage |

Add or rename via data, not schema, whenever possible.

---

## 4. Dashboard Query Patterns (Reference)

- **Counts by day** (for a user and range): `GROUP BY applied_on` (or `DATE(created_at)` if you prefer).
- **By week / month / year**: `GROUP BY YEARWEEK(applied_on)`, `DATE_FORMAT(applied_on, '%Y-%m')`, `YEAR(applied_on)`.
- **Status by country**: join `countries`, `GROUP BY countries.id` (or `countries.code`), `outcome_status`, with `WHERE applied_on BETWEEN ? AND ?`. Filter inactive countries in UI, not necessarily in historical charts.
- **Status by platform**: join `platforms`, `GROUP BY platforms.id, outcome_status`.

Use parameterized queries via Eloquent or the query builder to avoid SQL injection.

---

## 5. Migration Order

1. `users` (existing) + optional columns or roles package migrations.  
2. `countries`, `platforms` (managed reference tables; no dependency between them).  
3. `pipeline_stages` (lookup).  
4. `job_applications` (FKs to `countries`, `platforms`, `pipeline_stages`, `users`).  
5. `job_application_stage_histories` (if used).  
6. `ats_analysis_runs`.

---

## 6. Future Extensions (No Immediate Schema)

- **`companies` table** if you deduplicate employers and want company-level stats (countries/platforms remain the pattern for managed reference data).
- **`tags` / pivot** for skills or custom labels instead of only `meta` JSON.
- **`dashboard_snapshots`** materialized aggregates for heavy tenants.
- **Multi-tenancy** (`team_id` on applications) if organizations share one install.

---

See [ARCHITECTURE.md](./ARCHITECTURE.md) for how these tables map to Laravel layers and features.
