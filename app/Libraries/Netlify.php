<?php

namespace App\Libraries;

use App\Models\Account;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Netlify
{
    const API_URL = "https://api.netlify.com/api/v1";

    private Account $account;
    private PendingRequest $http;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->http = Http::withToken($account->token)->timeout(10);
        if(!empty($account->proxy)) {
            $this->http->withOptions(['proxy' => $account->proxy])->withoutVerifying();
        }
    }

    //get user information
    public function user()
    {
        $request = $this->http->asJson()->get(self::API_URL . "/user");
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get accounts
    public function accounts(): array
    {
        $request = $this->http->asJson()
            ->get(self::API_URL . "/accounts");
        return [
            'status' => $request->successful(),
            'limit' => $request->header('X-Ratelimit-Limit'),
            'left' => $request->header('X-Ratelimit-Remaining'),
            'reset_at' => $this->parseResetTime($request->header('X-Ratelimit-Reset')),
            'data' => $request->json()
        ];
    }

    //get sites
    public function sites()
    {
        $request = $this->http->asJson()->get(sprintf("%s/sites", self::API_URL), [
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
        $request = $this->http->asJson()->get(sprintf("%s/sites/%s", self::API_URL, $site));
        if ($request->successful()) {
            return $request->json();
        }
        return false;
    }

    //get identity info
    public function identity(string $site, string $identity)
    {
        $request = $this->http->asJson()
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
        $request = $this->http->asJson()
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
        $request = $this->http->asForm()
            ->put(sprintf("%s/sites/%s/identity/%s", self::API_URL, $site, $identity), [
                'subjects' => ['invite' => $subject],
                'templates' => ['invite' => $template]
            ]);
        return $request->successful();
    }

    //invite identity users
    public function inviteIdentity(string $site, string $identity, array $emails): array
    {
        $request = $this->http->asJson()
            ->post(sprintf("%s/sites/%s/identity/%s/users/invite", self::API_URL, $site, $identity), [
                'invites' => $emails
            ]);
        $result = [
            'account' => $this->account->id,
            'status' => $request->successful(),
            'code' => $request->status(),
            'limit' => $request->header('X-Ratelimit-Limit'),
            'left' => $request->header('X-Ratelimit-Remaining'),
            'reset_at' => $this->parseResetTime($request->header('X-Ratelimit-Reset')),
            'headers' => $request->headers(),
            'body' => $request->body()
        ];
        if ($request->failed()) {
            Log::error('Error invites account ' . $this->account->id, $result);
        }
        return $result;
    }

    //delete invite user
    public function removeInviteIdentity(string $site, string $identity, string $userId): bool
    {
        $request = $this->http->asJson()
            ->delete(sprintf("%s/sites/%s/identity/%s/users/%s", self::API_URL, $site, $identity, $userId));
        return $request->successful();
    }

    private function isValidTimeStamp($timestamp): bool
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    private function parseResetTime($time): Carbon
    {
        if($this->isValidTimeStamp($time)) {
            return Carbon::createFromTimestamp($time);
        }
        return Carbon::parse($time);
    }


}
