<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthCheckController extends Controller
{
    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = collect($checks)->every(fn (array $check) => $check['ok']);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true, 'message' => 'connected'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'unavailable'];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.uniqid();
            Cache::put($key, true, 10);
            $ok = Cache::get($key) === true;
            Cache::forget($key);

            return ['ok' => $ok, 'message' => $ok ? 'read_write_ok' : 'read_write_failed'];
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'unavailable'];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            Queue::connection($connection)->size();

            return ['ok' => true, 'message' => $connection];
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'unavailable'];
        }
    }
}
