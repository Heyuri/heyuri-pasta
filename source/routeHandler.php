<?php

class routeHandler {
    private array $routes = [];

    public function __construct(
        private routeContext $routeContext
    ) {}

    public function addRoute(string $label, string $filePath) {
        $this->routes[$label] = $filePath;
    }

    public function getRoute(string $label) {
        if (isset($this->routes[$label])) {
            require $this->routes[$label];

            return new $label($this->routeContext);
        } else {
            throw new Exception("Route not found: " . $label);
        }
    }
}