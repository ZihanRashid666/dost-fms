<?php

namespace App\Http\Controllers;

use App\Models\AstAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AssetController extends Controller
{
    /** GET /api/assets */
    public function index(Request $request)
    {
        $query = AstAsset::with(['category', 'facility', 'assignedTo']);
        if ($request->has('facility_id')) $query->where('facility_id', $request->facility_id);
        if ($request->has('category_id')) $query->where('category_id', $request->category_id);
        if ($request->has('status'))      $query->where('status', $request->status);
        if ($request->has('search'))      $query->where('asset_name', 'like', "%{$request->search}%");
        return response()->json($query->paginate(15));
    }

    /** POST /api/assets */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'asset_name'       => 'required|string|max:150',
            'category_id'      => 'required|exists:ast_categories,category_id',
            'facility_id'      => 'required|exists:fac_facilities,facility_id',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life_years'=> 'sometimes|integer|min:1',
            'warranty_expiry_date' => 'nullable|date',
            'pm_interval_days' => 'sometimes|integer|min:1',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        // Auto-generate asset code
        $count = AstAsset::count() + 1;
        $data = $request->all();
        $data['asset_code'] = 'AST-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        // Calculate straight-line depreciation
        $cost       = $data['acquisition_cost'];
        $life       = $data['useful_life_years'] ?? 5;
        $salvage    = $cost * 0.10;
        $annual_dep = ($cost - $salvage) / $life;

        $data['salvage_value']      = $salvage;
        $data['annual_depreciation'] = $annual_dep;
        $data['current_value']      = $this->calculateCurrentValue($cost, $salvage, $life, $data['acquisition_date']);

        // Set first PM date
        if (!empty($data['pm_interval_days'])) {
            $data['next_pm_date'] = Carbon::parse($data['acquisition_date'])->addDays($data['pm_interval_days'])->toDateString();
        }

        $asset = AstAsset::create($data);
        return response()->json($asset->load(['category', 'facility']), 201);
    }

    /** GET /api/assets/{id} */
    public function show($id)
    {
        return response()->json(AstAsset::with(['category','facility','assignedTo'])->findOrFail($id));
    }

    /** PUT /api/assets/{id} */
    public function update(Request $request, $id)
    {
        $asset = AstAsset::findOrFail($id);
        $asset->update($request->all());
        // Recalculate depreciation if cost or life changed
        if ($request->has('acquisition_cost') || $request->has('useful_life_years')) {
            $cost    = $asset->acquisition_cost;
            $life    = $asset->useful_life_years;
            $salvage = $cost * 0.10;
            $asset->update([
                'salvage_value'      => $salvage,
                'annual_depreciation'=> ($cost - $salvage) / $life,
                'current_value'      => $this->calculateCurrentValue($cost, $salvage, $life, $asset->acquisition_date),
            ]);
        }
        return response()->json($asset);
    }

    /** DELETE /api/assets/{id} */
    public function destroy($id)
    {
        AstAsset::findOrFail($id)->delete();
        return response()->json(['message' => 'Asset deleted.']);
    }

    /** GET /api/assets/warranty-expiring */
    public function warrantyExpiring(Request $request)
    {
        $days = $request->get('days', 30);
        $assets = AstAsset::whereNotNull('warranty_expiry_date')
            ->where('warranty_expiry_date', '<=', Carbon::now()->addDays($days))
            ->where('warranty_expiry_date', '>=', Carbon::now())
            ->get();
        return response()->json($assets);
    }

    /** GET /api/assets/pm-due */
    public function pmDue()
    {
        $assets = AstAsset::whereNotNull('next_pm_date')
            ->where('next_pm_date', '<=', Carbon::now()->addDays(7))
            ->get();
        return response()->json($assets);
    }

    /** PATCH /api/assets/{id}/depreciation */
    public function recalculateDepreciation($id)
    {
        $asset = AstAsset::findOrFail($id);
        $cost    = $asset->acquisition_cost;
        $life    = $asset->useful_life_years;
        $salvage = $cost * 0.10;
        $current = $this->calculateCurrentValue($cost, $salvage, $life, $asset->acquisition_date);
        $asset->update([
            'salvage_value'      => $salvage,
            'annual_depreciation'=> ($cost - $salvage) / $life,
            'current_value'      => $current,
        ]);
        return response()->json(['message' => 'Depreciation recalculated.', 'current_value' => $current]);
    }

    private function calculateCurrentValue($cost, $salvage, $life, $acquisitionDate): float
    {
        $yearsElapsed = Carbon::parse($acquisitionDate)->diffInYears(Carbon::now());
        $depreciated  = $cost - (($cost - $salvage) / $life * $yearsElapsed);
        return max($depreciated, $salvage);
    }
}
