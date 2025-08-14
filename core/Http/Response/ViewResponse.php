<?php

namespace Core\Http\Response;

use Core\Render\View;
use Core\Logging\RenderLogger;
use Core\Events\EventBus;

class ViewResponse extends AbstractResponse
{
    public function __construct(string $view, array $data = [], int $statusCode = 200, array $headers = [])
    {
        // Vyvolá událost před renderem
        EventBus::beforeRender($view, $data);

        $startTime = microtime(true);
        $content = View::render($view, $data);
        $renderTime = microtime(true) - $startTime;

        parent::__construct($content, $statusCode, $headers);
        $this->contentType = 'text/html; charset=UTF-8';

        // Loguje metriky
        RenderLogger::logRender('view', $startTime, $this);

        // Vyvolá událost po renderu
        EventBus::afterRender($view, $this, $renderTime);
    }

    /**
     * Vytvoří ViewResponse s view
     */
    public static function create(string $view, array $data = [], int $statusCode = 200, array $headers = []): self
    {
        return new self($view, $data, $statusCode, $headers);
    }

    /**
     * Přepíše send metodu pro logování
     */
    public function send(): void
    {
        // Vyvolá událost před odesláním
        EventBus::beforeSend($this);

        parent::send();

        // Vyvolá událost po odeslání
        EventBus::afterSend($this);
    }
}
