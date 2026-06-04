<?php

class modService {

    public function __construct(private modRepository $modRepository) {}

    public function authenticate(string $username, string $password): bool {
        $mod = $this->modRepository->findByUsername($username);
        if ($mod === null) {
            return false;
        }
        return password_verify($password, $mod['password_hash']);
    }

    public function getModInfo(string $username): ?array {
        return $this->modRepository->findByUsername($username);
    }

    public function changePassword(string $username, string $currentPassword, string $newPassword): bool {
        $mod = $this->modRepository->findByUsername($username);
        if ($mod === null || !password_verify($currentPassword, $mod['password_hash'])) {
            return false;
        }
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->modRepository->updatePasswordHash($username, $hash);
        return true;
    }

    public function getAllMods(): array {
        return $this->modRepository->getAllMods();
    }

    public function createMod(string $username, string $role, string $password): ?string {
        if (!preg_match('/^[a-zA-Z0-9_]{1,50}$/', $username)) {
            return 'Username may only contain letters, numbers, and underscores (max 50 chars).';
        }
        if (!in_array($role, ['admin', 'mod'], true)) {
            return 'Invalid role.';
        }
        if ($this->modRepository->findByUsername($username) !== null) {
            return 'Username already exists.';
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->modRepository->createMod($username, $role, $hash);
        return null;
    }

    public function deleteModsByIds(array $ids, string $currentUsername): ?string {
        $self = $this->modRepository->findByUsername($currentUsername);
        if ($self !== null) {
            $selfId = (int) $self['id'];
            foreach (array_map('intval', $ids) as $id) {
                if ($id === $selfId) {
                    return 'You cannot delete your own account.';
                }
            }
        }
        $this->modRepository->deleteByIds($ids);
        return null;
    }

    public function adminSetPassword(string $targetUsername, string $newPassword): void {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->modRepository->updatePasswordHash($targetUsername, $hash);
    }
}


