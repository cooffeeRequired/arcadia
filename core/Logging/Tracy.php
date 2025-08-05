<?php

namespace Core\Logging;

use Core\Database\DatabasePanel;
use Tracy\Debugger;

class Tracy
{
    public static function init()
    {
        // Inicializace Tracy
        Debugger::enable(Debugger::Development, APP_CONFIGURATION['cache_dir'] . '/tracy');

        // Přidání custom panelů
        Debugger::getBar()->addPanel(new DatabasePanel(), 'database');
        Debugger::getBar()->addPanel(new ViewPanel(), 'views');
        Debugger::getBar()->addPanel(new CachePanel(), 'cache');
        Debugger::getBar()->addPanel(new PerformancePanel(), 'performance');
        Debugger::getBar()->addPanel(new ArcadiaPanel(), 'arcadia');

        // Nastavení vlastních stylů
        Debugger::$customCssFiles[] = APP_ROOT . '/resources/css/tracy-custom.css';
    }
}