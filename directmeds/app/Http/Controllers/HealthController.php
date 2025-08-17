<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'services' => []
        ];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $health['services']['database'] = 'healthy';
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['services']['database'] = 'unhealthy';
        }

        // Check Redis connection
        try {
            Redis::ping();
            $health['services']['redis'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['redis'] = 'unavailable';
        }

        // Check disk space
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsagePercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
        
        if ($diskUsagePercent > 90) {
            $health['status'] = 'warning';
            $health['services']['disk'] = 'warning';
        } else {
            $health['services']['disk'] = 'healthy';
        }

        $health['metrics'] = [
            'disk_usage_percent' => $diskUsagePercent,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    public function liveness()
    {
        return response()->json(['status' => 'alive'], 200);
    }

    public function readiness()
    {
        try {
            // Check database
            DB::connection()->getPdo();
            
            // Check Redis
            Redis::ping();
            
            return response()->json(['status' => 'ready'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'not_ready', 'error' => $e->getMessage()], 503);
        }
    }
}