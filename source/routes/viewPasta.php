<?php

use function Puchiko\strings\sanitizeStr;

class viewPasta {

    public function __construct(private routeContext $routeContext) {}

    public function invoke(): void {
        $uuid  = trim($this->routeContext->request->getParameter('id', 'GET', ''));
        $paste = $this->routeContext->getPasteService()->findByUuid($uuid);

        if ($paste === null) {
            http_response_code(404);
            echo $this->routeContext->renderer->renderPage('<p>Pasta not found.</p>');
            return;
        }

        $content = $this->routeContext->renderer->renderViewPaste(
            sanitizeStr($paste['title']),
            sanitizeStr($paste['content'])
        );

        echo $content;
    }
}
