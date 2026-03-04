<?php
require_once __DIR__ . '/../bootstrap.php';

use Nexa\Database\Connection;

function hasColumn(PDO $conn, $table, $column)
{
    $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'pgsql')
    {
        $statement = $conn->prepare("
            SELECT 1
              FROM information_schema.columns
             WHERE table_name = :table
               AND column_name = :column
             LIMIT 1
        ");
        $statement->execute([
            ':table' => $table,
            ':column' => $column,
        ]);

        return (bool) $statement->fetchColumn();
    }

    $statement = $conn->prepare("PRAGMA table_info({$table})");
    $statement->execute();
    $columns = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col)
    {
        if (isset($col['name']) && $col['name'] === $column)
        {
            return true;
        }
    }

    return false;
}

function insertRows(PDO $conn, $table, array $rows)
{
    if (!$rows)
    {
        return;
    }

    $columns = array_keys($rows[0]);
    $columnSql = implode(', ', $columns);
    $placeholderSql = implode(', ', array_map(function ($column) {
        return ':' . $column;
    }, $columns));

    $statement = $conn->prepare("INSERT INTO {$table} ({$columnSql}) VALUES ({$placeholderSql})");

    foreach ($rows as $row)
    {
        $params = [];
        foreach ($columns as $column)
        {
            $params[':' . $column] = isset($row[$column]) ? $row[$column] : null;
        }
        $statement->execute($params);
    }
}

function resetPgSequences(PDO $conn, array $tables)
{
    foreach ($tables as $table)
    {
        $sql = "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)";
        $conn->exec($sql);
    }
}

function ensureSqliteWritable()
{
    $dbName = getenv('DB_NAME') ?: 'App/Database/painel_comercial.db';
    $dbPath = str_starts_with($dbName, '/')
        ? $dbName
        : dirname(__DIR__) . '/' . ltrim($dbName, '/');

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

try
{
    $conn = Connection::open('painel_comercial');
    $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
    $supportsParcelas = hasColumn($conn, 'venda', 'parcelas');

    $conn->beginTransaction();

    if ($driver === 'pgsql')
    {
        $conn->exec('TRUNCATE TABLE item_venda, conta, venda, pessoa_grupo, produto, pessoa, cidade, estado, fabricante, tipo, unidade, grupo RESTART IDENTITY CASCADE');
    }
    else
    {
        $conn->exec('PRAGMA foreign_keys = OFF');
        $conn->exec('DELETE FROM item_venda');
        $conn->exec('DELETE FROM conta');
        $conn->exec('DELETE FROM venda');
        $conn->exec('DELETE FROM pessoa_grupo');
        $conn->exec('DELETE FROM produto');
        $conn->exec('DELETE FROM pessoa');
        $conn->exec('DELETE FROM cidade');
        $conn->exec('DELETE FROM estado');
        $conn->exec('DELETE FROM fabricante');
        $conn->exec('DELETE FROM tipo');
        $conn->exec('DELETE FROM unidade');
        $conn->exec('DELETE FROM grupo');
        try
        {
            $conn->exec("DELETE FROM sqlite_sequence WHERE name IN ('item_venda','conta','venda','pessoa_grupo','produto','pessoa','cidade','estado','fabricante','tipo','unidade','grupo')");
        }
        catch (Throwable $e)
        {
            // Alguns bancos SQLite não possuem sqlite_sequence quando tabelas não usam AUTOINCREMENT.
        }
        $conn->exec('PRAGMA foreign_keys = ON');
    }

    insertRows($conn, 'estado', [
        ['id' => 1, 'sigla' => 'SP', 'nome' => 'Sao Paulo'],
        ['id' => 2, 'sigla' => 'RJ', 'nome' => 'Rio de Janeiro'],
        ['id' => 3, 'sigla' => 'MG', 'nome' => 'Minas Gerais'],
        ['id' => 4, 'sigla' => 'PR', 'nome' => 'Parana'],
        ['id' => 5, 'sigla' => 'SC', 'nome' => 'Santa Catarina'],
    ]);

    insertRows($conn, 'cidade', [
        ['id' => 1, 'nome' => 'Sao Paulo', 'id_estado' => 1],
        ['id' => 2, 'nome' => 'Rio de Janeiro', 'id_estado' => 2],
        ['id' => 3, 'nome' => 'Belo Horizonte', 'id_estado' => 3],
        ['id' => 4, 'nome' => 'Curitiba', 'id_estado' => 4],
        ['id' => 5, 'nome' => 'Florianopolis', 'id_estado' => 5],
    ]);

    insertRows($conn, 'grupo', [
        ['id' => 1, 'nome' => 'Cliente'],
        ['id' => 2, 'nome' => 'Fornecedor'],
        ['id' => 3, 'nome' => 'Revendedor'],
        ['id' => 4, 'nome' => 'Colaborador'],
        ['id' => 5, 'nome' => 'Parceiro'],
    ]);

    insertRows($conn, 'fabricante', [
        ['id' => 1, 'nome' => 'Nexa Tech', 'site' => 'https://nexatech.example'],
        ['id' => 2, 'nome' => 'Aurora Devices', 'site' => 'https://auroradevices.example'],
        ['id' => 3, 'nome' => 'Atlas Components', 'site' => 'https://atlascomponents.example'],
        ['id' => 4, 'nome' => 'Pioneer Systems', 'site' => 'https://pioneersystems.example'],
        ['id' => 5, 'nome' => 'Vertex Supply', 'site' => 'https://vertexsupply.example'],
    ]);

    insertRows($conn, 'unidade', [
        ['id' => 1, 'sigla' => 'UN', 'nome' => 'Unidade'],
        ['id' => 2, 'sigla' => 'CX', 'nome' => 'Caixa'],
        ['id' => 3, 'sigla' => 'KIT', 'nome' => 'Kit'],
        ['id' => 4, 'sigla' => 'PC', 'nome' => 'Peca'],
        ['id' => 5, 'sigla' => 'L', 'nome' => 'Litro'],
    ]);

    insertRows($conn, 'tipo', [
        ['id' => 1, 'nome' => 'Maquina'],
        ['id' => 2, 'nome' => 'Acessorio'],
        ['id' => 3, 'nome' => 'Insumo'],
        ['id' => 4, 'nome' => 'Componente'],
        ['id' => 5, 'nome' => 'Servico'],
    ]);

    insertRows($conn, 'produto', [
        ['id' => 1, 'descricao' => 'Notebook Pro 14', 'estoque' => 12, 'preco_custo' => 3100, 'preco_venda' => 4290, 'id_fabricante' => 1, 'id_unidade' => 1, 'id_tipo' => 1],
        ['id' => 2, 'descricao' => 'Mouse Sem Fio M2', 'estoque' => 40, 'preco_custo' => 45, 'preco_venda' => 99, 'id_fabricante' => 2, 'id_unidade' => 1, 'id_tipo' => 2],
        ['id' => 3, 'descricao' => 'SSD NVMe 1TB', 'estoque' => 18, 'preco_custo' => 220, 'preco_venda' => 369, 'id_fabricante' => 3, 'id_unidade' => 1, 'id_tipo' => 4],
        ['id' => 4, 'descricao' => 'Teclado Mecanico K80', 'estoque' => 22, 'preco_custo' => 180, 'preco_venda' => 329, 'id_fabricante' => 4, 'id_unidade' => 1, 'id_tipo' => 2],
        ['id' => 5, 'descricao' => 'Pasta Termica 5g', 'estoque' => 55, 'preco_custo' => 12, 'preco_venda' => 29, 'id_fabricante' => 5, 'id_unidade' => 1, 'id_tipo' => 3],
    ]);

    insertRows($conn, 'pessoa', [
        ['id' => 1, 'nome' => 'Ana Martins', 'endereco' => 'Rua das Flores, 101', 'bairro' => 'Centro', 'telefone' => '(11) 98888-1111', 'email' => 'ana.martins@example.com', 'id_cidade' => 1],
        ['id' => 2, 'nome' => 'Bruno Lima', 'endereco' => 'Av. Litoral, 200', 'bairro' => 'Praia', 'telefone' => '(21) 97777-2222', 'email' => 'bruno.lima@example.com', 'id_cidade' => 2],
        ['id' => 3, 'nome' => 'Carla Souza', 'endereco' => 'Rua Minas, 303', 'bairro' => 'Savassi', 'telefone' => '(31) 96666-3333', 'email' => 'carla.souza@example.com', 'id_cidade' => 3],
        ['id' => 4, 'nome' => 'Diego Reis', 'endereco' => 'Rua das Araucarias, 404', 'bairro' => 'Batel', 'telefone' => '(41) 95555-4444', 'email' => 'diego.reis@example.com', 'id_cidade' => 4],
        ['id' => 5, 'nome' => 'Erika Rocha', 'endereco' => 'Rua do Sol, 505', 'bairro' => 'Centro', 'telefone' => '(48) 94444-5555', 'email' => 'erika.rocha@example.com', 'id_cidade' => 5],
    ]);

    insertRows($conn, 'pessoa_grupo', [
        ['id' => 1, 'id_pessoa' => 1, 'id_grupo' => 1],
        ['id' => 2, 'id_pessoa' => 2, 'id_grupo' => 1],
        ['id' => 3, 'id_pessoa' => 3, 'id_grupo' => 2],
        ['id' => 4, 'id_pessoa' => 4, 'id_grupo' => 3],
        ['id' => 5, 'id_pessoa' => 5, 'id_grupo' => 4],
    ]);

    $vendas = [
        ['id' => 1, 'id_cliente' => 1, 'data_venda' => '2026-01-10', 'valor_venda' => 4389, 'desconto' => 99, 'acrescimos' => 0, 'valor_final' => 4290, 'parcelas' => 3, 'obs' => 'Venda demo parcelada'],
        ['id' => 2, 'id_cliente' => 2, 'data_venda' => '2026-01-15', 'valor_venda' => 297, 'desconto' => 0, 'acrescimos' => 0, 'valor_final' => 297, 'parcelas' => 1, 'obs' => 'Venda avista'],
        ['id' => 3, 'id_cliente' => 3, 'data_venda' => '2026-02-01', 'valor_venda' => 738, 'desconto' => 20, 'acrescimos' => 0, 'valor_final' => 718, 'parcelas' => 2, 'obs' => 'Venda com desconto'],
        ['id' => 4, 'id_cliente' => 4, 'data_venda' => '2026-02-12', 'valor_venda' => 329, 'desconto' => 0, 'acrescimos' => 15, 'valor_final' => 344, 'parcelas' => 2, 'obs' => 'Venda com acrescimo'],
        ['id' => 5, 'id_cliente' => 5, 'data_venda' => '2026-02-20', 'valor_venda' => 116, 'desconto' => 0, 'acrescimos' => 0, 'valor_final' => 116, 'parcelas' => 1, 'obs' => 'Venda rapida'],
    ];

    if ($supportsParcelas)
    {
        insertRows($conn, 'venda', $vendas);
    }
    else
    {
        $vendasLegacy = [];
        foreach ($vendas as $venda)
        {
            unset($venda['parcelas']);
            $vendasLegacy[] = $venda;
        }
        insertRows($conn, 'venda', $vendasLegacy);
    }

    insertRows($conn, 'item_venda', [
        ['id' => 1, 'id_produto' => 1, 'id_venda' => 1, 'quantidade' => 1, 'preco' => 4290],
        ['id' => 2, 'id_produto' => 2, 'id_venda' => 2, 'quantidade' => 3, 'preco' => 99],
        ['id' => 3, 'id_produto' => 3, 'id_venda' => 3, 'quantidade' => 2, 'preco' => 369],
        ['id' => 4, 'id_produto' => 4, 'id_venda' => 4, 'quantidade' => 1, 'preco' => 329],
        ['id' => 5, 'id_produto' => 5, 'id_venda' => 5, 'quantidade' => 4, 'preco' => 29],
    ]);

    insertRows($conn, 'conta', [
        ['id' => 1, 'id_cliente' => 1, 'dt_emissao' => '2026-01-10', 'dt_vencimento' => '2026-02-10', 'valor' => 1430, 'paga' => 'N'],
        ['id' => 2, 'id_cliente' => 1, 'dt_emissao' => '2026-01-10', 'dt_vencimento' => '2026-03-10', 'valor' => 1430, 'paga' => 'N'],
        ['id' => 3, 'id_cliente' => 1, 'dt_emissao' => '2026-01-10', 'dt_vencimento' => '2026-04-10', 'valor' => 1430, 'paga' => 'N'],
        ['id' => 4, 'id_cliente' => 3, 'dt_emissao' => '2026-02-01', 'dt_vencimento' => '2026-03-01', 'valor' => 359, 'paga' => 'S'],
        ['id' => 5, 'id_cliente' => 3, 'dt_emissao' => '2026-02-01', 'dt_vencimento' => '2026-04-01', 'valor' => 359, 'paga' => 'N'],
    ]);

    if ($driver === 'pgsql')
    {
        resetPgSequences($conn, [
            'estado', 'cidade', 'grupo', 'fabricante', 'unidade', 'tipo',
            'produto', 'pessoa', 'pessoa_grupo', 'venda', 'item_venda', 'conta'
        ]);
    }

    $conn->commit();

    if ($driver === 'sqlite')
    {
        ensureSqliteWritable();
    }

    echo "Banco reseedado com massa minima de demonstracao.\n";
    exit(0);
}
catch (Throwable $e)
{
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction())
    {
        $conn->rollBack();
    }

    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
