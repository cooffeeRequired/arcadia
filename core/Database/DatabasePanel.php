<?php

namespace Core\Database;

use Tracy\IBarPanel;

/**
 * Custom panel pro databázi
 */
class DatabasePanel implements IBarPanel
{
    public function getTab(): string
    {
        $totalQueries = count(DatabaseLogger::$queries);
        $totalTime = array_sum(array_column(DatabaseLogger::$queries, 'time'));

        return '<span title="Database">💾 DB</span>' .
            '<span class="tracy-label">' . $totalQueries . ' queries / ' . sprintf('%.1f', $totalTime) . 'ms</span>';
    }

    public function getPanel(): string
    {
        $queries = DatabaseLogger::$queries;

        if (empty($queries)) {
            return '<h1>Database Queries</h1>
                    <div class="tracy-inner">
                        <p>Žádné dotazy nebyly provedeny.</p>
                    </div>';
        }

        $totalTime = array_sum(array_column($queries, 'time'));
        $totalRows = array_sum(array_column($queries, 'rows'));

        $panel = '<h1>🗄️ Database Queries</h1>
                <div class="tracy-inner">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . count($queries) . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Celkem dotazů</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . number_format($totalTime, 2) . 'ms</div>
                            <div style="font-size: 12px; opacity: 0.8;">Celkový čas</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . $totalRows . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Celkem řádků</div>
                        </div>
                    </div>
                    <table class="tracy-sortable">
                        <thead>
                            <tr>
                                <th>SQL</th>
                                <th>Čas (ms)</th>
                                <th>Řádky</th>
                                <th>Parametry</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($queries as $query) {
            $sql = htmlspecialchars($query['sql']);
            $time = number_format($query['time'] ?? 0, 2);
            $rows = $query['rows'] ?? 'N/A';
            $params = $query['params'] ? json_encode($query['params']) : 'N/A';

            $panel .= '<tr>
                        <td><pre>' . $sql . '</pre></td>
                        <td>' . $time . '</td>
                        <td>' . $rows . '</td>
                        <td>' . htmlspecialchars($params) . '</td>
                       </tr>';
        }

        $panel .= '</tbody></table></div>';

        return $panel;
    }
}