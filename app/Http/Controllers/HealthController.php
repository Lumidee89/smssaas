<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => fn () => DB::select('select 1'),
            'cache' => function (): void {
                $key = 'health:'.bin2hex(random_bytes(8));
                Cache::put($key, 'ok', 10);
                if (Cache::get($key) !== 'ok') {
                    throw new \RuntimeException('Cache read failed.');
                }
                Cache::forget($key);
            },
        ];

        $results = [];
        foreach ($checks as $name => $check) {
            try {
                $check();
                $results[$name] = 'ok';
            } catch (Throwable) {
                $results[$name] = 'failed';
            }
        }

        $ready = ! in_array('failed', $results, true);

        return response()->json([
            'status' => $ready ? 'ready' : 'degraded',
            'checks' => $results,
            'timestamp' => now()->toIso8601String(),
        ], $ready ? 200 : 503);
    }
}
