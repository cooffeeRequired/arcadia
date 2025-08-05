<?php

namespace Core\State;

use PDO;
use ReturnTypeWillChange;
use SessionHandlerInterface;

class DBSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function open(string $path, mixed $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $row['data'] : '';
    }

    public function write($id, $data): bool
    {
        $stmt = $this->pdo->prepare("
            REPLACE INTO sessions (id, data, last_activity) VALUES (:id, :data, NOW())
        ");
        return $stmt->execute(['id' => $id, 'data' => $data]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    #[ReturnTypeWillChange] public function gc($max_lifetime): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_activity < NOW() - INTERVAL :lifetime SECOND");
        return $stmt->execute(['lifetime' => $max_lifetime]);
    }
}