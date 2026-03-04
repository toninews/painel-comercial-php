<?php
use Nexa\Database\Transaction;

class SaldoDiagnosticoService
{
    public static function build($previewLimit = 5)
    {
        Transaction::open('painel_comercial');

        try
        {
            $all = ViewSaldoPessoa::all() ?: [];
            $count = count($all);
            $previewItems = array_slice($all, 0, $previewLimit);

            Transaction::close();

            return [
                'count' => $count,
                'preview_items' => $previewItems,
            ];
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }
}
