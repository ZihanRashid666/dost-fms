<?php

namespace App\Http\Controllers;

use App\Models\SysUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /** GET /api/users */
    public function index(Request $request)
    {
        $query = SysUser::query();
        if ($request->has('role'))   $query->where('role', $request->role);
        if ($request->has('search')) $query->where('full_name', 'like', "%{$request->search}%");
        return response()->json($query->paginate(15));
    }

    /** POST /api/users */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'full_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:sys_users,email',
            'password'   => 'required|string|min:8',
            'role'       => 'required|in:system_admin,facility_manager,maintenance_staff,requestor,viewer',
            'department' => 'nullable|string|max:100',
            'contact_no' => 'nullable|string|max:20',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $count = SysUser::count() + 1;
        $user = SysUser::create([
            'user_code'     => 'USR-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => $request->role,
            'department'    => $request->department,
            'contact_no'    => $request->contact_no,
            'is_active'     => true,
        ]);

        return response()->json($user, 201);
    }

    /** GET /api/users/{id} */
    public function show($id)
    {
        $user = SysUser::findOrFail($id);
        return response()->json($user);
    }

    /** PUT /api/users/{id} */
    public function update(Request $request, $id)
    {
        $user = SysUser::findOrFail($id);
        $v = Validator::make($request->all(), [
            'full_name'  => 'sometimes|string|max:100',
            'email'      => "sometimes|email|unique:sys_users,email,{$id},user_id",
            'role'       => 'sometimes|in:system_admin,facility_manager,maintenance_staff,requestor,viewer',
            'department' => 'nullable|string|max:100',
            'contact_no' => 'nullable|string|max:20',
            'is_active'  => 'sometimes|boolean',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $user->update($request->only(['full_name','email','role','department','contact_no','is_active']));
        return response()->json($user);
    }

    /** DELETE /api/users/{id} */
    public function destroy($id)
    {
        $user = SysUser::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deactivated.']);
    }

    /** PATCH /api/users/{id}/toggle-status */
    public function toggleStatus($id)
    {
        $user = SysUser::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        return response()->json(['message' => 'Status updated.', 'is_active' => $user->is_active]);
    }
}
