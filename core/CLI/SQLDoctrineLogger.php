<?php
namespace Core\CLI;

use Doctrine\DBAL\Logging\Middleware as DbalLoggingMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class SQLDoctrineLogger
{
    public static function new(CustomSQLLogger $logger): DbalLoggingMiddleware
    {
        return new DbalLoggingMiddleware(
            new class($logger) implements LoggerInterface {
                public function __construct(private readonly CustomSQLLogger $logger) {}

                public function log($level, $message, array $context = []): void
                {
                    $sql    = $context['sql'] ?? null;
                    $params = $context['params'] ?? null;
                    $types  = $context['types'] ?? null;

                    if ($sql) {
                        $this->logger->startQuery($sql, $params, $types);
                    }
                }

                public function emergency($message, array $context = []): void { $this->log(LogLevel::EMERGENCY, $message, $context); }
                public function alert($message, array $context = []): void     { $this->log(LogLevel::ALERT, $message, $context); }
                public function critical($message, array $context = []): void  { $this->log(LogLevel::CRITICAL, $message, $context); }
                public function error($message, array $context = []): void     { $this->log(LogLevel::ERROR, $message, $context); }
                public function warning($message, array $context = []): void   { $this->log(LogLevel::WARNING, $message, $context); }
                public function notice($message, array $context = []): void    { $this->log(LogLevel::NOTICE, $message, $context); }
                public function info($message, array $context = []): void      { $this->log(LogLevel::INFO, $message, $context); }
                public function debug($message, array $context = []): void     { $this->log(LogLevel::DEBUG, $message, $context); }
            }
        );
    }
}