<?php

use Puchiko\template\templateEngine;

class pageRenderer {
    private templateEngine $templateEngine;

    public function __construct(private array $config, private string $activeRoute = 'mainRoute') {
        $this->templateEngine = new templateEngine(__DIR__ . '/templates', false);
    }

    private function getMenuVars(string $activeRoute): array {
        $homeUrl = $this->config['server']['home_url'] ?? '/';

        $link = fn(string $url, string $label, bool $active): string =>
            $active ? $label : '<a href="' . $url . '">' . $label . '</a>';

        return [
            'MENU_HOME'      => '<a href="' . $homeUrl . '">Home</a>',
            'MENU_INDEX'     => $link('?route=mainRoute',     'Index',     $activeRoute === 'mainRoute'),
            'MENU_NEW_PASTA' => $link('?route=newPasta',      'New paste', $activeRoute === 'newPasta'),
            'MENU_MODERATE'  => $link('?route=moderateRoute', 'Moderate',  $activeRoute === 'moderateRoute'),
        ];
    }

    public function renderViewPaste(string $title, string $content, string $createdAt = ''): string {
        $staticUrl = $this->config['server']['url'] . 'static/';
        $this->templateEngine->clear()->bind([
            'PASTE_TITLE'   => $title,
            'PASTE_CONTENT' => $content,
            'PASTE_CREATED' => $createdAt,
        ]);
        $pasteContent = $this->templateEngine->render('viewPaste');

        return $this->renderPage($pasteContent, 'viewPasta', '<script src="' . $staticUrl . 'js/viewPaste.js"></script>');
    }

    public function renderPage(string $content, ?string $activeRoute = null, string $extraScripts = ''): string {
        if ($activeRoute === null) {
            $activeRoute = $this->activeRoute;
        }

        $staticUrl = $this->config['server']['url'] . 'static/';

        $bindings = array_merge([
            'PASTA_TITLE'        => $this->config['site']['name'],
            'PAGE_TITLE'         => $this->config['site']['name'],
            'MAIN_PAGE_SUBTITLE' => $this->config['site']['subtitle'],
            'STATIC_URL'         => $staticUrl,
            'NEW_PASTE_URL'      => '?route=newPasta',
            'EXTRA_SCRIPTS'      => $extraScripts,
            'CONTENT'            => $content,
        ], $this->getMenuVars($activeRoute));

        $this->templateEngine->clear()->bind($bindings);

        $head = $this->templateEngine->render('head');
        $body = $this->templateEngine->render('base');

        return '<!DOCTYPE html>' . PHP_EOL
            . '<html>' . PHP_EOL
            . $head . PHP_EOL
            . $body . PHP_EOL
            . '</html>';
    }
}
