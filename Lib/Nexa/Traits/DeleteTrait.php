<?php
namespace Nexa\Traits;

use Nexa\Control\Action;
use Nexa\Database\Transaction;
use Nexa\Widgets\Dialog\Message;
use Nexa\Widgets\Dialog\Question;

use Exception;

trait DeleteTrait
{
    /**
     * Pergunta sobre a exclusão de registro
     */
    function onDelete($param)
    {
        $id = isset($param['id']) ? $param['id'] : $param['key'];
        $action1 = new Action(array($this, 'onDeleteConfirmed'));
        $action1->setParameter('id', $id);
        
        new Question('Deseja realmente excluir o registro?', $action1);
    }

    /**
     * Exclui um registro
     */
    function onDeleteConfirmed($param)
    {
        try
        {
            $id = $param['id']; // obtém a chave
            Transaction::open( $this->connection ); // inicia transação com o BD
            
            $class = $this->activeRecord;
            
            $object = $class::find($id); // instancia objeto
            if (!$object)
            {
                throw new Exception('Registro nao encontrado');
            }
            $object->delete(); // deleta objeto do banco de dados
            Transaction::close(); // finaliza a transação
            $this->onReload(); // recarrega a datagrid
            new Message('info', "Registro excluído com sucesso");
        }
        catch (Exception $e)
        {
            Transaction::rollback();
            new Message('error', $e->getMessage());
        }
    }
}
