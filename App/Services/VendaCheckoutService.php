<?php
class VendaCheckoutService
{
    public static function buildFromFormData($dados)
    {
        $items = VendaSessionService::all();

        if (!$items)
        {
            throw new Exception('Adicione pelo menos um item antes de concluir a venda.');
        }

        $clienteId = isset($dados->id_cliente) ? (int) $dados->id_cliente : 0;
        $parcelas = isset($dados->parcelas) ? (int) $dados->parcelas : 0;

        if ($clienteId <= 0)
        {
            throw new Exception('Selecione um cliente valido.');
        }

        if ($parcelas < 1)
        {
            throw new Exception('Selecione uma quantidade valida de parcelas.');
        }

        $valorVenda = (float) VendaSessionService::total();
        $desconto = self::normalizeDecimal(isset($dados->desconto) ? $dados->desconto : 0);
        $acrescimos = self::normalizeDecimal(isset($dados->acrescimos) ? $dados->acrescimos : 0);
        $valorFinal = max(0, $valorVenda + $acrescimos - $desconto);

        return (object) [
            'id_cliente' => $clienteId,
            'parcelas' => $parcelas,
            'valor_venda' => $valorVenda,
            'desconto' => $desconto,
            'acrescimos' => $acrescimos,
            'valor_final' => $valorFinal,
            'obs' => isset($dados->obs) ? trim((string) $dados->obs) : '',
            'items' => $items,
        ];
    }

    public static function defaultFormData()
    {
        $valorVenda = (float) VendaSessionService::total();

        return (object) [
            'valor_venda' => $valorVenda,
            'valor_final' => $valorVenda,
            'desconto' => 0,
            'acrescimos' => 0,
        ];
    }

    private static function normalizeDecimal($value)
    {
        if (is_numeric($value))
        {
            return (float) $value;
        }

        $value = trim((string) $value);

        if ($value === '')
        {
            return 0.0;
        }

        $normalized = str_replace('.', '', $value);
        $normalized = str_replace(',', '.', $normalized);

        if (!is_numeric($normalized))
        {
            throw new Exception('Informe valores monetarios validos.');
        }

        return (float) $normalized;
    }
}
