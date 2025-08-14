<?php

namespace Core\Cache;

use Core\Facades\Container;
use Core\State\RedisContainer;
use Doctrine\ORM\EntityManager;
use Predis\Client;

class CacheManager
{
    private static ?Client $redis = null;
    private static ?EntityManager $entityManager = null;

    /**
     * Inicializuje CacheManager
     */
    public static function init(): void
    {
        self::$redis = Container::get('redis');
        self::$entityManager = Container::get('doctrine.em');
    }

    /**
     * Vymaže všechny typy cache
     */
    public static function clearAllCache(): array
    {
        $results = [
            'file_cache' => false,
            'redis_cache' => false,
            'orm_cache' => false,
            'opcache' => false,
            'session_cache' => false
        ];

        try {
            // 1. Vymazání souborové cache
            $results['file_cache'] = self::clearFileCache();

            // 2. Vymazání Redis cache
            $results['redis_cache'] = self::clearRedisCache();

            // 3. Vymazání ORM cache
            $results['orm_cache'] = self::clearOrmCache();

            // 4. Vymazání OPcache
            $results['opcache'] = self::clearOpCache();

            // 5. Vymazání session cache
            $results['session_cache'] = self::clearSessionCache();

        } catch (\Exception $e) {
            error_log("Chyba při vymazání cache: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Vymaže souborovou cache
     */
    public static function clearFileCache(): bool
    {
        try {
            $cacheDir = APP_CONFIGURATION['cache_dir'];
            $success = true;

            if (is_dir($cacheDir)) {
                $deleteFilesOnly = function($dir) use (&$deleteFilesOnly, &$success) {
                    if (!is_dir($dir)) {
                        return;
                    }

                    $files = array_diff(scandir($dir), ['.', '..']);

                    foreach ($files as $file) {
                        $path = $dir . DIRECTORY_SEPARATOR . $file;

                        if (is_dir($path)) {
                            $deleteFilesOnly($path);
                        } else {
                            if (!unlink($path)) {
                                $success = false;
                            }
                        }
                    }
                };

                $deleteFilesOnly($cacheDir);
            }

            return $success;
        } catch (\Exception $e) {
            error_log("Chyba při vymazání souborové cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vymaže Redis cache
     */
    public static function clearRedisCache(): bool
    {
        try {
            if (self::$redis) {
                self::$redis->flushdb();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Chyba při vymazání Redis cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vymaže ORM cache
     */
    public static function clearOrmCache(): bool
    {
        try {
            if (self::$entityManager) {
                $config = self::$entityManager->getConfiguration();
                
                // Vymazání metadata cache
                if ($config->getMetadataCache()) {
                    $config->getMetadataCache()->clear();
                }
                
                // Vymazání query cache
                if ($config->getQueryCache()) {
                    $config->getQueryCache()->clear();
                }
                
                // Vymazání result cache
                if ($config->getResultCache()) {
                    $config->getResultCache()->clear();
                }

                return true;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Chyba při vymazání ORM cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vymaže OPcache
     */
    public static function clearOpCache(): bool
    {
        try {
            if (function_exists('opcache_reset')) {
                return opcache_reset();
            }
            return true; // OPcache není dostupný, považujeme za úspěch
        } catch (\Exception $e) {
            error_log("Chyba při vymazání OPcache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vymaže session cache
     */
    public static function clearSessionCache(): bool
    {
        try {
            RedisContainer::clearSessionCache();
            return true;
        } catch (\Exception $e) {
            error_log("Chyba při vymazání session cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Získá statistiky cache
     */
    public static function getCacheStats(): array
    {
        $stats = [
            'file_cache' => self::getFileCacheStats(),
            'redis_cache' => self::getRedisCacheStats(),
            'orm_cache' => self::getOrmCacheStats(),
            'opcache' => self::getOpCacheStats(),
            'session_cache' => self::getSessionCacheStats()
        ];

        return $stats;
    }

    /**
     * Získá statistiky souborové cache
     */
    private static function getFileCacheStats(): array
    {
        $cacheDir = APP_CONFIGURATION['cache_dir'];
        $fileCount = 0;
        $totalSize = 0;

        if (is_dir($cacheDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $fileCount++;
                    $totalSize += $file->getSize();
                }
            }
        }

        return [
            'file_count' => $fileCount,
            'total_size' => $totalSize,
            'formatted_size' => self::formatBytes($totalSize)
        ];
    }

    /**
     * Získá statistiky Redis cache
     */
    private static function getRedisCacheStats(): array
    {
        try {
            if (self::$redis) {
                $info = self::$redis->info();
                $keys = self::$redis->dbsize();

                return [
                    'keys' => $keys,
                    'memory_usage' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'uptime' => $info['uptime_in_seconds'] ?? 0
                ];
            }
        } catch (\Exception $e) {
            error_log("Chyba při získávání Redis statistik: " . $e->getMessage());
        }

        return [
            'keys' => 0,
            'memory_usage' => 'N/A',
            'connected_clients' => 0,
            'uptime' => 0
        ];
    }

    /**
     * Získá statistiky ORM cache
     */
    private static function getOrmCacheStats(): array
    {
        try {
            if (self::$entityManager && self::$redis) {
                $config = self::$entityManager->getConfiguration();
                
                $metadataKeys = self::$redis->keys('doctrine_metadata*');
                $queryKeys = self::$redis->keys('doctrine_query*');
                $resultKeys = self::$redis->keys('doctrine_result*');

                return [
                    'metadata_cache_keys' => count($metadataKeys),
                    'query_cache_keys' => count($queryKeys),
                    'result_cache_keys' => count($resultKeys),
                    'total_orm_keys' => count($metadataKeys) + count($queryKeys) + count($resultKeys)
                ];
            }
        } catch (\Exception $e) {
            error_log("Chyba při získávání ORM statistik: " . $e->getMessage());
        }

        return [
            'metadata_cache_keys' => 0,
            'query_cache_keys' => 0,
            'result_cache_keys' => 0,
            'total_orm_keys' => 0
        ];
    }

    /**
     * Získá statistiky OPcache
     */
    private static function getOpCacheStats(): array
    {
        try {
            if (function_exists('opcache_get_status')) {
                $status = opcache_get_status();
                if ($status) {
                    return [
                        'enabled' => true,
                        'memory_usage' => $status['memory_usage']['used_memory'] ?? 0,
                        'memory_usage_formatted' => self::formatBytes($status['memory_usage']['used_memory'] ?? 0),
                        'hit_rate' => $status['opcache_statistics']['opcache_hit_rate'] ?? 0,
                        'cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log("Chyba při získávání OPcache statistik: " . $e->getMessage());
        }

        return [
            'enabled' => false,
            'memory_usage' => 0,
            'memory_usage_formatted' => '0 B',
            'hit_rate' => 0,
            'cached_scripts' => 0
        ];
    }

    /**
     * Získá statistiky session cache
     */
    private static function getSessionCacheStats(): array
    {
        try {
            $allKeys = RedisContainer::all();
            $sessionKeys = array_filter(array_keys($allKeys), function($key) {
                return str_starts_with($key, 'toast_') || str_starts_with($key, 'session_');
            });

            return [
                'session_keys' => count($sessionKeys),
                'total_container_keys' => count($allKeys)
            ];
        } catch (\Exception $e) {
            error_log("Chyba při získávání session statistik: " . $e->getMessage());
            return [
                'session_keys' => 0,
                'total_container_keys' => 0
            ];
        }
    }

    /**
     * Formátuje bajty do čitelné podoby
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
