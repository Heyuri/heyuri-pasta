<?php

use Puchiko\database\baseRepository;
use Puchiko\database\databaseConnection;

class pasteRepository extends baseRepository {

    public function __construct(databaseConnection $db) {
        parent::__construct($db, 'pastes');
    }

    public function findByUuid(string $uuid): ?array {
        return $this->findBy('uuid', $uuid);
    }

    public function insertPaste(string $uuid, string $title, string $content, int $timeToLive, string $ipAddress): void {
        $this->insert([
            'uuid'         => $uuid,
            'title'        => $title,
            'content'      => $content,
            'time_to_live' => $timeToLive,
            'ip_address'   => $ipAddress,
        ]);
    }

    public function countAll(): int {
        return $this->count();
    }

    public function countRecentByIp(string $ip, int $windowSeconds): int {
        $query = "SELECT COUNT(*) FROM {$this->table}
                  WHERE ip_address = :ip
                  AND created_at >= NOW() - INTERVAL :window SECOND";
        return (int) $this->databaseConnection->fetchColumn($query, [
            ':ip'     => $ip,
            ':window' => $windowSeconds,
        ]);
    }

    public function getPaginatedPastes(int $limit, int $offset): array {
        $query = "SELECT id, uuid, title, ip_address, created_at, time_to_live,
                         SUBSTRING(content, 1, 120) AS content_preview
                  FROM {$this->table}
                  ORDER BY created_at DESC
                  LIMIT :limit OFFSET :offset";
        return $this->databaseConnection->fetchAllAsArray($query, [
            ':limit'  => $limit,
            ':offset' => $offset,
        ]);
    }

    public function findByIds(array $ids): array {
        if (empty($ids)) {
            return [];
        }
        $ids          = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query        = "SELECT id, uuid, title, ip_address, created_at FROM {$this->table} WHERE id IN ({$placeholders})";
        return $this->databaseConnection->fetchAllAsArray($query, array_values($ids));
    }

    public function deleteByIds(array $ids): void {
        if (empty($ids)) {
            return;
        }
        $ids          = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query        = "DELETE FROM {$this->table} WHERE id IN ({$placeholders})";
        $this->databaseConnection->execute($query, array_values($ids));
    }
}
