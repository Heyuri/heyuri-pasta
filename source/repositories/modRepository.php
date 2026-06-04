<?php

use Puchiko\database\baseRepository;
use Puchiko\database\databaseConnection;

class modRepository extends baseRepository {

    public function __construct(databaseConnection $db) {
        parent::__construct($db, 'mods');
    }

    public function findByUsername(string $username): ?array {
        return $this->findBy('username', $username);
    }

    public function updatePasswordHash(string $username, string $passwordHash): void {
        $this->updateWhere(['password_hash' => $passwordHash], 'username', $username);
    }

    public function getAllMods(): array {
        $query = "SELECT id, username, role, created_at FROM {$this->table} ORDER BY id ASC";
        return $this->databaseConnection->fetchAllAsArray($query);
    }

    public function findModsByIds(array $ids): array {
        if (empty($ids)) {
            return [];
        }
        $ids          = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        return $this->databaseConnection->fetchAllAsArray(
            "SELECT id, username FROM {$this->table} WHERE id IN ({$placeholders})",
            array_values($ids)
        );
    }

    public function deleteByIds(array $ids): void {
        if (empty($ids)) {
            return;
        }
        $ids          = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->databaseConnection->execute(
            "DELETE FROM {$this->table} WHERE id IN ({$placeholders})",
            array_values($ids)
        );
    }

    public function createMod(string $username, string $role, string $passwordHash): void {
        $this->insert([
            'username'      => $username,
            'role'          => $role,
            'password_hash' => $passwordHash,
        ]);
    }
}
