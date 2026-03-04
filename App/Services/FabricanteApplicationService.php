<?php
use Nexa\Database\Transaction;

class FabricanteApplicationService
{
    public static function save($dados)
    {
        Transaction::open('painel_comercial');

        try
        {
            $fabricanteData = (array) $dados;
            $fabricante = self::resolveFabricante($fabricanteData);
            $fabricante->fromArray($fabricanteData);
            $fabricante->store();

            Transaction::close();

            return $fabricante;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    public static function loadForEdit($id)
    {
        Transaction::open('painel_comercial');

        try
        {
            $fabricante = Fabricante::find($id);

            if (!$fabricante)
            {
                throw new Exception('Fabricante não encontrado.');
            }

            Transaction::close();

            return $fabricante;
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    public static function delete($id)
    {
        Transaction::open('painel_comercial');

        try
        {
            $fabricante = Fabricante::find($id);

            if (!$fabricante)
            {
                throw new Exception('Registro não encontrado.');
            }

            $fabricante->delete();
            Transaction::close();
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw $e;
        }
    }

    private static function resolveFabricante(array $fabricanteData)
    {
        $id = isset($fabricanteData['id']) ? (int) $fabricanteData['id'] : 0;

        if ($id > 0)
        {
            $fabricante = Fabricante::find($id);
            if ($fabricante)
            {
                return $fabricante;
            }
        }

        return new Fabricante;
    }
}
