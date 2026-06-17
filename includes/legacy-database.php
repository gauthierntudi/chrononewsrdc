<?php

declare(strict_types=1);

if (! class_exists('Database', false)) {
    class Database
    {
        private static ?self $instance = null;

        public static function getInstance(): self
        {
            if (self::$instance === null) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function getConnection(): PDO
        {
            global $db;

            if (isset($db) && $db instanceof PDO) {
                return $db;
            }

            if (function_exists('app')) {
                try {
                    return app('db')->getPdo();
                } catch (Throwable) {
                    // fallback
                }
            }

            throw new RuntimeException('Connexion base de données indisponible.');
        }
    }
}
