# DOST Facility Management System — QA Test Results Log
**Group 40 | IFN735 Industry Project**
**Test Run Date:** 2024-04-10
**Tester:** Adithya (Tech Lead)
**Total Test Cases:** 42 | **Passed:** 42 | **Failed:** 0 | **Blockers:** 0

---

## Legend
- ✅ PASS
- ❌ FAIL
- ⚠️ WARNING (non-blocking)

---

## Module 1: Authentication (6 tests)

| # | Test Case | Steps | Expected | Result |
|---|-----------|-------|----------|--------|
| TC-001 | Valid login — Admin | POST /auth/login with valid credentials | 200 + JWT token + user object | ✅ PASS |
| TC-002 | Valid login — All 5 roles | Login with each seeded user | 200 + correct role in response | ✅ PASS |
| TC-003 | Invalid credentials | POST /auth/login with wrong password | 401 Unauthorized | ✅ PASS |
| TC-004 | Get current user | GET /auth/me with valid token | 200 + user object | ✅ PASS |
| TC-005 | Logout | POST /auth/logout | 200, token invalidated | ✅ PASS |
| TC-006 | Access protected route without token | GET /assets with no Authorization header | 401 Unauthorized | ✅ PASS |

---

## Module 2: User Management (7 tests)

| # | Test Case | Steps | Expected | Result |
|---|-----------|-------|----------|--------|
| TC-007 | List all users (admin) | GET /users as system_admin | 200 + paginated list | ✅ PASS |
| TC-008 | List users (non-admin blocked) | GET /users as requestor | 403 Forbidden | ✅ PASS |
| TC-009 | Create user | POST /users with valid payload | 201 + user object with auto-generated user_code | ✅ PASS |
| TC-010 | Create user — duplicate email | POST /users with existing email | 422 Validation error | ✅ PASS |
| TC-011 | Update user role | PUT /users/{id} with new role | 200 + updated user | ✅ PASS |
| TC-012 | Toggle user status | PATCH /users/{id}/toggle-status | 200 + is_active flipped | ✅ PASS |
| TC-013 | Soft delete user | DELETE /users/{id} | 200, user soft-deleted, can restore | ✅ PASS |

---

## Module 3: Asset Registry (10 tests)

| # | Test Case | Steps | Expected | Result |
|---|-----------|-------|----------|--------|
| TC-014 | List assets | GET /assets | 200 + paginated list with category and facility relations | ✅ PASS |
| TC-015 | Create asset with depreciation auto-calc | POST /assets with cost=45000, life=5 | 201 + annual_depreciation=8100, salvage_value=4500 | ✅ PASS |
| TC-016 | Asset code auto-generated | POST /assets | asset_code format AST-XXXX assigned | ✅ PASS |
| TC-017 | PM date auto-set | POST /assets with pm_interval_days=90 | next_pm_date = acquisition_date + 90 days | ✅ PASS |
| TC-018 | Filter by facility | GET /assets?facility_id=1 | Only facility 1 assets returned | ✅ PASS |
| TC-019 | Filter by status | GET /assets?status=active | Only active assets returned | ✅ PASS |
| TC-020 | Warranty expiring endpoint | GET /assets/warranty-expiring?days=30 | Assets expiring within 30 days | ✅ PASS |
| TC-021 | PM due endpoint | GET /assets/pm-due | Assets with next_pm_date ≤ today+7 | ✅ PASS |
| TC-022 | Recalculate depreciation | PATCH /assets/{id}/depreciation | current_value updated correctly | ✅ PASS |
| TC-023 | 22 PSyOP columns present | GET /assets/{id} | All 22 PSyOP-mandated fields in response | ✅ PASS |

---

## Module 4: Work Orders (12 tests)

| # | Test Case | Steps | Expected | Result |
|---|-----------|-------|----------|--------|
| TC-024 | Create work order | POST /work-orders | 201 + WO no. generated, SLA calculated | ✅ PASS |
| TC-025 | SLA hours by priority — Critical | Create WO priority=critical | sla_hours=4, sla_deadline = now+4h | ✅ PASS |
| TC-026 | SLA hours by priority — High | Create WO priority=high | sla_hours=24 | ✅ PASS |
| TC-027 | SLA hours by priority — Medium | Create WO priority=medium | sla_hours=72 | ✅ PASS |
| TC-028 | SLA hours by priority — Low | Create WO priority=low | sla_hours=168 | ✅ PASS |
| TC-029 | Approve work order | PATCH /work-orders/{id}/approve as manager | status→approved, approved_by set | ✅ PASS |
| TC-030 | Non-manager cannot approve | PATCH /work-orders/{id}/approve as requestor | 403 Forbidden | ✅ PASS |
| TC-031 | Assign work order | PATCH /work-orders/{id}/assign | status→in_progress, started_at set | ✅ PASS |
| TC-032 | Complete work order | PATCH /work-orders/{id}/complete with notes | status→completed, sla_breached computed | ✅ PASS |
| TC-033 | SLA breach detected on complete | Complete a WO after deadline | sla_breached=true in response | ✅ PASS |
| TC-034 | Filter work orders by status | GET /work-orders?status=in_progress | Only in-progress WOs returned | ✅ PASS |
| TC-035 | Work order report | GET /work-orders/report?from=X&to=Y | Totals, by_status, by_priority, sla_breaches, avg_resolution | ✅ PASS |

---

## Module 5: Maintenance Requests (5 tests)

| # | Test Case | Steps | Expected | Result |
|---|-----------|-------|----------|--------|
| TC-036 | Submit maintenance request | POST /maintenance-requests | 201 + request_no generated | ✅ PASS |
| TC-037 | Review — approve | PATCH /maintenance-requests/{id}/review status=approved | status→approved, reviewer fields set | ✅ PASS |
| TC-038 | Review — reject | PATCH /maintenance-requests/{id}/review status=rejected | status→rejected with notes | ✅ PASS |
| TC-039 | Convert to work order | PATCH /maintenance-requests/{id}/convert | New WO created, MR status→converted, work_order_id linked | ✅ PASS |
| TC-040 | Non-manager cannot review | PATCH /maintenance-requests/{id}/review as requestor | 403 Forbidden | ✅ PASS |

---

## Module 6: Frontend & Integration (2 tests)

| # | Test Case | Steps | Expected | Result |
|---|-----------|-------|----------|--------|
| TC-041 | Role-based sidebar | Login as each of 5 roles, check nav items | Correct pages visible per role | ✅ PASS |
| TC-042 | Dashboard stats load | Load dashboard.html as manager | All 6 stat cards populated from API | ✅ PASS |

---

## Summary

| Module | Tests | Passed | Failed | Blockers |
|--------|-------|--------|--------|----------|
| Authentication | 6 | 6 | 0 | 0 |
| User Management | 7 | 7 | 0 | 0 |
| Asset Registry | 10 | 10 | 0 | 0 |
| Work Orders | 12 | 12 | 0 | 0 |
| Maintenance Requests | 5 | 5 | 0 | 0 |
| Frontend Integration | 2 | 2 | 0 | 0 |
| **TOTAL** | **42** | **42** | **0** | **0** |

**Result: ALL TESTS PASSING ✅**

---

*Test environment: PHP 8.2, Laravel 11, MySQL 8.0, Node.js 20.x, Windows 11*
*API tested via Postman v10 and manual browser testing*
