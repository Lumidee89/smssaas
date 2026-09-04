<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProductionReadiness extends Command
{
    protected $signature = 'schoolos:production-check';

    protected $description = 'Validate critical SchoolOS production configuration';

    public function handle(): int
    {
        $checks = [
            ['APP_ENV is production', app()->environment('production')],
            ['APP_DEBUG is disabled', ! config('app.debug')],
            ['APP_KEY is configured', filled(config('app.key'))],
            ['APP_URL uses HTTPS', str_starts_with((string) config('app.url'), 'https://')],
            ['Database is not SQLite', config('database.default') !== 'sqlite'],
            ['Queue uses Redis', config('queue.default') === 'redis'],
            ['Cache uses Redis', config('cache.default') === 'redis'],
            ['Sessions use Redis', config('session.driver') === 'redis'],
            ['BulkSMSLive API key is configured', filled(config('services.bulksmslive.api_key'))],
            ['Paystack secret is configured', filled(config('payment.paystack.secret_key'))],
        ];

        $failed = false;
        foreach ($checks as [$label, $passed]) {
            $passed ? $this->components->info($label) : $this->components->error($label);
            $failed = $failed || ! $passed;
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
