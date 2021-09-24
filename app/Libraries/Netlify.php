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
        if($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get accounts
    public function accounts()
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(self::API_URL . "/accounts");
        if($request->successful()) {
            return $request->json();
        }
        return false;
    }

    public function sites(string $account)
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(sprintf("%s/%s/sites", self::API_URL, $account), [
                'sort_by' => 'updated_at',
                'per_page' => 100
            ]);
        if($request->successful()) {
            return $request->json();
        }
        return false;
    }

    public function site(string $account, string $site)
    {
        $request = Http::asJson()->withToken($this->account->token)
            ->get(sprintf("%s/%s/sites/%s", self::API_URL, $account, $site));
        if($request->successful()) {
            return $request->json();
        }
        return false;
    }
}
