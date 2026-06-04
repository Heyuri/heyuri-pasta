<?php

use Puchiko\template\templateEngine;

class mainRoute {

    public function __construct(private routeContext $routeContext) {}

    public function invoke(): void {
        $engine  = new templateEngine(__DIR__ . '/../templates', false);
        $content = $engine->render('main', [
            'DESCRIPTION' => $this->routeContext->config['site']['description'],
        ]);

        echo $this->routeContext->renderer->renderPage($content);
    }
}
