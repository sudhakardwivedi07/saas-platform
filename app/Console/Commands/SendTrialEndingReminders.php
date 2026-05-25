<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\Company;
use App\Models\SuperAdmin\Subscription;
use Illuminate\Console\Command;

class SendTrialEndingReminders extends Command
{
    protected $signature   = 'subscriptions:trial-reminders';
    protected $description = 'Email tenants whose trial ends in 3 days or 1 day.';

    public function handle(): void
    {
        $reminders = [3, 1]; // days before trial ends

        foreach ($reminders as $days) {
            $date = now()->addDays($days)->toDateString();

            $subscriptions = Subscription::where('status', 'trialing')
                ->whereDate('trial_ends_at', $date)
                ->with('company.users')
                ->get();

            foreach ($subscriptions as $sub) {
                $company = $sub->company;
                if (!$company) continue;

                $admin = $company->users()->first();
                if (!$admin) continue;

                SendEmailJob::dispatch(
                    toEmail:      $admin->email,
                    templateSlug: 'trial_ending',
                    placeholders: [
                        '{{name}}'           => $admin->name,
                        '{{app_name}}'      => $company->app_name,
                        '{{days_left}}'     => (string) $days,
                        '{{trial_end_date}}' => $sub->trial_ends_at->format('F j, Y'),
                        '{{billing_url}}'   => $company->domainUrlFor('billing') ?? url('/billing'),
                    ],
                    companyId: $company->id
                )->onQueue('emails');

                $this->line("  ✓ Sent {$days}-day reminder to {$admin->email}");
            }
        }

        $this->info('Trial reminders dispatched.');
    }
}
