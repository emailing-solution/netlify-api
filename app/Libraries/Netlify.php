<?php

namespace App\Libraries;

use App\Models\Account;
use Illuminate\Support\Facades\Http;

class Netlify
{
    const API_URL = "https://api.netlify.com/api/v1";

    private Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    //get user information
    public function user()
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(self::API_URL . "/user");
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get accounts
    public function accounts()
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(self::API_URL . "/accounts");
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get sites
    public function sites()
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(sprintf("%s/sites", self::API_URL), [
                'sort_by' => 'updated_at',
                'per_page' => 100
            ]);
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get site
    public function site(string $site)
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(sprintf("%s/sites/%s", self::API_URL, $site));
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get identity info
    public function identity(string $site, string $identity)
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(sprintf("%s/sites/%s/identity/%s", self::API_URL, $site, $identity), [
                'page' => 1,
                'per_page' => 100
            ]);
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get user identity
    public function identityUsers(string $site, string $identity)
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(sprintf("%s/sites/%s/identity/%s/users", self::API_URL, $site, $identity), [
                'page' => 1,
                'per_page' => 100
            ]);
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    // set invite identity params
    public function paramsIdentity(string $site, string $identity, string $subject = null, string $template = null): bool
    {
        $request = Http::asForm()->withToken($this->account->token)
            ->put(sprintf("%s/sites/%s/identity/%s", self::API_URL, $site, $identity), [
                'subjects' => ['invite' => $subject],
                'templates' => ['invite' => $template]
            ]);
        return $request->successful();
    }

    //invite identity users
    public function inviteIdentity(string $site, string $identity, array $emails): bool
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->post(sprintf("%s/sites/%s/identity/%s/users/invite", self::API_URL, $site, $identity), [
                'invites' => $emails
            ]);
        return $request->successful();
    }

    //delete invite user
    public function removeInviteIdentity(string $site, string $identity, string $userId): bool
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->delete(sprintf("%s/sites/%s/identity/%s/users/%s", self::API_URL, $site, $identity, $userId));
        return $request->successful();
    }
}
