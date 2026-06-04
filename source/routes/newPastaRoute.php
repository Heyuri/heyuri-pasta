<?php

use Puchiko\template\templateEngine;

use function Puchiko\strings\sanitizeStr;

class newPasta {

    public function __construct(private routeContext $routeContext) {}

    public function invoke(): void {
        $times = $this->routeContext->config['times_to_live'];

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