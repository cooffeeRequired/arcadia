<?php

namespace Core\Logging;

use Core\Database\DatabaseLogger;
use Core\Render\Renderer;
use Core\Render\View;
use Tracy\IBarPanel;

/**
 * Custom panel pro Arcadia CRM
 */
class ArcadiaPanel implements IBarPanel
{
    public function getTab(): string
    {
        $totalViews = count(View::$renderedViews);
        $totalQueries = count(DatabaseLogger::$queries);

        return '<span title="Arcadia CRM">🏛️ Arcadia</span>' .
            '<span class="tracy-label">v1.0.0 / ' . $totalViews . ' views</span>';
    }

    public function getPanel(): string
    {
        $appInfo = [
            'Název aplikace' => 'Arcadia CRM',
            'Verze' => '1.0.0',
            'Prostředí' => 'Development',
            'PHP verze' => PHP_VERSION,
            'Framework' => 'Custom PHP Framework',
            'Blade šablony' => 'Aktivní',
            'Tailwind CSS' => 'Aktivní',
            'Tracy Debugger' => 'Aktivní',
            'Auto-refresh' => 'Aktivní'
        ];


        $panel = '<h1>🏛️ Arcadia CRM - Informace o aplikaci</h1>
                <div class="tracy-inner">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">v1.0.0</div>
                            <div style="font-size: 12px; opacity: 0.8;">Verze</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">Development</div>
                            <div style="font-size: 12px; opacity: 0.8;">Prostředí</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . count(View::$renderedViews) . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Rendered views</div>
                        </div>
                    </div>
                    <h2>Základní informace</h2>
                    <table class="tracy-sortable">
                        <thead>
                            <tr>
                                <th>Klíč</th>
                                <th>Hodnota</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($appInfo as $key => $value) {
            $panel .= '<tr>
                        <td>' . htmlspecialchars($key) . '</td>
                        <td>' . htmlspecialchars($value) . '</td>
                       </tr>';
        }

        $panel .= '</tbody></table>';

        $panel .= '<h2>Konfigurace (příklad)</h2>
                    <pre>' . htmlspecialchars(json_encode([
                'DEBUG_MODE' => true,
                'DATABASE_TYPE' => 'SQLite',
                'CACHE_ENABLED' => true,
                'AUTO_REFRESH' => true,
                'TRACY_DEBUGGER' => true
            ], JSON_PRETTY_PRINT)) . '</pre>';

        $panel .= '</div>';

        return $panel;
    }
}