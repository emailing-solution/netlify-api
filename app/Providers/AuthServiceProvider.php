<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //
        Gate::define('is_admin', fn($user) => $user->type === 'admin');
        Gate::define('is_mailer', fn($user) => $user->type === 'mailer');
        Gate::define('account_allowed', fn($user, $account) => $user->type === 'admin' || $user->id === $account->user_id);
        Gate::define('process_allowed', fn($user, $process) => $user->type === 'admin' || $user->id === $process->user_id);
    }
}
