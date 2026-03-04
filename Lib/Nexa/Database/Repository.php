<?php
namespace Nexa\Database;

use Exception;

/**
 * Manipular coleções de objetos.
 */
final class Repository
{
    private $activeRecord; // classe manipulada pelo repositório
    
    /**
     * Instancia um Repositório de objetos
     * @param $class = Classe dos Objetos
     */
    function __construct($class)
    {
        $this->activeRecord = $class;
    }
    
    /**
     * Carrega um conjunto de objetos (collection) da base de dados
     * @param $criteria = objeto do tipo TCriteria
     */
    function load(Criteria $criteria)
    {
        // instancia a instrução de SELECT
        $sql = "SELECT * FROM " . $this->getEntity();
        $preparedVars = array();
        
        // obtém a cláusula WHERE do objeto criteria.
        if ($criteria)
        {
            $expression = $criteria->dump();
            $preparedVars = $criteria->getPreparedVars();
            if ($expression)
            {
                $sql .= ' WHERE ' . $expression;
            }
            
            // obtém as propriedades do critério
            $order = $criteria->getProperty('order');
            $limit = $criteria->getProperty('limit');
            $offset= $criteria->getProperty('offset');
            
            // obtém a ordenação do SELECT
            if ($order) {
                $sql .= ' ORDER BY ' . $this->sanitizeOrder($order);
            }
            if ($limit) {
                $sql .= ' LIMIT ' . (int) $limit;
            }
            if ($offset) {
                $sql .= ' OFFSET ' . (int) $offset;
            }
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // registra mensagem de log
            Transaction::log($sql);
            
            // executa a consulta no banco de dados
            $statement = $conn->prepare($sql);
            $statement->execute($preparedVars);
            $results = array();
            
            if ($statement)
            {
                // percorre os resultados da consulta, retornando um objeto
                while ($row = $statement->fetchObject($this->activeRecord))
                {
                    // armazena no array $results;
                    $results[] = $row;
                }
            }
            return $results;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Excluir um conjunto de objetos (collection) da base de dados
     * @param $criteria = objeto do tipo Criteria
     */
    function delete(Criteria $criteria)
    {
        $expression = $criteria->dump();
        $preparedVars = $criteria->getPreparedVars();
        $sql = "DELETE FROM " . $this->getEntity();
        if ($expression)
        {
            $sql .= ' WHERE ' . $expression;
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // registra mensagem de log
            Transaction::log($sql);
            // executa instrução de DELETE
            $statement = $conn->prepare($sql);
            $statement->execute($preparedVars);
            return $statement->rowCount();
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
            
        }
    }
    
    /**
     * Retorna a quantidade de objetos da base de dados
     * que satisfazem um determinado critério de seleção.
     * @param $criteria = objeto do tipo TCriteria
     */
    function count(Criteria $criteria)
    {
        $expression = $criteria->dump();
        $preparedVars = $criteria->getPreparedVars();
        $sql = "SELECT count(*) FROM " . $this->getEntity();
        if ($expression)
        {
            $sql .= ' WHERE ' . $expression;
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // registra mensagem de log
            Transaction::log($sql);
            
            // executa instrução de SELECT
            $statement = $conn->prepare($sql);
            $statement->execute($preparedVars);
            if ($statement)
            {
                $row = $statement->fetch();
            }
            // retorna o resultado
            return $row[0];
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    private function getEntity()
    {
        $table = constant($this->activeRecord.'::TABLENAME');

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table))
        {
            throw new Exception('Nome de tabela invalido');
        }

        return $table;
    }

    private function sanitizeOrder($order)
    {
        if (!is_string($order))
        {
            throw new Exception('Clausula ORDER BY invalida');
        }

        $order = trim($order);

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\s+(ASC|DESC))?$/i', $order))
        {
            throw new Exception('Clausula ORDER BY invalida');
        }

        return $order;
    }
}
