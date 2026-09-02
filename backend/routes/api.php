<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\MaintenanceRequestController;

/*
|--------------------------------------------------------------------------
| DOST FMS API Routes — Laravel 11
| Total: 42 RESTful endpoints
|--------------------------------------------------------------------------
*/

// ─── Auth Routes (4 endpoints) ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',   [AuthController::class, 'login']);   // POST /api/auth/login
    Route::post('logout',  [AuthController::class, 'logout'])->middleware('auth:api');  // POST /api/auth/logout
    Route::get('me',       [AuthController::class, 'me'])->middleware('auth:api');      // GET  /api/auth/me
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api'); // POST /api/auth/refresh
});

// ─── Protected Routes ──────────────────────────────────────────────────────
Route::middleware(['auth:api'])->group(function () {

    // Users (7 endpoints) — system_admin only
    Route::get('users/me', [UserController::class, 'me']);
    Route::middleware(['role:system_admin'])->group(function () {
        Route::get('users',                        [UserController::class, 'index']);
        Route::post('users',                       [UserController::class, 'store']);
        Route::get('users/{id}',                   [UserController::class, 'show']);
        Route::put('users/{id}',                   [UserController::class, 'update']);
        Route::delete('users/{id}',                [UserController::class, 'destroy']);
        Route::patch('users/{id}/toggle-status',   [UserController::class, 'toggleStatus']);
    });

    // Assets (10 endpoints)
    Route::get('assets/warranty-expiring', [AssetController::class, 'warrantyExpiring']);
    Route::get('assets/pm-due', [AssetController::class, 'pmDue']);
    Route::get('assets/categories', fn() => response()->json(\App\Models\AstCategory::all()));
    Route::post('assets/categories', fn(\Illuminate\Http\Request $r) => response()->json(\App\Models\AstCategory::create($r->all()), 201));
    Route::middleware(['role:system_admin,facility_manager,maintenance_staff'])->group(function () {
        Route::post('assets',                               [AssetController::class, 'store']);
        Route::put('assets/{id}',                          [AssetController::class, 'update']);
        Route::delete('assets/{id}',                       [AssetController::class, 'destroy']);
        Route::patch('assets/{id}/depreciation',           [AssetController::class, 'recalculateDepreciation']);
    });
    Route::get('assets',                                   [AssetController::class, 'index']);
    Route::get('assets/{id}',                              [AssetController::class, 'show']);

    // Work Orders (12 endpoints)
    Route::get('work-orders',                              [WorkOrderController::class, 'index']);
    Route::post('work-orders',                             [WorkOrderController::class, 'store']);
    Route::get('work-orders/report',                       [WorkOrderController::class, 'report']);
    Route::get('work-orders/{id}',                         [WorkOrderController::class, 'show']);
    Route::put('work-orders/{id}',                         [WorkOrderController::class, 'update']);
    Route::delete('work-orders/{id}',                      [WorkOrderController::class, 'destroy']);
    Route::middleware(['role:system_admin,facility_manager'])->group(function () {
        Route::patch('work-orders/{id}/approve',           [WorkOrderController::class, 'approve']);
        Route::patch('work-orders/{id}/reject',            fn($id) => response()->json(tap(\App\Models\WkoWorkOrder::findOrFail($id))->update(['status'=>'rejected'])));
        Route::patch('work-orders/{id}/assign',            [WorkOrderController::class, 'assign']);
    });
    Route::middleware(['role:system_admin,facility_manager,maintenance_staff'])->group(function () {
        Route::patch('work-orders/{id}/start',             fn($id) => response()->json(tap(\App\Models\WkoWorkOrder::findOrFail($id))->update(['status'=>'in_progress','started_at'=>now()])));
        Route::patch('work-orders/{id}/complete',          [WorkOrderController::class, 'complete']);
        Route::patch('work-orders/{id}/cancel',            fn($id) => response()->json(tap(\App\Models\WkoWorkOrder::findOrFail($id))->update(['status'=>'cancelled'])));
    });

    // Maintenance Requests (8 endpoints)
    Route::get('maintenance-requests',                     [MaintenanceRequestController::class, 'index']);
    Route::post('maintenance-requests',                    [MaintenanceRequestController::class, 'store']);
    Route::get('maintenance-requests/{id}',                [MaintenanceRequestController::class, 'show']);
    Route::patch('maintenance-requests/{id}/review',       [MaintenanceRequestController::class, 'review']);
    Route::patch('maintenance-requests/{id}/convert',      [MaintenanceRequestController::class, 'convertToWorkOrder']);

    // Facilities (5 endpoints)
    Route::get('facilities',     fn() => response()->json(\App\Models\FacFacility::with('manager')->paginate(15)));
    Route::post('facilities',    fn(\Illuminate\Http\Request $r) => response()->json(\App\Models\FacFacility::create($r->all()), 201));
    Route::get('facilities/{id}',fn($id) => response()->json(\App\Models\FacFacility::with('manager','assets')->findOrFail($id)));
    Route::put('facilities/{id}',fn(\Illuminate\Http\Request $r, $id) => response()->json(tap(\App\Models\FacFacility::findOrFail($id))->update($r->all())));
    Route::delete('facilities/{id}',fn($id) => response()->json(tap(\App\Models\FacFacility::findOrFail($id))->delete()));

    // Dashboard Stats (2 endpoints)
    Route::get('dashboard/stats', function () {
        return response()->json([
            'total_assets'      => \App\Models\AstAsset::count(),
            'active_assets'     => \App\Models\AstAsset::where('status','active')->count(),
            'open_work_orders'  => \App\Models\WkoWorkOrder::whereIn('status',['submitted','approved','in_progress'])->count(),
            'pending_requests'  => \App\Models\MntMaintenanceRequest::where('status','pending')->count(),
            'sla_breaches'      => \App\Models\WkoWorkOrder::where('sla_breached',true)->count(),
            'pm_due_this_week'  => \App\Models\AstAsset::whereNotNull('next_pm_date')->where('next_pm_date','<=',\Carbon\Carbon::now()->addDays(7))->count(),
        ]);
    });
    Route::get('dashboard/recent-work-orders', fn() => response()->json(
        \App\Models\WkoWorkOrder::with(['requestedBy','facility'])->orderByDesc('requested_at')->limit(5)->get()
    ));
});
