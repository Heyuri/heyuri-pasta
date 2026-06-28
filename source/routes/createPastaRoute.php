<?php

class createPasta {

    public function __construct(private routeContext $routeContext) {}

    public function invoke(): void {
        $req     = $this->routeContext->request;
        $title   = trim($req->getParameter('title',         'POST', ''));
        $content = trim($req->getParameter('content',       'POST', ''));
        $ttl     = (int) ($req->getParameter('time_to_live', 'POST', 86400));

        if ($content === '') {
            http_response_code(400);
            echo $this->routeContext->renderer->renderPage('<p>Content is required.</p>');
            return;
        }

        $pasteCfg     = $this->routeContext->config['paste'] ?? [];
        $minLength    = max(1, (int) ($pasteCfg['min_length'] ?? 1));
        $maxLength    = max(1, (int) ($pasteCfg['max_length'] ?? 10000));
        $titleMaxLen  = max(1, (int) ($pasteCfg['title_max_length'] ?? 255));
        $len          = mb_strlen($content, 'UTF-8');

        if (mb_strlen($title, 'UTF-8') > $titleMaxLen) {
            http_response_code(400);
            echo $this->routeContext->renderer->renderPage('<p>Title is too long (maximum ' . $titleMaxLen . ' characters).</p>');
            return;
        }

        if ($len < $minLength) {
            http_response_code(400);
            echo $this->routeContext->renderer->renderPage('<p>Paste is too short (minimum ' . $minLength . ' character' . ($minLength === 1 ? '' : 's') . ').</p>');
            return;
        }

        if ($len > $maxLength) {
            http_response_code(400);
            echo $this->routeContext->renderer->renderPage('<p>Paste is too long (maximum ' . $maxLength . ' characters).</p>');
            return;
        }

        $ip           = $req->getIp();
        $flood        = $this->routeContext->config['flood'] ?? [];
        $maxPastes    = max(1, (int) ($flood['max_pastes']     ?? 5));
        $windowSecs   = max(1, (int) ($flood['window_seconds'] ?? 60));

        if ($this->routeContext->getPasteService()->isFloodedByIp($ip, $maxPastes, $windowSecs)) {
            http_response_code(429);
            echo $this->routeContext->renderer->renderPage('<p>You are posting too fast. Please wait before submitting again.</p>');
            return;
        }

        $allowedTtls = array_map('intval', array_values($this->routeContext->config['times_to_live'] ?? []));

        try {
            $uuid = $this->routeContext->getPasteService()->createPaste($title, $content, $ttl, $ip, $allowedTtls);
        } catch (InvalidArgumentException) {
            http_response_code(400);
            echo $this->routeContext->renderer->renderPage('<p>Invalid time to live.</p>');
            return;
        }

        // Remember the chosen TTL so it is pre-selected on future pastes.
        setcookie('preferred_ttl', (string) $ttl, [
            'expires'  => time() + 31536000,
            'path'     => '/',
            'samesite' => 'Lax',
        ]);

        header('Location: ?route=viewPasta&id=' . urlencode($uuid));
        exit;
    }
}
