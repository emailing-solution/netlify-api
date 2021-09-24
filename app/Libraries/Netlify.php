<?php

namespace App\Libraries;

use App\Models\Account;

class Netlify
{
    const API_URL = "https://api.netlify.com/api/v1";

    private Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
