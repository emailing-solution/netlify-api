<?php

namespace App\Http\Controllers;

use App\Libraries\Netlify;
use App\Models\Account;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

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

    public function load(Account $account = null, Request $request)
    {
        return view('account')->with('account', $account);
    }

    public function add(Account $account = null, Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'token' => 'required|string',
            'proxy' => 'nullable|string'
        ]);

        if(is_null($account)) {
            $res = Account::create([
                'name' => $request->name,
                'token' => $request->token,
                'proxy' => $request->proxy,
                'is_active' => true,
                'user_id' => auth()->id()
            ]);
        } else {
            $res = $account->update([
                'name' => $request->name,
                'token' => $request->token,
                'proxy' => $request->proxy,
            ]);
        }

        return response()->json([
            'status' => $res,
        ]);

    }

    public function check(Account $account): JsonResponse
    {
        Gate::authorize('account_allowed', $account);
        $netfliy = new Netlify($account);
        $result = $netfliy->accounts();
        $body = sprintf("Your Limit :  %s - Left : %s - Reset At : %s",
            $result['limit'],
            $result['left'],
            $result['reset_at']->toDateTimeString()
        );
        return response()->json([
            'status' => $result['status'] ? 'success' : 'error',
            'body' => $body,
        ]);

    }

    public function proxy(Account $account): JsonResponse
    {
        Gate::authorize('account_allowed', $account);
        if($account->proxy) {
            try {
                $result = Http::withOptions(['proxy' => $account->proxy])
                    ->withoutVerifying()
                    ->timeout(5)
                    ->get('https://ip.seeip.org')
                    ->body();
                $status = trim($result) === explode(':', $account->proxy)[0];
                return response()->json([
                    'status' => $status ? 'success' : 'error',
                    'body' => sprintf("Proxy %s Working IP : %s", $status ? '' : 'Not', $result)
                ]);
            } catch (ConnectionException $timeout) {
                return response()->json([
                    'status' => 'error',
                    'body' => 'Proxy Timeout Not Working'
                ]);
            } catch (RequestException $cnx) {
                return response()->json([
                    'status' => 'error',
                    'body' => 'Request Exception'
                ]);
            }
        }
        return response()->json(['error' => 'No Proxy To Check'], 400);
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
