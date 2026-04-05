<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Config;

use PDO;

final class Connection
{
    private string $host;
    private string $port;
    private string $database;
    private string $username;
    private string $password;
    private string $charset;

    public function __construct(
        string $host,
        string $port,
        string $database,
        string $username,
        string $password,
        string $charset = 'utf8mb4'
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;
    }

    public function createPDO(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->charset
        );

        return new PDO(
            $dsn,
            $this->username,
            $this->password,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            )
        );
    }



}