<?php
use Nexa\Database\Transaction;

class PessoasReportService
{
    public static function build()
    {
        $payload = [
            'report_title' => 'Pessoas',
            'report_subtitle' => 'Posicao de saldo por cadastro',
            'total_pessoas' => 0,
            'total_aberto' => 0,
            'pessoas' => [],
        ];

        Transaction::open('painel_comercial');

        try
        {
            $pessoas = ViewSaldoPessoa::all() ?: [];

            foreach ($pessoas as $pessoa)
            {
                $payload['total_pessoas']++;
                $payload['total_aberto'] += (float) $pessoa->aberto;
            }

            $payload['pessoas'] = $pessoas;

            Transaction::close();

            return $payload;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }
}
