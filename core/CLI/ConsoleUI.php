<?php

namespace Core\CLI;

final class ConsoleUI
{
    private const array COLORS = [
        'reset' => "\033[0m",
        'bold' => "\033[1m",
        'dim' => "\033[2m",

        'fg' => [
            'default' => "\033[39m",
            'black' => "\033[30m",
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'magenta' => "\033[35m",
            'cyan' => "\033[36m",
            'white' => "\033[37m",
            'gray' => "\033[90m",
        ],
        'bg' => [
            'default' => "\033[49m",
            'black' => "\033[40m",
            'red' => "\033[41m",
            'green' => "\033[42m",
            'yellow' => "\033[43m",
            'blue' => "\033[44m",
            'magenta' => "\033[45m",
            'cyan' => "\033[46m",
            'white' => "\033[47m",
        ],
    ];

    public static function color(
        string $text,
        ?string $fg = null,
        ?string $bg = null,
        bool $bold = false,
        bool $dim = false
    ): string {
        $seq = '';
        if ($bold) $seq .= self::COLORS['bold'];
        if ($dim) $seq .= self::COLORS['dim'];
        if ($fg && isset(self::COLORS['fg'][$fg])) $seq .= self::COLORS['fg'][$fg];
        if ($bg && isset(self::COLORS['bg'][$bg])) $seq .= self::COLORS['bg'][$bg];
        return $seq . $text . self::COLORS['reset'];
    }

    public static function ok(string $t): string     { return "✅ " . $t; }
    public static function warn(string $t): string   { return "⚠️  " . $t; }
    public static function err(string $t): string    { return "❌ " . $t; }
    public static function info(string $t): string   { return "ℹ️  " . $t; }
    public static function bullet(string $t): string { return "•  " . $t; }
    public static function subtle(string $t): string { return self::color($t, 'gray'); }
    public static function strong(string $t): string { return self::color($t, null, null, true); }


    public static function section(string $title, ?string $tag = null): void
    {
        $line = str_repeat('─', max(8, 60 - mb_strlen($title)));
        $head = self::strong(self::color($title, 'cyan'));
        $suffix = $tag ? ' ' . self::color("{$tag}", 'gray') : '';
        echo "{$head} {$line}{$suffix}\n";
    }

    public static function kv(string $key, string $value, int $keyWidth = 0): void
    {
        $k = self::color(str_pad($key, $keyWidth), 'gray');
        echo "  {$k} " . $value . "\n";
    }

    public static function items(array $lines): void
    {
        foreach ($lines as $l) echo "  " . self::bullet($l) . "\n";
    }

    public static function table(array $rows, ?array $headers = null): void
    {
        if ($headers) array_unshift($rows, $headers);

        $widths = [];
        foreach ($rows as $r) {
            foreach ($r as $i => $c) {
                $widths[$i] = max($widths[$i] ?? 0, self::vlen((string)$c));
            }
        }

        $border = '─';
        $line = '  ' . self::subtle(str_repeat($border, array_sum($widths) + (3 * count($widths)) - 1));
        foreach ($rows as $i => $r) {
            $out = '  ';
            foreach ($r as $j => $c) {
                $cell = (string)$c;
                $cell = self::padRightVisible($cell, $widths[$j]);
                $out .= $cell . '   ';
            }
            if ($headers && $i === 0) {
                echo self::strong($out) . "\n";
                echo $line . "\n";
            } else {
                echo $out . "\n";
            }
        }
    }

    public static function box(string $title, string|array $content = '', string $variant = 'neutral', int $padX = 1, int $padY = 0): void
    {
        $palette = match ($variant) {
            'success' => ['fg' => 'green',   'border' => 'green'],
            'warning' => ['fg' => 'yellow',  'border' => 'yellow'],
            'error'   => ['fg' => 'red',     'border' => 'red'],
            'info'    => ['fg' => 'cyan',    'border' => 'cyan'],
            default   => ['fg' => 'white',   'border' => 'gray'],
        };

        $lines = is_array($content) ? $content : (strlen($content) ? explode("\n", $content) : []);
        $maxInner = self::vlen($title);
        foreach ($lines as $l) $maxInner = max($maxInner, self::vlen($l));

        $innerW = $maxInner + ($padX * 2);
        $top = "┌" . str_repeat("─", $innerW) . "┐";
        $btm = "└" . str_repeat("─", $innerW) . "┘";

        $titleRaw = self::truncateVisible($title, $innerW - $padX * 2);
        $titleLineInner = str_repeat(' ', $padX) . $titleRaw;
        $titleLineInner = self::padRightVisible($titleLineInner, $innerW);
        $titleLine = "│" . $titleLineInner . "│";

        echo self::color($top, $palette['border']) . "\n";
        echo self::color($titleLine, $palette['fg'], null, true) . "\n";

        for ($i = 0; $i < $padY; $i++) {
            echo self::color("│" . str_repeat(' ', $innerW) . "│", $palette['border']) . "\n";
        }

        foreach ($lines as $l) {
            $l = self::truncateVisible($l, $innerW - $padX * 2);
            $inner = str_repeat(' ', $padX) . $l;
            $inner = self::padRightVisible($inner, $innerW);
            echo self::color("│" . $inner . "│", $palette['border']) . "\n";
        }

        for ($i = 0; $i < $padY; $i++) {
            echo self::color("│" . str_repeat(' ', $innerW) . "│", $palette['border']) . "\n";
        }
        echo self::color($btm, $palette['border']) . "\n";
    }

    private static function stripAnsi(string $s): string {
        return preg_replace('/\033\[[0-9;]*m/u', '', $s) ?? $s;
    }

    private static function vlen(string $s): int {
        return mb_strlen(self::stripAnsi($s));
    }

    private static function padRightVisible(string $s, int $targetLen): string
    {
        $len = self::vlen($s);
        if ($len >= $targetLen) return $s;
        return $s . str_repeat(' ', $targetLen - $len);
    }

    private static function truncateVisible(string $s, int $targetLen): string {
        $plain = self::stripAnsi($s);
        if (mb_strlen($plain) <= $targetLen) return $s;
        return mb_substr($plain, 0, max(0, $targetLen - 1)) . '…';
    }
}