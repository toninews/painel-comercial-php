<?php
require_once __DIR__ . '/../bootstrap.php';

use Nexa\Database\Connection;

function tableExists(PDO $conn, $table)
{
    $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'pgsql')
    {
        $statement = $conn->prepare("
            SELECT 1
              FROM information_schema.tables
             WHERE table_schema = 'public'
               AND table_name = :table
             LIMIT 1
        ");
        $statement->execute([':table' => $table]);

        return (bool) $statement->fetchColumn();
    }

    if ($driver === 'sqlite')
    {
        $statement = $conn->prepare("
            SELECT 1
              FROM sqlite_master
             WHERE type = 'table'
               AND name = :table
             LIMIT 1
        ");
        $statement->execute([':table' => $table]);

        return (bool) $statement->fetchColumn();
    }

    return false;
}

try
{
    $conn = Connection::open('painel_comercial');
}
catch (Throwable $e)
{
    fwrite(STDERR, "DB_UNAVAILABLE: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

if (!tableExists($conn, 'venda'))
{
    fwrite(STDOUT, "DB_SCHEMA_MISSING: table venda not found" . PHP_EOL);
    exit(2);
}

fwrite(STDOUT, "DB_READY" . PHP_EOL);
exit(0);
