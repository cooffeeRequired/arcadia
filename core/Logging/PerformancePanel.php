<?php

namespace Core\Logging;

use Tracy\IBarPanel;

/**
 * Custom panel pro výkon
 */
class PerformancePanel implements IBarPanel
{
    public function getTab(): string
    {
        $memoryUsage = memory_get_usage(true);
        $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        return '<span title="Performance">🚀 Perf</span>' .
            '<span class="tracy-label">' . $this->formatBytes($memoryUsage) . ' / ' . sprintf('%.1f', $executionTime * 1000) . 'ms</span>';
    }

    public function getPanel(): string
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        return '<h1>🚀 Performance</h1>
                <div class="tracy-inner">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . $this->formatBytes($memoryUsage) . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Memory usage</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . $this->formatBytes($memoryPeak) . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Memory peak</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . number_format($executionTime * 1000, 1) . 'ms</div>
                            <div style="font-size: 12px; opacity: 0.8;">Execution time</div>
                        </div>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; margin-top: 20px;">
                        <p><strong>PHP version:</strong> ' . PHP_VERSION . '</p>
                        <p><strong>Server:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>
                    </div>
                </div>';
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . $units[$pow];
    }
}