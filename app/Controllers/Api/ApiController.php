<?php

namespace App\Controllers\Api;

class ApiController
{
    public function checkUpdates(): void
    {
        header('Content-Type: application/json');
        
        // Kontrola změn v klíčových souborech
        $files = [
            __DIR__ . '/../../resources/views/**/*.blade.php',
            __DIR__ . '/../../resources/css/_app.css',
            __DIR__ . '/../../public/css/_app.css',
            __DIR__ . '/../../app/Controllers/*.php',
            __DIR__ . '/../../core/*.php'
        ];
        
        $maxModified = 0;
        foreach ($files as $pattern) {
            $matches = glob($pattern);
            foreach ($matches as $file) {
                $modified = filemtime($file);
                if ($modified > $maxModified) {
                    $maxModified = $modified;
                }
            }
        }
        
        echo json_encode([
            'modified' => $maxModified,
            'timestamp' => time()
        ]);
    }
} 