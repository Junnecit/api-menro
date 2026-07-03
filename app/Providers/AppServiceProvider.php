<?php

namespace App\Providers;

use App\Models\Agency;
use App\Models\PlantingMonitoring;
use App\Models\Request as PlantingRequestModel;
use App\Models\TestItem;
use App\Models\Tree;
use App\Models\User;
use App\Policies\AgencyPolicy;
use App\Policies\PlantingMonitoringPolicy;
use App\Policies\RequestPolicy;
use App\Policies\TestItemPolicy;
use App\Policies\TreePolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Agency::class, AgencyPolicy::class);
        Gate::policy(PlantingRequestModel::class, RequestPolicy::class);
        Gate::policy(TestItem::class, TestItemPolicy::class);
        Gate::policy(Tree::class, TreePolicy::class);
        Gate::policy(PlantingMonitoring::class, PlantingMonitoringPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->input('email').'|'.$request->ip());
        });
    }
}
