<?php

use Puchiko\template\templateEngine;

use function Puchiko\strings\sanitizeStr;

class newPasta {

    public function __construct(private routeContext $routeContext) {}

    public function invoke(): void {
        $times    = $this->routeContext->config['times_to_live'];
        $pasteCfg = $this->routeContext->config['paste'] ?? [];

        $options = '';
        foreach ($times as $label => $value) {
            $options .= '<option value="' . sanitizeStr($value) . '">'
                . sanitizeStr($this->formatTimeToLiveLabel($label))
                . '</option>';
        }
        $expireSelect = '<select name="time_to_live">' . $options . '</select>';

        $engine  = new templateEngine(__DIR__ . '/../templates', false);
        $content = $engine->render('newPaste', [
            'CREATE_PASTE_HEADER' => 'New pasta',
            'EXPIRE_TIMES'        => $expireSelect,
            'CONTENT_MAX_LENGTH'  => max(1, (int) ($pasteCfg['max_length'] ?? 4194303)),
            'TITLE_MAX_LENGTH'    => max(1, (int) ($pasteCfg['title_max_length'] ?? 255)),
        ]);

        echo $this->routeContext->renderer->renderPage($content);
    }

    private function formatTimeToLiveLabel(string $label): string {
        if ($label === 'permanent') {
            return 'Permanent';
        }

        return preg_replace('/(?<=\d)(?=[A-Za-z])/', ' ', $label) ?? $label;
    }
}