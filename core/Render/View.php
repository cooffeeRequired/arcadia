<?php

namespace Core\Render;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class View
{
    private static ?Factory $factory = null;
    private static ?BladeCompiler $compiler = null;
    private static ?EngineResolver $resolver = null;
    private static ?FileViewFinder $finder = null;

    public static array $renderedViews = []; // Pro logování rendered šablon

    public static function init(): void
    {
        if (self::$factory === null) {
            $filesystem = new Filesystem();
            $dispatcher = new Dispatcher();
            self::$resolver = new EngineResolver();
            self::$resolver->register('php', fn() => new PhpEngine($filesystem));
            self::$compiler = new BladeCompiler(
                $filesystem,
                APP_ROOT . '/cache/views'
            );

            self::$resolver->register('blade', function () {
                return new CompilerEngine(self::$compiler);
            });

            self::$finder = new FileViewFinder($filesystem, [
                APP_ROOT . '/resources/views'
            ]);

            self::$factory = new Factory(
                self::$resolver,
                self::$finder,
                $dispatcher
            );
        }
    }

    public static function render($view, $data = []): string
    {
        self::init();

        $start = microtime(true);
        $output = self::$factory->make($view, $data)->render();
        $end = microtime(true);

        // Log rendered view
        self::$renderedViews[] = [
            'name' => $view,
            'time' => ($end - $start) * 1000, // v ms
            'size' => strlen($output),
            'data' => $data,
        ];

        return $output;
    }

    public static function exists($view): bool
    {
        self::init();
        return self::$factory->exists($view);
    }

    public static function share($key, $value = null)
    {
        self::init();
        return self::$factory->share($key, $value);
    }

    public static function getFactory(): Factory
    {
        self::init();
        return self::$factory;
    }
}