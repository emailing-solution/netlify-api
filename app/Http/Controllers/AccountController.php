<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax()) {
            $accounts = Account::query()->when(Gate::allows('is_mailer'), fn($q) => $q->where('user_id', auth()->id()));
            return datatables()->of($accounts)->toJson();
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
            'is_active' => true,
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'status' => $res,
        ]);
    }

    public function status(Account $account, Request $request): JsonResponse
    {
        Gate::authorize('account_allowed', $account);
        $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $result = $account->update(['is_active' => $request->status]);
        return response()->json([
            'status' => $result
        ]);
    }

    public function delete(Account $account): JsonResponse
    {
        Gate::authorize('is_admin');
        return response()->json([
            'status' => $account->delete()
        ]);
    }

}
