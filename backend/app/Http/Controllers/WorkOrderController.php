<?php

namespace App\Http\Controllers;

use App\Models\WkoWorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class WorkOrderController extends Controller
{
    // SLA hours by priority (PSyOP Rev 1.1)
    const SLA_HOURS = [
        'critical' => 4,
        'high'     => 24,
        'medium'   => 72,
        'low'      => 168,
    ];

    /** GET /api/work-orders */
    public function index(Request $request)
    {
        $query = WkoWorkOrder::with(['asset', 'facility', 'requestedBy', 'assignedTo', 'approvedBy']);
        if ($request->has('status'))      $query->where('status', $request->status);
        if ($request->has('priority'))    $query->where('priority', $request->priority);
        if ($request->has('type'))        $query->where('type', $request->type);
        if ($request->has('facility_id')) $query->where('facility_id', $request->facility_id);
        if ($request->has('assigned_to')) $query->where('assigned_to', $request->assigned_to);
        return response()->json($query->orderByDesc('requested_at')->paginate(15));
    }

    /** POST /api/work-orders */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title'       => 'required|string|max:200',
            'description' => 'required|string',
            'type'        => 'required|in:corrective,preventive,emergency,inspection',
            'priority'    => 'required|in:low,medium,high,critical',
            'facility_id' => 'required|exists:fac_facilities,facility_id',
            'asset_id'    => 'nullable|exists:ast_assets,asset_id',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $user  = JWTAuth::parseToken()->authenticate();
        $count = WkoWorkOrder::count() + 1;
        $now   = Carbon::now();
        $slaHours = self::SLA_HOURS[$request->priority];

        $wo = WkoWorkOrder::create([
            'work_order_no' => 'WO-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'title'         => $request->title,
            'description'   => $request->description,
            'type'          => $request->type,
            'priority'      => $request->priority,
            'status'        => 'draft',
            'facility_id'   => $request->facility_id,
            'asset_id'      => $request->asset_id,
            'requested_by'  => $user->user_id,
            'requested_at'  => $now,
            'sla_hours'     => $slaHours,
            'sla_deadline'  => $now->copy()->addHours($slaHours),
        ]);

        return response()->json($wo->load(['facility', 'requestedBy']), 201);
    }

    /** GET /api/work-orders/{id} */
    public function show($id)
    {
        return response()->json(WkoWorkOrder::with(['asset','facility','requestedBy','assignedTo','approvedBy'])->findOrFail($id));
    }

    /** PUT /api/work-orders/{id} */
    public function update(Request $request, $id)
    {
        $wo = WkoWorkOrder::findOrFail($id);
        $wo->update($request->only(['title','description','type','priority','estimated_cost','asset_id','facility_id']));
        return response()->json($wo);
    }

    /** DELETE /api/work-orders/{id} */
    public function destroy($id)
    {
        WkoWorkOrder::findOrFail($id)->delete();
        return response()->json(['message' => 'Work order deleted.']);
    }

    /** PATCH /api/work-orders/{id}/approve */
    public function approve($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $wo   = WkoWorkOrder::findOrFail($id);
        $wo->update(['status' => 'approved', 'approved_by' => $user->user_id, 'approved_at' => now()]);
        return response()->json(['message' => 'Work order approved.', 'work_order' => $wo]);
    }

    /** PATCH /api/work-orders/{id}/assign */
    public function assign(Request $request, $id)
    {
        $v = Validator::make($request->all(), ['assigned_to' => 'required|exists:sys_users,user_id']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $wo = WkoWorkOrder::findOrFail($id);
        $wo->update(['assigned_to' => $request->assigned_to, 'status' => 'in_progress', 'started_at' => now()]);
        return response()->json(['message' => 'Work order assigned.', 'work_order' => $wo]);
    }

    /** PATCH /api/work-orders/{id}/complete */
    public function complete(Request $request, $id)
    {
        $v = Validator::make($request->all(), [
            'resolution_notes' => 'required|string',
            'actual_cost'      => 'nullable|numeric|min:0',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $wo = WkoWorkOrder::findOrFail($id);
        $completedAt = Carbon::now();
        $breached = $wo->sla_deadline && $completedAt->isAfter($wo->sla_deadline);
        $wo->update([
            'status'           => 'completed',
            'completed_at'     => $completedAt,
            'resolution_notes' => $request->resolution_notes,
            'actual_cost'      => $request->actual_cost,
            'sla_breached'     => $breached,
        ]);
        return response()->json(['message' => 'Work order completed.', 'sla_breached' => $breached]);
    }

    /** GET /api/work-orders/report */
    public function report(Request $request)
    {
        $from = $request->get('from', Carbon::now()->subMonth()->toDateString());
        $to   = $request->get('to', Carbon::now()->toDateString());
        $wos  = WkoWorkOrder::whereBetween('requested_at', [$from, $to])->get();
        return response()->json([
            'period'         => compact('from', 'to'),
            'total'          => $wos->count(),
            'by_status'      => $wos->groupBy('status')->map->count(),
            'by_priority'    => $wos->groupBy('priority')->map->count(),
            'sla_breaches'   => $wos->where('sla_breached', true)->count(),
            'avg_resolution' => $wos->whereNotNull('completed_at')->avg(fn($w) => Carbon::parse($w->started_at)->diffInHours($w->completed_at)),
        ]);
    }
}
