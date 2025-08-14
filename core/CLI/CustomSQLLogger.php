<?php

namespace Core\CLI;

use Doctrine\DBAL\Logging\SQLLogger;

class CustomSQLLogger implements SQLLogger
{
	private int $queryCount = 0;
	private static bool $enabled = false;

	public static function enable(): void
	{
		self::$enabled = true;
	}

	public static function disable(): void
	{
		self::$enabled = false;
	}

	public function startQuery($sql, ?array $params = null, ?array $types = null): void
	{
		if (!self::$enabled) {
			return;
		}

		$this->queryCount++;
		echo $this->colorize("\r  |-> Query #{$this->queryCount}:", 'cyan') . "\n";
		echo $this->colorize("\r  |-> " . $this->formatSQL($sql), 'yellow') . "\n";

		if ($params) {
			echo $this->colorize("\r  |-> Params: " . json_encode($params, JSON_UNESCAPED_UNICODE), 'magenta') . "\n";
		}
		echo "\n";
	}

	public function stopQuery()
	{
		// No-op
	}

	private function formatSQL($sql): string
	{
		// Základní formátování SQL
		$sql = str_replace(['SELECT', 'FROM', 'WHERE', 'INSERT INTO', 'UPDATE', 'DELETE FROM', 'CREATE TABLE', 'DROP TABLE', 'ALTER TABLE', 'ADD COLUMN', 'ADD INDEX', 'ADD CONSTRAINT'],
			["\n     SELECT", "\n     FROM", "\n     WHERE", "\n     INSERT INTO", "\n     UPDATE", "\n     DELETE FROM", "\n     CREATE TABLE", "\n     DROP TABLE", "\n     ALTER TABLE", "\n     ADD COLUMN", "\n     ADD INDEX", "\n     ADD CONSTRAINT"], $sql);

		return trim($sql);
	}

	private function colorize($text, $color): string
	{
		$colors = [
			'red' => "\033[31m",
			'green' => "\033[32m",
			'yellow' => "\033[33m",
			'blue' => "\033[34m",
			'magenta' => "\033[35m",
			'cyan' => "\033[36m",
			'white' => "\033[37m",
			'bold' => "\033[1m",
			'reset' => "\033[0m"
		];

		return $colors[$color] . $text . $colors['reset'];
	}
}
