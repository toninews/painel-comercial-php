<?php
use Nexa\Database\Transaction;

class VendaApplicationService
{
    private static $supportsParcelasColumn;

    public static function finalize($formData)
    {
        Transaction::open('painel_comercial');

        try
        {
            $dados = VendaCheckoutService::buildFromFormData($formData);
            $cliente = self::loadValidCliente($dados->id_cliente);

            $venda = new Venda;
            $venda->cliente     = $cliente;
            $venda->data_venda  = date('Y-m-d');
            $venda->valor_venda = $dados->valor_venda;
            $venda->desconto    = $dados->desconto;
            $venda->acrescimos  = $dados->acrescimos;
            $venda->valor_final = $dados->valor_final;
            $venda->obs         = $dados->obs;
            if (self::supportsParcelasColumn())
            {
                $venda->parcelas = $dados->parcelas;
            }

            foreach ($dados->items as $item)
            {
                $venda->addItem(new Produto($item->id_produto), $item->quantidade);
            }

            $venda->store();
            Conta::geraParcelas($dados->id_cliente, 2, $dados->valor_final, $dados->parcelas);

            Transaction::close();
            VendaSessionService::clear();

            return $venda;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    private static function supportsParcelasColumn()
    {
        if (self::$supportsParcelasColumn !== null)
        {
            return self::$supportsParcelasColumn;
        }

        $conn = Transaction::get();
        $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql')
        {
            $statement = $conn->prepare("
                SELECT 1
                  FROM information_schema.columns
                 WHERE table_name = 'venda'
                   AND column_name = 'parcelas'
                 LIMIT 1
            ");
            $statement->execute();
            self::$supportsParcelasColumn = (bool) $statement->fetchColumn();
            return self::$supportsParcelasColumn;
        }

        $statement = $conn->prepare("PRAGMA table_info(venda)");
        $statement->execute();
        $columns = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($columns as $column)
        {
            if (isset($column['name']) && $column['name'] === 'parcelas')
            {
                self::$supportsParcelasColumn = true;
                return true;
            }
        }

        self::$supportsParcelasColumn = false;
        return false;
    }

    private static function loadValidCliente($clienteId)
    {
        $cliente = Pessoa::find($clienteId);

        if (!$cliente)
        {
            throw new Exception('Cliente não encontrado.');
        }

        if ($cliente->totalDebitos() > 0)
        {
            throw new Exception('Débitos impedem esta operação.');
        }

        return $cliente;
    }
}
