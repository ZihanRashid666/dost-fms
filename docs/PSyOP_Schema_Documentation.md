# PSyOP Rev 1.1 — Database Schema Documentation
**DOST Facility Management System | Group 40**

---

## Overview
This system implements the PSyOP (Philippine System for Official Operations Protocol) Rev 1.1 naming conventions across all database tables and columns.

## Naming Conventions
- Table prefixes indicate module: `sys_` (system), `fac_` (facilities), `ast_` (assets), `wko_` (work orders), `mnt_` (maintenance), `aud_` (audit)
- Primary keys follow pattern: `{prefix}_id` (e.g., `user_id`, `asset_id`)
- Foreign keys reference the primary key of the related table
- Soft deletes used on critical tables (`deleted_at`)

---

## Tables

### sys_users
| Column | Type | Description |
|--------|------|-------------|
| user_id | BIGINT PK | Auto-increment primary key |
| user_code | VARCHAR(20) UNIQUE | PSyOP user code (USR-XXXX) |
| full_name | VARCHAR(100) | Full legal name |
| email | VARCHAR(150) UNIQUE | Login email |
| password_hash | VARCHAR(255) | Bcrypt hash |
| role | ENUM | system_admin / facility_manager / maintenance_staff / requestor / viewer |
| department | VARCHAR(100) | Department assignment |
| contact_no | VARCHAR(20) | Contact number |
| is_active | BOOLEAN | Account status |
| last_login_at | TIMESTAMP | Last successful login |
| created_at | TIMESTAMP | Record creation |
| updated_at | TIMESTAMP | Last update |
| deleted_at | TIMESTAMP | Soft delete |

### ast_assets (22 PSyOP columns)
| Column | Type | PSyOP Field | Description |
|--------|------|-------------|-------------|
| asset_id | BIGINT PK | — | Primary key |
| asset_code | VARCHAR(30) | asset_code | Auto-generated code |
| asset_name | VARCHAR(150) | asset_name | Asset description |
| serial_number | VARCHAR(100) | serial_number | Manufacturer serial |
| model | VARCHAR(100) | model | Model number |
| brand | VARCHAR(100) | brand | Manufacturer brand |
| category_id | FK | — | Asset category |
| facility_id | FK | — | Host facility |
| status | ENUM | — | active/inactive/under_repair/disposed/condemned |
| condition | ENUM | — | excellent/good/fair/poor |
| acquisition_date | DATE | acquisition_date | Purchase date |
| acquisition_cost | DECIMAL(15,2) | acquisition_cost | Purchase price |
| current_value | DECIMAL(15,2) | current_value | Depreciated book value |
| salvage_value | DECIMAL(15,2) | salvage_value | End-of-life residual value |
| useful_life_years | INT | useful_life_years | Expected lifespan |
| annual_depreciation | DECIMAL(15,2) | annual_depreciation | Straight-line annual amount |
| warranty_expiry_date | DATE | warranty_expiry_date | Warranty end date |
| warranty_provider | VARCHAR(150) | warranty_provider | Warranty company |
| warranty_contact | VARCHAR(100) | warranty_contact | Warranty contact info |
| last_pm_date | DATE | last_pm_date | Last preventive maintenance |
| next_pm_date | DATE | next_pm_date | Scheduled next PM |
| pm_interval_days | INT | pm_interval_days | PM frequency in days |

### wko_work_orders
Includes SLA computation fields:
- `sla_hours`: Target resolution time by priority (Critical=4h, High=24h, Medium=72h, Low=168h)
- `sla_deadline`: Computed as `requested_at + sla_hours`
- `sla_breached`: Boolean, set on completion if `completed_at > sla_deadline`

---

## Depreciation Formula
Straight-line depreciation per PSyOP Rev 1.1:
```
salvage_value      = acquisition_cost × 0.10
annual_depreciation = (acquisition_cost - salvage_value) / useful_life_years
current_value      = acquisition_cost - (annual_depreciation × years_elapsed)
                     (floored at salvage_value)
```

## SLA Matrix
| Priority | SLA Target |
|----------|-----------|
| Critical | 4 hours |
| High | 24 hours |
| Medium | 72 hours |
| Low | 168 hours (7 days) |
