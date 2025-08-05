<?php

namespace Core\Logging;

use Tracy\IBarPanel;
use Core\Facades\Container;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Predis\Client;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Custom panel pro cache s reálnými daty
 */
class CachePanel implements IBarPanel
{
    private ?RedisAdapter $redisCache = null;
    private ?Client $redisClient = null;

    public function __construct()
    {
        try {
            // Pokus o získání Redis cache z containeru
            $this->redisCache = Container::get('cache');
            $this->redisClient = Container::get('redis');
        } catch (\Exception $e) {
            // Pokud není dostupný, pokusíme se vytvořit nové připojení
            try {
                $this->redisClient = new Client('tcp://127.0.0.1:6379');
                $this->redisCache = new RedisAdapter($this->redisClient);
            } catch (\Exception $e) {
                // Redis není dostupný
            }
        }
    }

    public function getTab(): string
    {
        $stats = $this->getCacheStats();
        $totalHits = $stats['totalHits'];
        $totalMisses = $stats['totalMisses'];
        $hitRate = $totalHits + $totalMisses > 0 ? ($totalHits / ($totalHits + $totalMisses)) * 100 : 0;

        return '<span title="Cache">⚡ Cache</span>' .
            '<span class="tracy-label">' . number_format($hitRate, 1) . '% hit / ' . $totalHits . ' hits</span>';
    }

    public function getPanel(): string
    {
        $cacheStats = $this->getDetailedCacheStats();
        $stats = $this->getCacheStats();
        
        $totalHits = $stats['totalHits'];
        $totalMisses = $stats['totalMisses'];
        $hitRate = $totalHits + $totalMisses > 0 ? ($totalHits / ($totalHits + $totalMisses)) * 100 : 0;

        $panel = '<h1>⚡ Cache Statistics</h1>
                <div class="tracy-inner">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . number_format($hitRate, 1) . '%</div>
                            <div style="font-size: 12px; opacity: 0.8;">Hit rate</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . $totalHits . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Total hits</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; flex: 1;">
                            <div style="font-size: 24px; font-weight: bold;">' . $totalMisses . '</div>
                            <div style="font-size: 12px; opacity: 0.8;">Total misses</div>
                        </div>
                    </div>
                    <table class="tracy-sortable">
                        <thead>
                            <tr>
                                <th>Typ</th>
                                <th>Hits</th>
                                <th>Misses</th>
                                <th>Velikost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($cacheStats as $stat) {
            $statusClass = match($stat['status']) {
                'active' => 'tracy-success',
                'inactive' => 'tracy-warning',
                'unavailable' => 'tracy-error',
                'error' => 'tracy-error',
                default => 'tracy-error'
            };
            $statusText = match($stat['status']) {
                'active' => 'Aktivní',
                'inactive' => 'Neaktivní',
                'unavailable' => 'Nedostupné',
                'error' => 'Chyba',
                default => 'Neznámý'
            };
            
            $panel .= '<tr>
                        <td>' . htmlspecialchars($stat['type']) . '</td>
                        <td>' . $stat['hits'] . '</td>
                        <td>' . $stat['misses'] . '</td>
                        <td>' . $stat['size'] . '</td>
                        <td><span class="' . $statusClass . '">' . $statusText . '</span></td>
                       </tr>';
        }

        $panel .= '</tbody></table></div>';
        
        // Přidání informací o cache klíčích
        if ($this->redisClient) {
            $panel .= $this->getRedisKeysInfo();
        }
        
        $panel .= $this->getDoctrineCacheInfo();
        $panel .= $this->getOPCacheInfo();

        return $panel;
    }

    private function getCacheStats(): array
    {
        $totalHits = 0;
        $totalMisses = 0;

        // OPcache stats
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status(false);
            if ($opcacheStatus) {
                $totalHits += $opcacheStatus['opcache_statistics']['hits'] ?? 0;
                $totalMisses += $opcacheStatus['opcache_statistics']['misses'] ?? 0;
            }
        }

        // Redis stats (pokud je dostupný)
        if ($this->redisClient) {
            try {
                $info = $this->redisClient->info('stats');
                $totalHits += $info['keyspace_hits'] ?? 0;
                $totalMisses += $info['keyspace_misses'] ?? 0;
            } catch (\Exception $e) {
                // Redis není dostupný
            }
        }

        return [
            'totalHits' => $totalHits,
            'totalMisses' => $totalMisses
        ];
    }

    private function getDetailedCacheStats(): array
    {
        $stats = [];

        // OPcache
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status(false);
            if ($opcacheStatus) {
                $opcacheStats = $opcacheStatus['opcache_statistics'];
                $memoryUsage = $opcacheStatus['memory_usage'];
                
                $stats[] = [
                    'type' => 'OPcache',
                    'hits' => $opcacheStats['hits'] ?? 0,
                    'misses' => $opcacheStats['misses'] ?? 0,
                    'size' => $this->formatBytes($memoryUsage['used_memory'] ?? 0),
                    'status' => 'active'
                ];
            } else {
                $stats[] = [
                    'type' => 'OPcache',
                    'hits' => 0,
                    'misses' => 0,
                    'size' => '0 B',
                    'status' => 'inactive'
                ];
            }
        } else {
            $stats[] = [
                'type' => 'OPcache',
                'hits' => 0,
                'misses' => 0,
                'size' => 'N/A',
                'status' => 'unavailable'
            ];
        }

        // Redis
        if ($this->redisClient) {
            try {
                $info = $this->redisClient->info();
                $keyspaceHits = $info['keyspace_hits'] ?? 0;
                $keyspaceMisses = $info['keyspace_misses'] ?? 0;
                $usedMemory = $info['used_memory'] ?? 0;
                $dbSize = $this->redisClient->dbsize();
                
                $stats[] = [
                    'type' => 'Redis',
                    'hits' => $keyspaceHits,
                    'misses' => $keyspaceMisses,
                    'size' => $this->formatBytes($usedMemory) . ' (' . $dbSize . ' klíčů)',
                    'status' => 'active'
                ];
            } catch (\Exception $e) {
                $stats[] = [
                    'type' => 'Redis',
                    'hits' => 0,
                    'misses' => 0,
                    'size' => 'N/A',
                    'status' => 'error'
                ];
            }
        } else {
            $stats[] = [
                'type' => 'Redis',
                'hits' => 0,
                'misses' => 0,
                'size' => 'N/A',
                'status' => 'unavailable'
            ];
        }

        // Doctrine cache (pokud je dostupný)
        try {
            $em = Container::get('doctrine.em');
            $config = $em->getConfiguration();
            
            // Kontrola, zda jsou cache nastavené
            $hasMetadataCache = $config->getMetadataCache() !== null;
            $hasQueryCache = $config->getQueryCache() !== null;
            $hasResultCache = $config->getResultCache() !== null;
            
            if ($hasMetadataCache || $hasQueryCache || $hasResultCache) {
                // Pokus o získání statistik z Redis (pokud používá Redis)
                $doctrineHits = 0;
                $doctrineMisses = 0;
                
                if ($this->redisClient) {
                    try {
                        // Získání Doctrine cache klíčů z Redis
                        $doctrineKeys = $this->redisClient->keys('doctrine*');
                        $doctrineKeyCount = count($doctrineKeys);
                        
                        // Odhad velikosti na základě klíčů
                        $estimatedSize = $doctrineKeyCount * 1024; // Přibližně 1KB na klíč
                        
                        $stats[] = [
                            'type' => 'Doctrine',
                            'hits' => $doctrineHits,
                            'misses' => $doctrineMisses,
                            'size' => $this->formatBytes($estimatedSize) . ' (' . $doctrineKeyCount . ' klíčů)',
                            'status' => 'active'
                        ];
                    } catch (\Exception $e) {
                        $stats[] = [
                            'type' => 'Doctrine',
                            'hits' => $doctrineHits,
                            'misses' => $doctrineMisses,
                            'size' => 'N/A',
                            'status' => 'active'
                        ];
                    }
                } else {
                    $stats[] = [
                        'type' => 'Doctrine',
                        'hits' => $doctrineHits,
                        'misses' => $doctrineMisses,
                        'size' => 'N/A',
                        'status' => 'active'
                    ];
                }
            } else {
                $stats[] = [
                    'type' => 'Doctrine',
                    'hits' => 0,
                    'misses' => 0,
                    'size' => 'N/A',
                    'status' => 'inactive'
                ];
            }
        } catch (\Exception $e) {
            $stats[] = [
                'type' => 'Doctrine',
                'hits' => 0,
                'misses' => 0,
                'size' => 'N/A',
                'status' => 'unavailable'
            ];
        }

        // File cache
        $cacheDir = APP_CONFIGURATION['cache_dir'] ?? __DIR__ . '/../../cache';
        if (is_dir($cacheDir)) {
            $cacheSize = $this->getDirectorySize($cacheDir);
            $stats[] = [
                'type' => 'File Cache',
                'hits' => 0, // File cache neposkytuje statistiky
                'misses' => 0,
                'size' => $this->formatBytes($cacheSize),
                'status' => 'active'
            ];
        } else {
            $stats[] = [
                'type' => 'File Cache',
                'hits' => 0,
                'misses' => 0,
                'size' => 'N/A',
                'status' => 'unavailable'
            ];
        }

        return $stats;
    }

    private function getRedisKeysInfo(): string
    {
        if (!$this->redisClient) {
            return '';
        }

        try {
            $keys = $this->redisClient->keys('*');
            $keyCount = count($keys);
            
            // Získání vzorku klíčů pro zobrazení
            $sampleKeys = array_slice($keys, 0, 10);
            $keyTypes = [];
            
            foreach ($sampleKeys as $key) {
                $type = $this->redisClient->type($key);
                $keyTypes[$type] = ($keyTypes[$type] ?? 0) + 1;
            }
            
            $info = '<h3>Redis Keys (' . $keyCount . ')</h3>';
            $info .= '<div style="margin-bottom: 15px;">';
            
            foreach ($keyTypes as $type => $count) {
                $info .= '<span style="background: rgba(255,255,255,0.1); padding: 5px 10px; margin: 2px; border-radius: 5px; font-size: 12px;">' . 
                         $type . ': ' . $count . '</span>';
            }
            
            $info .= '</div>';
            
            return $info;
        } catch (\Exception $e) {
            return '<p><em>Redis keys info není dostupné</em></p>';
        }
    }

    private function getDoctrineCacheInfo(): string
    {
        try {
            $em = Container::get('doctrine.em');
            $config = $em->getConfiguration();
            
            $hasMetadataCache = $config->getMetadataCache() !== null;
            $hasQueryCache = $config->getQueryCache() !== null;
            $hasResultCache = $config->getResultCache() !== null;
            
            if (!$hasMetadataCache && !$hasQueryCache && !$hasResultCache) {
                return '<p><em>Doctrine cache není nakonfigurován</em></p>';
            }
            
            $info = '<h3>Doctrine Cache Details</h3>';
            $info .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px;">';
            
            // Metadata Cache
            $info .= '<div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px;">';
            $info .= '<strong>Metadata Cache:</strong> ' . ($hasMetadataCache ? '✅ Aktivní' : '❌ Neaktivní') . '<br>';
            if ($hasMetadataCache && $this->redisClient) {
                $metadataKeys = $this->redisClient->keys('doctrine_metadata*');
                $info .= '<strong>Klíče:</strong> ' . count($metadataKeys) . '<br>';
            }
            $info .= '</div>';
            
            // Query Cache
            $info .= '<div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px;">';
            $info .= '<strong>Query Cache:</strong> ' . ($hasQueryCache ? '✅ Aktivní' : '❌ Neaktivní') . '<br>';
            if ($hasQueryCache && $this->redisClient) {
                $queryKeys = $this->redisClient->keys('doctrine_query*');
                $info .= '<strong>Klíče:</strong> ' . count($queryKeys) . '<br>';
            }
            $info .= '</div>';
            
            // Result Cache
            $info .= '<div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px;">';
            $info .= '<strong>Result Cache:</strong> ' . ($hasResultCache ? '✅ Aktivní' : '❌ Neaktivní') . '<br>';
            if ($hasResultCache && $this->redisClient) {
                $resultKeys = $this->redisClient->keys('doctrine_result*');
                $info .= '<strong>Klíče:</strong> ' . count($resultKeys) . '<br>';
            }
            $info .= '</div>';
            
            $info .= '</div>';
            
            // Přehled všech Doctrine klíčů
            if ($this->redisClient) {
                try {
                    $allDoctrineKeys = $this->redisClient->keys('doctrine*');
                    if (!empty($allDoctrineKeys)) {
                        $info .= '<h4>Doctrine Cache Keys</h4>';
                        $info .= '<div style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.1); padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px;">';
                        foreach (array_slice($allDoctrineKeys, 0, 20) as $key) {
                            $info .= htmlspecialchars($key) . '<br>';
                        }
                        if (count($allDoctrineKeys) > 20) {
                            $info .= '... a dalších ' . (count($allDoctrineKeys) - 20) . ' klíčů';
                        }
                        $info .= '</div>';
                    }
                } catch (\Exception $e) {
                    $info .= '<p><em>Nelze načíst Doctrine cache klíče</em></p>';
                }
            }
            
            return $info;
        } catch (\Exception $e) {
            return '<p><em>Doctrine cache info není dostupné</em></p>';
        }
    }

    private function getOPCacheInfo(): string
    {
        if (!function_exists('opcache_get_status')) {
            return '<p><em>OPcache není dostupný</em></p>';
        }

        $status = opcache_get_status(false);
        if (!$status) {
            return '<p><em>OPcache není aktivní</em></p>';
        }

        $stats = $status['opcache_statistics'];
        $memory = $status['memory_usage'];
        
        $info = '<h3>OPcache Details</h3>';
        $info .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px;">';
        $info .= '<div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px;">';
        $info .= '<strong>Hit Rate:</strong> ' . number_format(($stats['hits'] / ($stats['hits'] + $stats['misses'])) * 100, 1) . '%<br>';
        $info .= '<strong>Memory Used:</strong> ' . $this->formatBytes($memory['used_memory']) . '<br>';
        $info .= '<strong>Memory Free:</strong> ' . $this->formatBytes($memory['free_memory']) . '<br>';
        $info .= '<strong>Cached Files:</strong> ' . $stats['num_cached_scripts'] . '<br>';
        $info .= '<strong>Cached Keys:</strong> ' . $stats['num_cached_keys'] . '<br>';
        $info .= '<strong>Max Keys:</strong> ' . $stats['max_cached_keys'];
        $info .= '</div></div>';

        return $info;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function getDirectorySize(string $path): int
    {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
}