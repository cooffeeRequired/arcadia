<?php

namespace Core\Render;

use Illuminate\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;

/**
 * Vlastní View Factory třída, která nezávisí na Laravel containeru
 */
class CustomViewFactory
{
    private EngineResolver $resolver;
    public FileViewFinder $finder;
    private Dispatcher $dispatcher;
    private array $shared = [];
    private array $sections = [];
    private array $stacks = [];
    public ?string $currentSection = null;
    private array $sectionContent = [];
    private ?string $layout = null;

    public function __construct(EngineResolver $resolver, FileViewFinder $finder, Dispatcher $dispatcher)
    {
        $this->resolver = $resolver;
        $this->finder = $finder;
        $this->dispatcher = $dispatcher;
    }

    public function make(string $view, array $data = []): CustomView
    {
        $path = $this->finder->find($view);
        $engine = $this->resolver->resolve($path);

        // Merge shared data with view data
        $data = array_merge($this->shared, $data);

        return new CustomView($this, $engine, $view, $path, $data);
    }

    public function exists(string $view): bool
    {
        try {
            $this->finder->find($view);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function share($key, $value = null): self
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } else {
            $this->shared[$key] = $value;
        }
        return $this;
    }

    public function getShared(): array
    {
        return $this->shared;
    }

    public function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function stopSection(): void
    {
        if ($this->currentSection !== null) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function yieldContent(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    public function startPush(string $name): void
    {
        if (!isset($this->stacks[$name])) {
            $this->stacks[$name] = [];
        }
        ob_start();
    }

    public function stopPush(): void
    {
        if (!empty($this->stacks)) {
            $content = ob_get_clean();
            $lastKey = array_key_last($this->stacks);
            $this->stacks[$lastKey][] = $content;
        }
    }

    public function yieldPushContent(string $name): string
    {
        if (!isset($this->stacks[$name])) {
            return '';
        }
        return implode('', $this->stacks[$name]);
    }

    public function startComponent(string $name, array $data = []): void
    {
        // Implementace pro @component
    }

    public function renderComponent(): string
    {
        // Implementace pro @endcomponent
        return '';
    }

    public function slot(string $name): void
    {
        // Implementace pro @slot
    }

    public function endSlot(): void
    {
        // Implementace pro @endslot
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }
}