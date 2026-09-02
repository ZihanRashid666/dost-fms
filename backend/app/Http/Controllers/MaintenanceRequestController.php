<?php

namespace App\Http\Controllers;

use App\Models\MntMaintenanceRequest;
use App\Models\WkoWorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class MaintenanceRequestController extends Controller
{
    /** GET /api/maintenance-requests */
    public function index(Request $request)
    {
        $query = MntMaintenanceRequest::with(['asset','facility','submittedBy','reviewedBy','workOrder']);
        if ($request->has('status'))   $query->where('status', $request->status);
        if ($request->has('urgency'))  $query->where('urgency', $request->urgency);
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    /** POST /api/maintenance-requests */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'issue_title'       => 'required|string|max:200',
            'issue_description' => 'required|string',
            'urgency'           => 'required|in:low,medium,high,critical',
            'facility_id'       => 'required|exists:fac_facilities,facility_id',
            'asset_id'          => 'nullable|exists:ast_assets,asset_id',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $user  = JWTAuth::parseToken()->authenticate();
        $count = MntMaintenanceRequest::count() + 1;

        $mr = MntMaintenanceRequest::create([
            'request_no'        => 'MR-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'submitted_by'      => $user->user_id,
            'facility_id'       => $request->facility_id,
            'asset_id'          => $request->asset_id,
            'issue_title'       => $request->issue_title,
            'issue_description' => $request->issue_description,
            'urgency'           => $request->urgency,
            'status'            => 'pending',
        ]);

        return response()->json($mr->load(['facility','submittedBy']), 201);
    }

    /** GET /api/maintenance-requests/{id} */
    public function show($id)
    {
        return response()->json(MntMaintenanceRequest::with(['asset','facility','submittedBy','reviewedBy','workOrder'])->findOrFail($id));
    }

    /** PATCH /api/maintenance-requests/{id}/review */
    public function review(Request $request, $id)
    {
        $v = Validator::make($request->all(), [
            'status'         => 'required|in:approved,rejected',
            'reviewer_notes' => 'required|string',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $user = JWTAuth::parseToken()->authenticate();
        $mr   = MntMaintenanceRequest::findOrFail($id);
        $mr->update([
            'status'         => $request->status,
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by'    => $user->user_id,
            'reviewed_at'    => now(),
        ]);
        return response()->json(['message' => "Maintenance request {$request->status}.", 'request' => $mr]);
    }

    /** PATCH /api/maintenance-requests/{id}/convert */
    public function convertToWorkOrder($id)
    {
        $mr    = MntMaintenanceRequest::findOrFail($id);
        $user  = JWTAuth::parseToken()->authenticate();
        $count = WkoWorkOrder::count() + 1;
        $slaMap= ['critical'=>4,'high'=>24,'medium'=>72,'low'=>168];
        $now   = Carbon::now();

        $wo = WkoWorkOrder::create([
            'work_order_no' => 'WO-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'title'         => $mr->issue_title,
            'description'   => $mr->issue_description,
            'type'          => 'corrective',
            'priority'      => $mr->urgency,
            'status'        => 'submitted',
            'facility_id'   => $mr->facility_id,
            'asset_id'      => $mr->asset_id,
            'requested_by'  => $mr->submitted_by,
            'requested_at'  => $now,
            'sla_hours'     => $slaMap[$mr->urgency],
            'sla_deadline'  => $now->copy()->addHours($slaMap[$mr->urgency]),
        ]);

        $mr->update(['status' => 'converted', 'work_order_id' => $wo->work_order_id]);
        return response()->json(['message' => 'Converted to work order.', 'work_order' => $wo], 201);
    }
}
