<?php

namespace Core\Logging;

use Core\Render\View;
use Tracy\IBarPanel;

/**
 * Custom panel pro šablony
 */
class ViewPanel implements IBarPanel
{
    public function getTab(): string
    {
        $totalViews = count(View::$renderedViews);
        $totalTime = array_sum(array_column(View::$renderedViews, 'time'));

        return '<span title="Views">📄 Views</span>' .
            '<span class="tracy-label">' . $totalViews . ' views / ' . sprintf('%.1f', $totalTime) . 'ms</span>';
    }

    public function getPanel(): string
    {
        $views = View::$renderedViews;

        if (empty($views)) {
            return '<h1>Rendered Views</h1>
                    <div class="tracy-inner">
                        <p>Žádné šablony nebyly renderovány.</p>
                    </div>';
        }

        $totalTime = array_sum(array_column($views, 'time'));
        $totalSize = array_sum(array_column($views, 'size'));

        $panel = '<h1>📄 Rendered Views</h1>
                <div class="tracy-inner">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . count($views) . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Celkem šablon</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . number_format($totalTime, 2) . 'ms</div>
                            <div style="font-size: 12px; opacity: 0.8;">Celkový čas</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . number_format($totalSize / 1024, 2) . ' KB</div>
                            <div style="font-size: 12px; opacity: 0.8;">Celková velikost</div>
                        </div>
                    </div>
                    <table class="tracy-sortable">
                        <thead>
                            <tr>
                                <th>Šablona</th>
                                <th>Čas (ms)</th>
                                <th>Velikost (KB)</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($views as $view) {
            $name = htmlspecialchars($view['name']);
            $time = number_format($view['time'], 2);
            $size = number_format($view['size'] / 1024, 2);
            $data = json_encode($view['data'], JSON_PRETTY_PRINT);

            $panel .= '<tr>
                        <td>' . $name . '</td>
                        <td>' . $time . '</td>
                        <td>' . $size . '</td>
                        <td><pre>' . htmlspecialchars($data) . '</pre></td>
                       </tr>';
        }

        $panel .= '</tbody></table></div>';

        return $panel;
    }
}