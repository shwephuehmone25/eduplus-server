<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function countTotalAdmins()
    {
        $admins = Admin::count();

        return response()->json(['data' => $admins, 'status' => 200]);
    }

    public function index()
    {
        $admins = Admin::all();

        return response()->json(['data' => $admins, 'status' => 200]);
    }

    public function updateProfile(Request $request, $adminId)
    {
        $admin = Admin::find($adminId);

        if(!$admin) {
            return response()->json(['message' => 'Admin not found!', 'status' => 404]);
        }

        $request->validate([
            'name' => 'required'
        ]);

        $admin->name = $request->input('name');
        $admin->image_url = $request->input('image_url');
        $admin->save();

        return response()->json(['message' => 'Profile updated successfully!', 'data' => $admin, 'status' => 200]);
    }
}
