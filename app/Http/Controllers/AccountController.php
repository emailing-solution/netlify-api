<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax()) {
            return datatables()->of(Account::query())->toJson();
        }
        return view('accounts');
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'token' => 'required|string'
        ]);

        $res = Account::create([
            'name' => $request->name,
            'token' => $request->token,
            'is_active' => true
        ]);

        return response()->json([
            'status' => $res,
        ]);
    }

    public function status(Account $account, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $result = $account->fill(['is_active' => $request->status])->save();
        return response()->json([
            'status' => $result
        ]);
    }

    public function delete(Account $account): JsonResponse
    {
        return response()->json([
            'status' => $account->delete()
        ]);
    }

}
