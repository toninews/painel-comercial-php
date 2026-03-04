<?php
require_once __DIR__ . '/../bootstrap.php';

function rrmdir($directory)
{
    if (!is_dir($directory))
    {
        return;
    }

    $items = scandir($directory);
    if ($items === false)
    {
        return;
    }

    foreach ($items as $item)
    {
        if ($item === '.' || $item === '..')
        {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path))
        {
            rrmdir($path);
            @rmdir($path);
            continue;
        }

        @unlink($path);
    }
}

function resetDirectory($path)
{
    rrmdir($path);

    if (!is_dir($path))
    {
        mkdir($path, 0777, true);
    }
}

function ensureSqliteWritable($dbPath)
{
    $dbDirectory = dirname($dbPath);

    if (is_dir($dbDirectory))
    {
        @chmod($dbDirectory, 0777);
    }

    if (file_exists($dbPath))
    {
        @chmod($dbPath, 0666);
    }
}

$lockDirectory = __DIR__ . '/../tmp';
if (!is_dir($lockDirectory))
{
    mkdir($lockDirectory, 0777, true);
}

$lockFile = fopen($lockDirectory . '/reset-demo-db.lock', 'c+');
if ($lockFile === false)
{
    fwrite(STDERR, "Nao foi possivel criar lock do reset demo.\n");
    exit(1);
}

if (!flock($lockFile, LOCK_EX | LOCK_NB))
{
    fwrite(STDERR, "Ja existe um reset demo em execucao.\n");
    fclose($lockFile);
    exit(1);
}

$dbType = getenv('DB_TYPE') ?: 'sqlite';
$sqlFile = $dbType === 'pgsql'
    ? __DIR__ . '/../App/Database/painel_comercial-pgsql.sql'
    : __DIR__ . '/../App/Database/painel_comercial-sqlite.sql';

if (!file_exists($sqlFile))
{
    fwrite(STDERR, "Arquivo SQL nao encontrado: {$sqlFile}\n");
    flock($lockFile, LOCK_UN);
    fclose($lockFile);
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false)
{
    fwrite(STDERR, "Nao foi possivel ler {$sqlFile}\n");
    flock($lockFile, LOCK_UN);
    fclose($lockFile);
    exit(1);
}

try
{
    $driver = $dbType === 'pgsql' ? 'pgsql' : 'sqlite';

    if ($driver === 'sqlite')
    {
        $dbName = getenv('DB_NAME') ?: 'App/Database/painel_comercial.db';
        $dbPath = str_starts_with($dbName, '/')
            ? $dbName
            : dirname(__DIR__) . '/' . ltrim($dbName, '/');
        if (file_exists($dbPath))
        {
            unlink($dbPath);
        }
        $pdo = Nexa\Database\Connection::open('painel_comercial');
        $pdo->exec($sql);
        ensureSqliteWritable($dbPath);
    }
    elseif ($driver === 'pgsql')
    {
        $pdo = Nexa\Database\Connection::open('painel_comercial');
        $pdo->exec('DROP SCHEMA public CASCADE; CREATE SCHEMA public;');
        $pdo->exec($sql);
    }
    else
    {
        throw new RuntimeException('Driver de banco nao suportado para reset demo.');
    }

    resetDirectory(__DIR__ . '/../tmp/rate_limits');

    if ((getenv('APP_DEMO_RESET_CLEAR_SESSIONS') ?: '1') === '1')
    {
        resetDirectory(__DIR__ . '/../tmp/sessions');
    }

    echo "Banco demo resetado com sucesso.\n";
    echo "Estado temporario do demo limpo com sucesso.\n";
    flock($lockFile, LOCK_UN);
    fclose($lockFile);
    exit(0);
}
catch (Throwable $e)
{
    fwrite(STDERR, $e->getMessage() . "\n");
    flock($lockFile, LOCK_UN);
    fclose($lockFile);
    exit(1);
}
