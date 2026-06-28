<?php

use Puchiko\template\templateEngine;

class moderateRoute {

    private const AUDIT_LOG = __DIR__ . '/../../private/audit.log';

    public function __construct(private routeContext $routeContext) {}

    public function invoke(): void {
        $req = $this->routeContext->request;

        // Refresh role from the DB *before* dispatching any action, so a
        // revoked/demoted session can never authorize a privileged POST on a
        // stale cached role.
        if ($this->isLoggedIn()) {
            $info = $this->routeContext->getModService()->getModInfo($_SESSION['mod_user']);
            $_SESSION['mod_role'] = is_array($info) ? ($info['role'] ?? 'mod') : 'mod';
        }

        if ($req->isPost()) {
            $action = $req->getParameter('action', 'POST', '');
            if ($action === 'login') {
                $this->handleLogin();
            } elseif ($action === 'logout') {
                $this->handleLogout();
            } elseif ($action === 'delete') {
                $this->handleDelete();
            } elseif ($action === 'changePassword') {
                $this->handleChangePassword();
            } elseif ($action === 'createMod') {
                $this->handleCreateMod();
            } elseif ($action === 'deleteAccounts') {
                $this->handleDeleteAccounts();
            } elseif ($action === 'adminSetPassword') {
                $this->handleAdminSetPassword();
            }
        }

        if (!$this->isLoggedIn()) {
            echo $this->routeContext->renderer->renderPage($this->renderLoginForm(), 'moderateRoute');
        } else {
            $subpage = $req->getParameter('subpage', 'GET', 'pastes');
            if ($subpage === 'account') {
                echo $this->routeContext->renderer->renderPage($this->renderAccountPage(), 'moderateRoute');
            } elseif ($subpage === 'accounts' && ($_SESSION['mod_role'] ?? '') === 'admin') {
                echo $this->routeContext->renderer->renderPage($this->renderAccountsPage(), 'moderateRoute');
            } else {
                echo $this->routeContext->renderer->renderPage($this->renderModPanel(), 'moderateRoute');
            }
        }
    }

    private function handleLogin(): void {
        $req      = $this->routeContext->request;
        $username = trim($req->getParameter('username', 'POST', ''));
        $password = $req->getParameter('password', 'POST', '');

        if ($this->routeContext->getModService()->authenticate($username, $password)) {
            session_regenerate_id(true);
            $_SESSION['mod_user']   = $username;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $modInfo = $this->routeContext->getModService()->getModInfo($username);
            $_SESSION['mod_role'] = $modInfo['role'] ?? 'mod';
        } else {
            $_SESSION['login_error'] = true;
        }

        header('Location: ?route=moderateRoute');
        exit;
    }

    private function handleLogout(): void {
        $_SESSION = [];
        session_destroy();
        header('Location: ?route=moderateRoute');
        exit;
    }

    private function handleChangePassword(): void {
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            exit;
        }

        $req = $this->routeContext->request;
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $req->getParameter('csrf_token', 'POST', ''))) {
            http_response_code(403);
            exit;
        }

        $current = $req->getParameter('current_password', 'POST', '');
        $new     = $req->getParameter('new_password', 'POST', '');
        $confirm = $req->getParameter('confirm_password', 'POST', '');

        if ($new === '') {
            $_SESSION['pw_error'] = 'New password cannot be empty.';
        } elseif ($new !== $confirm) {
            $_SESSION['pw_error'] = 'New passwords do not match.';
        } elseif (!$this->routeContext->getModService()->changePassword($_SESSION['mod_user'], $current, $new)) {
            $_SESSION['pw_error'] = 'Current password is incorrect.';
        } else {
            $_SESSION['pw_success'] = 'Password changed successfully.';
        }

        header('Location: ?route=moderateRoute&subpage=account');
        exit;
    }

    private function handleCreateMod(): void {
        if (($_SESSION['mod_role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit;
        }

        $req = $this->routeContext->request;
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $req->getParameter('csrf_token', 'POST', ''))) {
            http_response_code(403);
            exit;
        }

        $username = trim($req->getParameter('new_username', 'POST', ''));
        $role     = $req->getParameter('new_role', 'POST', 'mod');
        $password = $req->getParameter('new_password', 'POST', '');
        $confirm  = $req->getParameter('new_confirm', 'POST', '');

        if ($password === '') {
            $_SESSION['create_error'] = 'Password cannot be empty.';
        } elseif ($password !== $confirm) {
            $_SESSION['create_error'] = 'Passwords do not match.';
        } else {
            $error = $this->routeContext->getModService()->createMod($username, $role, $password);
            if ($error !== null) {
                $_SESSION['create_error'] = $error;
            } else {
                $_SESSION['create_success'] = "Account '{$username}' created.";
            }
        }

        header('Location: ?route=moderateRoute&subpage=accounts');
        exit;
    }

    private function handleDeleteAccounts(): void {
        if (($_SESSION['mod_role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit;
        }

        $req = $this->routeContext->request;
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $req->getParameter('csrf_token', 'POST', ''))) {
            http_response_code(403);
            exit;
        }

        $ids = $req->getParameter('ids', 'POST', []);
        if (is_array($ids) && !empty($ids)) {
            $error = $this->routeContext->getModService()->deleteModsByIds($ids, $_SESSION['mod_user']);
            if ($error !== null) {
                $_SESSION['delete_error'] = $error;
            } else {
                $_SESSION['delete_success'] = 'Selected account(s) deleted.';
            }
        }

        header('Location: ?route=moderateRoute&subpage=accounts');
        exit;
    }

    private function handleAdminSetPassword(): void {
        if (($_SESSION['mod_role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit;
        }

        $req = $this->routeContext->request;
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $req->getParameter('csrf_token', 'POST', ''))) {
            http_response_code(403);
            exit;
        }

        $targetUsername = trim($req->getParameter('target_username', 'POST', ''));
        $newPassword    = $req->getParameter('set_password', 'POST', '');
        $confirm        = $req->getParameter('set_confirm', 'POST', '');

        if ($targetUsername === '') {
            $_SESSION['setpw_error'] = 'No account selected.';
        } elseif ($newPassword === '') {
            $_SESSION['setpw_error'] = 'Password cannot be empty.';
        } elseif ($newPassword !== $confirm) {
            $_SESSION['setpw_error'] = 'Passwords do not match.';
        } else {
            $this->routeContext->getModService()->adminSetPassword($targetUsername, $newPassword);
            $_SESSION['setpw_success'] = "Password updated for '{$targetUsername}'.";
        }

        header('Location: ?route=moderateRoute&subpage=accounts');
        exit;
    }

    private function handleDelete(): void {
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            exit;
        }

        $req = $this->routeContext->request;
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $req->getParameter('csrf_token', 'POST', ''))) {
            http_response_code(403);
            exit;
        }

        $ids = $req->getParameter('ids', 'POST', []);
        if (is_array($ids) && !empty($ids)) {
            $deleted = $this->routeContext->getPasteService()->deletePastesByIds($ids);
            $this->writeAuditLog($_SESSION['mod_user'], $deleted);
        }

        header('Location: ?route=moderateRoute');
        exit;
    }

    private function writeAuditLog(string $modUser, array $deleted): void {
        if (empty($deleted)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $modIp     = $this->routeContext->request->getIp();
        $lines     = [];

        foreach ($deleted as $paste) {
            $lines[] = "[{$timestamp}] DELETE | mod: {$modUser} ({$modIp}) | paste #{$paste['id']} \"{$paste['title']}\" | poster ip: {$paste['ip_address']} | created: {$paste['created_at']}";
        }

        file_put_contents(
            self::AUDIT_LOG,
            implode(PHP_EOL, $lines) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    private function renderAccountPage(): string {
        $h       = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $modInfo = $this->routeContext->getModService()->getModInfo($_SESSION['mod_user']);

        $error   = $_SESSION['pw_error'] ?? '';
        $success = $_SESSION['pw_success'] ?? '';
        unset($_SESSION['pw_error'], $_SESSION['pw_success']);

        $engine = new templateEngine(__DIR__ . '/../templates', false);
        return $engine->render('modAccount', [
            'MOD_USER'         => $h((string) $_SESSION['mod_user']),
            'MOD_ROLE'         => $h((string) ($modInfo['role'] ?? '')),
            'MOD_CREATED'      => $h((string) ($modInfo['created_at'] ?? '')),
            'CSRF_TOKEN'       => $_SESSION['csrf_token'] ?? '',
            'PASSWORD_ERROR'   => $error,
            'PASSWORD_SUCCESS' => $success,
            'IS_ADMIN'             => $this->isAdmin(),
            'NAV_PASTES'           => false,
            'NAV_ACCOUNT'          => true,
            'SHOW_ACCOUNTS_LINK'   => $this->isAdmin(),
            'SHOW_ACCOUNTS_BOLD'   => false,
        ]);
    }

    private function renderAccountsPage(): string {
        $h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $createError   = $_SESSION['create_error'] ?? '';
        $createSuccess = $_SESSION['create_success'] ?? '';
        $deleteError   = $_SESSION['delete_error'] ?? '';
        $deleteSuccess = $_SESSION['delete_success'] ?? '';
        $setpwError    = $_SESSION['setpw_error'] ?? '';
        $setpwSuccess  = $_SESSION['setpw_success'] ?? '';
        unset(
            $_SESSION['create_error'], $_SESSION['create_success'],
            $_SESSION['delete_error'], $_SESSION['delete_success'],
            $_SESSION['setpw_error'],  $_SESSION['setpw_success']
        );

        $mods        = $this->routeContext->getModService()->getAllMods();
        $escapedMods = array_map(fn(array $m): array => [
            'id'         => (int) $m['id'],
            'username'   => $h((string) $m['username']),
            'role'       => $h((string) $m['role']),
            'created_at' => $h((string) $m['created_at']),
        ], $mods);

        $engine = new templateEngine(__DIR__ . '/../templates', false);
        return $engine->render('modAccounts', [
            'MOD_USER'       => $h((string) $_SESSION['mod_user']),
            'CSRF_TOKEN'     => $_SESSION['csrf_token'] ?? '',
            'ACCOUNTS'       => $escapedMods,
            'HAS_ACCOUNTS'   => !empty($escapedMods),
            'CREATE_ERROR'   => $createError,
            'CREATE_SUCCESS' => $createSuccess,
            'DELETE_ERROR'   => $deleteError,
            'DELETE_SUCCESS' => $deleteSuccess,
            'SETPW_ERROR'    => $setpwError,
            'SETPW_SUCCESS'  => $setpwSuccess,
            'IS_ADMIN'             => $this->isAdmin(),
            'NAV_PASTES'           => false,
            'NAV_ACCOUNT'          => false,
            'SHOW_ACCOUNTS_LINK'   => false,
            'SHOW_ACCOUNTS_BOLD'   => true,
        ]);
    }

    private function renderLoginForm(): string {
        $error = !empty($_SESSION['login_error']);
        unset($_SESSION['login_error']);

        $engine = new templateEngine(__DIR__ . '/../templates', false);
        return $engine->render('modLogin', [
            'LOGIN_ERROR' => $error ? 'Invalid username or password.' : '',
        ]);
    }

    private function renderModPanel(): string {
        $config  = $this->routeContext->config;
        $perPage = max(1, (int) ($config['moderation']['page_size'] ?? 50));
        $page    = $this->getPageFromRequest();

        $pasteService = $this->routeContext->getPasteService();
        $total        = $pasteService->countPastes();

        [$totalPages, $page] = $this->validateAndClampPagination($perPage, $total, $page);
        $pastes = $pasteService->getPageOfPastes($page, $perPage);

        $h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $escapedPastes = array_map(function (array $p) use ($h): array {
            $preview = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $p['content_preview'] ?? '');
            return [
                'id'              => (int) $p['id'],
                'uuid'            => $h((string) $p['uuid']),
                'title'           => $h($p['title'] !== '' ? $p['title'] : '(untitled)'),
                'ip_address'      => $h((string) $p['ip_address']),
                'created_at'      => $h((string) $p['created_at']),
                'ttl_label'       => $h($this->ttlLabel((int) $p['time_to_live'])),
                'content_preview' => $h($preview),
            ];
        }, $pastes);

        $req = $this->routeContext->request;

        $getLink = function (int $pg) use ($req): string {
            $params = $req->allGet();
            unset($params['page']);
            $params['page'] = $pg;
            return htmlspecialchars('?' . http_build_query($params), ENT_QUOTES, 'UTF-8');
        };

        $getForm = function (int $pg, string $label) use ($req): string {
            $params = $req->allGet();
            unset($params['page']);
            $params['page'] = $pg;

            $inputs = '';
            foreach ($params as $key => $val) {
                $safeKey = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
                if (is_array($val)) {
                    foreach ($val as $subVal) {
                        $inputs .= '<input type="hidden" name="' . $safeKey . '[]" value="' . htmlspecialchars((string) $subVal, ENT_QUOTES, 'UTF-8') . '">' . "\n";
                    }
                } else {
                    $inputs .= '<input type="hidden" name="' . $safeKey . '" value="' . htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') . '">' . "\n";
                }
            }

            return '<form action="?" method="get">'
                . $inputs
                . '<button type="submit">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</button>'
                . '</form>';
        };

        $engine = new templateEngine(__DIR__ . '/../templates', false);
        return $engine->render('modPanel', [
            'PASTES'       => $escapedPastes,
            'MOD_USER'     => $h((string) $_SESSION['mod_user']),
            'CSRF_TOKEN'   => $_SESSION['csrf_token'] ?? '',
            'PAGINATION'   => $this->renderPager($page, $totalPages, $getLink, $getForm),
            'TOTAL_PASTES' => $total,
            'HAS_PASTES'   => !empty($escapedPastes),
            'IS_ADMIN'             => $this->isAdmin(),
            'NAV_PASTES'           => true,
            'NAV_ACCOUNT'          => false,
            'SHOW_ACCOUNTS_LINK'   => $this->isAdmin(),
            'SHOW_ACCOUNTS_BOLD'   => false,
        ]);
    }

    private function isLoggedIn(): bool {
        // Note: a deliberate string check, not empty() — a username of "0" is
        // valid but would be falsy under empty().
        return isset($_SESSION['mod_user']) && $_SESSION['mod_user'] !== '';
    }

    private function isAdmin(): bool {
        return ($_SESSION['mod_role'] ?? '') === 'admin';
    }

    private function ttlLabel(int $ttl): string {
        if ($ttl === 0) {
            return 'Permanent';
        }
        $days = (int) round($ttl / 86400);
        return $days === 1 ? '1 day' : "{$days} days";
    }

    private function getPageFromRequest(string $pageParam = 'page'): int {
        $req = $this->routeContext->request;
        return ($req->hasParameter($pageParam, 'GET') && is_numeric($req->getParameter($pageParam, 'GET')))
            ? max(1, (int) $req->getParameter($pageParam, 'GET'))
            : 1;
    }

    private function validateAndClampPagination(int $entriesPerPage, int $totalEntries, int $currentPage): array {
        $totalPages  = $entriesPerPage > 0 ? (int) ceil($totalEntries / $entriesPerPage) : 0;
        $currentPage = max(1, min(max(1, $totalPages), $currentPage));
        return [$totalPages, $currentPage];
    }

    private function renderPager(int $currentPage, int $totalPages, callable $getLink, callable $getForm): string {
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<table id="pager"><tbody><tr>';

        if ($currentPage <= 1) {
            $html .= '<td id="pagerPreviousCell">First</td>';
        } else {
            $html .= '<td id="pagerPreviousCell">' . $getForm($currentPage - 1, 'Previous') . '</td>';
        }

        $html .= '<td id="pagerPagesCell"><div id="pagerPagesContainer">';
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === $currentPage) {
                $html .= "<span class=\"pagerPageLink\" id=\"pagerSelectedPage\">[$i]</span> ";
            } else {
                $html .= '<span class="pagerPageLink">[<a href="' . $getLink($i) . '">' . $i . '</a>]</span> ';
            }
        }
        $html .= '</div></td>';

        if ($currentPage >= $totalPages) {
            $html .= '<td id="pagerNextCell">Last</td>';
        } else {
            $html .= '<td id="pagerNextCell">' . $getForm($currentPage + 1, 'Next') . '</td>';
        }

        $html .= '</tr></tbody></table>';
        return $html;
    }
}
