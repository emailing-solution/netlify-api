<?php

namespace App\Http\Controllers;

use App\Libraries\Netlify;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SiteController extends Controller
{
    public function index()
    {
        return view('sites', [
            'accounts' => Account::where('is_active', true)
                ->when(Gate::allows('is_mailer'), fn($q) => $q->where('user_id', auth()->id()))
                ->get(['id', 'name'])
        ]);
    }

    public function sites(Account $account): JsonResponse
    {
        Gate::authorize('account_allowed', $account);
        $netlify = new Netlify($account);

        $sites = $netlify->sites();
        if ($sites) {
            return response()->json($sites);
        }
        return response()->json([
            'error' => 'Could Not Get Sites'
        ], 400);

    }

    public function identity(Account $account, string $site, string $identity, Request $request)
    {
        Gate::authorize('account_allowed', $account);
        if ($request->ajax()) {
            $netlify = new Netlify($account);
            if ($request->t == 'u') {
                $identityUsers = $netlify->identityUsers($site, $identity);
                return response()->json([
                    'data' => $identityUsers
                ]);
            }
            if ($request->t == 'i') {
                $identity = Cache::remember('identity:' . $account->id, '3600', function () use ($netlify, $site, $identity) {
                    $result = $netlify->identity($site, $identity);
                    return !empty($result) ? $result : null;
                });
                if (!empty($identity)) {
                    return response()->json($identity);
                }
                return response()->json([
                    'error' => 'Could not get identity info'
                ], 400);
            }
            abort(404);
        }
        return view('identity');
    }

    public function identityActions(Account $account, string $site, string $identity, Request $request): JsonResponse
    {
        Gate::authorize('account_allowed', $account);
        $request->validate([
            'action' => 'required|string'
        ]);

        $response = response()->json([
            'error' => 'Unknown Action'
        ], 400);

        $netlify = new Netlify($account);
        switch ($request->action) {
            case 'template' :
                $request->validate([
                    'subject' => ['required', 'string'],
                    'template' => ['required', 'string'],
                ]);
                $result = $netlify->paramsIdentity($site, $identity, $request->subject, $request->template);
                if ($result) {
                    Cache::forget('identity:' . $account->id);
                    $response = response()->json([
                        'data' => 'Changed Successfully'
                    ]);
                    break;
                }
                $response = response()->json([
                    'error' => 'Error Occured'
                ], 400);
                break;
            case 'add':
                $request->validate([
                    'emails' => ['required', 'array']
                ]);

                $emails = [];
                foreach ($request->emails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = ['email' => $email];
                    }
                }

                if (!empty($emails)) {
                    $result = $netlify->inviteIdentity($site, $identity, $emails);
                    if ($result['status']) {
                        $response = response()->json(['data' => 'Added Successfully']);
                        break;
                    }
                }
                $response = response()->json(['error' => 'Something bad occurred :('], 400);
                break;

            case 'remove':
                $request->validate([
                    'users' => ['required', 'array']
                ]);

                $nb = 0;
                foreach ($request->users as $user) {
                    $netlify->removeInviteIdentity($site, $identity, $user) && $nb++;
                }
                $response = response()->json([
                    'data' => "Removed {$nb} Users"
                ]);
                break;
        }

        return $response;
    }
}
