<?php

class pasteService {

    public function __construct(private pasteRepository $pasteRepository) {}

    public function createPaste(string $title, string $content, int $timeToLive, string $ipAddress): string {
        $uuid = $this->generateUuid();
        $this->pasteRepository->insertPaste($uuid, $title, $content, $timeToLive, $ipAddress);
        return $uuid;
    }

    public function findByUuid(string $uuid): ?array {
        return $this->pasteRepository->findByUuid($uuid);
    }

    public function countPastes(): int {
        return $this->pasteRepository->countAll();
    }

    public function isFloodedByIp(string $ip, int $maxPastes, int $windowSeconds): bool {
        return $this->pasteRepository->countRecentByIp($ip, $windowSeconds) >= $maxPastes;
    }

    public function getPageOfPastes(int $page, int $perPage): array {
        $offset = ($page - 1) * $perPage;
        return $this->pasteRepository->getPaginatedPastes($perPage, $offset);
    }

    public function deletePastesByIds(array $ids): array {
        $ids = array_values(array_filter(array_map('intval', $ids), fn(int $id) => $id > 0));
        if (empty($ids)) {
            return [];
        }
        $deleted = $this->pasteRepository->findByIds($ids);
        $this->pasteRepository->deleteByIds($ids);
        return $deleted;
    }

    private function generateUuid(): string {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
