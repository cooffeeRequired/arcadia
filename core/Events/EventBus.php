<?php

namespace Core\Events;

use Core\Http\Response\AbstractResponse;

class EventBus
{
    private static array $listeners = [];
    private static array $events = [];

    /**
     * Registruje listener pro událost
     */
    public static function listen(string $event, callable $listener): void
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $listener;
    }

    /**
     * Vyvolá událost
     */
    public static function dispatch(string $event, array $data = []): void
    {
        self::$events[] = [
            'event' => $event,
            'data' => $data,
            'timestamp' => microtime(true)
        ];

        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                $listener($data);
            }
        }
    }

    /**
     * Vyvolá událost před renderem
     */
    public static function beforeRender(string $view, array $data): void
    {
        self::dispatch('render.before', [
            'view' => $view,
            'data' => $data,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Vyvolá událost po renderu
     */
    public static function afterRender(string $view, AbstractResponse $response, float $renderTime): void
    {
        self::dispatch('render.after', [
            'view' => $view,
            'response' => $response,
            'render_time' => $renderTime,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Vyvolá událost před odesláním response
     */
    public static function beforeSend(AbstractResponse $response): void
    {
        self::dispatch('response.before_send', [
            'response' => $response,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Vyvolá událost po odeslání response
     */
    public static function afterSend(AbstractResponse $response): void
    {
        self::dispatch('response.after_send', [
            'response' => $response,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Získá všechny události
     */
    public static function getEvents(): array
    {
        return self::$events;
    }

    /**
     * Vyčistí události
     */
    public static function clear(): void
    {
        self::$events = [];
    }

    /**
     * Získá počet událostí
     */
    public static function getEventCount(): int
    {
        return count(self::$events);
    }
}
