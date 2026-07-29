<?php

namespace App\Providers;

use App\Mail\EmbedsMenroLogo;
use App\Models\Agency;
use App\Models\PlantingMonitoring;
use App\Models\ReportFile;
use App\Models\ReportFolder;
use App\Models\Request as PlantingRequestModel;
use App\Models\TestItem;
use App\Models\Tree;
use App\Models\User;
use App\Policies\AgencyPolicy;
use App\Policies\PlantingMonitoringPolicy;
use App\Policies\ReportCenterPolicy;
use App\Policies\RequestPolicy;
use App\Policies\TestItemPolicy;
use App\Policies\TreePolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
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
        Gate::policy(ReportFolder::class, ReportCenterPolicy::class);
        Gate::policy(ReportFile::class, ReportCenterPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->input('email').'|'.$request->ip());
        });

        // This is an API-only app with no `password.reset` web route, so the
        // default notification (which calls route('password.reset', ...))
        // would throw. Point the reset link at the frontend page instead.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

            return "{$frontendUrl}/reset-password?token={$token}&email=".urlencode($notifiable->getEmailForPasswordReset());
        });

        // Mirrors ResetPassword::buildMailMessage(), just adding the embedded
        // logo (see App\Mail\EmbedsMenroLogo) on top of the default content.
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = call_user_func(ResetPassword::$createUrlCallback, $notifiable, $token);
            $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return EmbedsMenroLogo::embed(
                (new MailMessage)
                    ->subject('Reset your password')
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->action('Reset Password', $url)
                    ->line("This password reset link will expire in {$expireMinutes} minutes.")
                    ->line('If you did not request a password reset, no further action is required.')
            );
        });
    }
}
