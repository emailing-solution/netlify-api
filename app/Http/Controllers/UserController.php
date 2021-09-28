<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()->of(User::withCount(['accounts', 'processes']))->toJson();
        }
        return view('users');
    }

    public function load(User $user = null, Request $request)
    {
        return view('user')->with('user', $user);
    }

    public function add(User $user = null, Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'password' => [Rule::requiredIf(empty($user))],
            'type' => ['required', 'string', 'in:mailer,admin'],
        ]);

        if (is_null($user)) {
            $request->validate([
                'username' => ['required', 'string', Rule::unique('users', 'username')],
            ]);
            $result = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'type' => $request->type,
                'is_active' => true,
            ]);
            if($result) {
                return response()->json([
                    'data' => 'Added Successfully'
                ]);
            }
        } else {
            $request->validate([
                'username' => ['required', 'string', Rule::unique('users', 'username')->ignoreModel($user)],
            ]);
            $data = [
                'name' => $request->name,
                'username' => $request->username,
                'type' => $request->type,
            ];
            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }
            $result = $user->update($data);
            if($result) {
                return response()->json([
                    'data' => 'Updated Successfully'
                ]);
            }
        }

        return response()->json([
            'error' => "Failed"
        ], 400);
    }

    public function status(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $result = $user->update(['is_active' => $request->status]);
        return response()->json([
            'status' => $result
        ]);
    }

    public function delete(User $user): JsonResponse
    {
        return response()->json([
            'status' => $user->delete()
        ]);
    }
}
