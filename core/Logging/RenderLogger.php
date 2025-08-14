<?php

namespace Core\Logging;

use Core\Http\Response\AbstractResponse;

class RenderLogger
{
    private static array $metrics = [];

    /**
     * Loguje metrika renderu
     */
    public static function logRender(string $type, float $startTime, AbstractResponse $response): void
    {
        $endTime = microtime(true);
        $renderTime = ($endTime - $startTime) * 1000; // v ms
        $contentSize = strlen($response->getContent());

        $metric = [
            'type' => $type,
            'render_time_ms' => $renderTime,
            'content_size_bytes' => $contentSize,
            'status_code' => $response->getStatusCode(),
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];

        self::$metrics[] = $metric;

        // Log do souboru pokud je debug mode
        if (defined('APP_CONFIGURATION') && APP_CONFIGURATION['app_debug']) {
            $logMessage = sprintf(
                "[%s] %s render: %.2fms, %d bytes, status: %d, memory: %s",
                $metric['timestamp'],
                $type,
                $renderTime,
                $contentSize,
                $response->getStatusCode(),
                self::formatBytes($metric['memory_usage'])
            );

            error_log($logMessage);
        }
    }

    /**
     * Získá všechny metriky
     */
    public static function getMetrics(): array
    {
        return self::$metrics;
    }

    /**
     * Získá průměrný čas renderu
     */
    public static function getAverageRenderTime(): float
    {
        if (empty(self::$metrics)) {
            return 0.0;
        }

        $totalTime = array_sum(array_column(self::$metrics, 'render_time_ms'));
        return $totalTime / count(self::$metrics);
    }

    /**
     * Získá celkovou velikost obsahu
     */
    public static function getTotalContentSize(): int
    {
        return array_sum(array_column(self::$metrics, 'content_size_bytes'));
    }

    /**
     * Vyčistí metriky
     */
    public static function clear(): void
    {
        self::$metrics = [];
    }

    /**
     * Formátuje bajty do čitelné podoby
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
