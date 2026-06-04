<?php

use Puchiko\database\databaseConnection;
use Puchiko\request\request;

class routeContext {
    public readonly pageRenderer $renderer;
    public readonly array $config;
    public readonly request $request;
    private readonly databaseConnection $db;
    private ?pasteService $pasteService = null;
    private ?modService $modService = null;

    public function __construct(request $request) {
        $this->request = $request;
        $this->config = parse_ini_file(__DIR__ . '/../private/config.ini', true);
        $this->renderer = new pageRenderer($this->config, $request->getParameter('route', 'GET', 'mainRoute'));

        $db = $this->config['database'];
        databaseConnection::createInstance(
            'mysql',
            $db['database_host'],
            $db['database_name'],
            'utf8mb4',
            $db['database_user'],
            $db['database_password'],
        );

        $connection = databaseConnection::getInstance();
        if ($connection === null) {
            throw new \RuntimeException('Failed to initialize database connection.');
        }

        $this->db = $connection;
    }

    public function getPasteService(): pasteService {
        return $this->pasteService ??= new pasteService(new pasteRepository($this->db));
    }

    public function getModService(): modService {
        return $this->modService ??= new modService(new modRepository($this->db));
    }
}