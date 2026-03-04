<?php
use Nexa\Database\Transaction;

class PessoaApplicationService
{
    public static function save($dados)
    {
        Transaction::open('painel_comercial');

        try
        {
            $pessoaData = (array) $dados;
            $idsGrupos = isset($pessoaData['ids_grupos']) ? (array) $pessoaData['ids_grupos'] : [];
            unset($pessoaData['ids_grupos']);

            $pessoa = self::resolvePessoa($pessoaData);
            $pessoa->fromArray($pessoaData);
            $pessoa->store();

            self::syncGroups($pessoa, $idsGrupos);

            Transaction::close();

            return $pessoa;
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
            $pessoa = Pessoa::find($id);

            if (!$pessoa)
            {
                throw new Exception('Pessoa não encontrada.');
            }

            $pessoa->ids_grupos = $pessoa->getIdsGrupos();

            Transaction::close();

            return $pessoa;
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
            $pessoa = Pessoa::find($id);

            if (!$pessoa)
            {
                throw new Exception('Registro não encontrado.');
            }

            $pessoa->delete();
            Transaction::close();
        }
        catch (Throwable $e)
        {
            Transaction::rollback();
            throw new Exception(DatabaseErrorService::toUserMessage($e, 'Não foi possível excluir a pessoa.'), 0, $e);
        }
    }

    private static function resolvePessoa(array $pessoaData)
    {
        $id = isset($pessoaData['id']) ? (int) $pessoaData['id'] : 0;

        if ($id > 0)
        {
            $pessoa = Pessoa::find($id);
            if ($pessoa)
            {
                return $pessoa;
            }
        }

        return new Pessoa;
    }

    private static function syncGroups(Pessoa $pessoa, array $idsGrupos)
    {
        $pessoa->delGrupos();

        foreach ($idsGrupos as $idGrupo)
        {
            $idGrupo = (int) $idGrupo;

            if ($idGrupo > 0)
            {
                $pessoa->addGrupo(new Grupo($idGrupo));
            }
        }
    }
}
