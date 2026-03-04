<?php
namespace Nexa\Database;

use Exception;

/**
 * Permite definir um Active Record
 */
abstract class Record implements RecordInterface
{
    protected $data; // array contendo os dados do objeto
    
    /**
     * Instancia um Active Record. Se passado o $id, já carrega o objeto
     * @param [$id] = ID do objeto
     */
    public function __construct($id = NULL)
    {
        if ($id) // se o ID for informado
        {
            // carrega o objeto correspondente
            $object = $this->load($id);
            if ($object)
            {
                $this->fromArray($object->toArray());
            }
        }
    }
    
    /**
     * Limpa o ID para que seja gerado um novo ID para o clone.
     */
    public function __clone()
    {
        unset($this->data['id']);
    }
    
    /**
     * Executado sempre que uma propriedade for atribuída.
     */
    public function __set($prop, $value)
    {
        // verifica se existe método set_<propriedade>
        if (method_exists($this, 'set_'.$prop))
        {
            // executa o método set_<propriedade>
            call_user_func(array($this, 'set_'.$prop), $value);
        }
        else
        {
            if ($value === NULL)
            {
                unset($this->data[$prop]);
            }
            else
            {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }
    
    /**
     * Executado sempre que uma propriedade for requerida
     */
    public function __get($prop)
    {
        // verifica se existe método get_<propriedade>
        if (method_exists($this, 'get_'.$prop))
        {
            // executa o método get_<propriedade>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // retorna o valor da propriedade
            if (isset($this->data[$prop]))
            {
                return $this->data[$prop];
            }
        }
    }
    
    /**
     * Retorna se a propriedade está definida
     */
    public function __isset($prop)
    {
        return isset($this->data[$prop]);
    }
    
    /**
     * Preenche os dados do objeto com um array
     */
    public function fromArray($data)
    {
        $this->data = $data;
    }
    
    /**
     * Retorna os dados do objeto como array
     */
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * Armazena o objeto na base de dados
     */
    public function store()
    {
        if (array_key_exists('id', (array) $this->data) && ($this->data['id'] === '' || $this->data['id'] === null))
        {
            unset($this->data['id']);
        }

        $prepared = $this->prepare($this->data);
        $hasDefinedId = !empty($this->data['id']);
        $isInsert = false;
        
        // verifica se tem ID ou se existe na base de dados
        if (empty($this->data['id']) or (!$this->load($this->id)))
        {
            $isInsert = true;
            
            // cria uma instrução de insert
            $columns = array_keys($prepared);
            $placeholders = array();

            foreach ($columns as $column)
            {
                $placeholders[] = ':' . $column;
            }

            $sql = "INSERT INTO {$this->getEntity()} " .
                   '(' . implode(', ', $columns) . ') ' .
                   'VALUES ' .
                   '(' . implode(', ', $placeholders) . ')';
        }
        else
        {
            // monta a string de UPDATE
            $sql = "UPDATE {$this->getEntity()}";
            // monta os pares: coluna=valor,...
            $set = array();
            if ($prepared) {
                foreach ($prepared as $column => $value) {
                    if ($column !== 'id') {
                        $set[] = "{$column} = :{$column}";
                    }
                }
            }

            if (empty($set)) {
                return 0;
            }

            $sql .= ' SET ' . implode(', ', $set);
            $sql .= ' WHERE id = :id';
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

            if ($isInsert && !$hasDefinedId && $driver === 'pgsql') {
                $sql .= ' RETURNING id';
            }

            // faz o log e executa o SQL
            Transaction::log($sql);
            $statement = $conn->prepare($sql);
            $statement->execute($prepared);

            if ($isInsert && !$hasDefinedId)
            {
                if ($driver === 'pgsql')
                {
                    $this->id = (int) $statement->fetchColumn();
                }
                else
                {
                    $this->id = (int) $conn->lastInsertId();
                }
            }

            // retorna o resultado
            return $statement->rowCount();
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /*
     * Recupera (retorna) um objeto da base de dados pelo seu ID
     * @param $id = ID do objeto
     */
    public function load($id)
    {
        // instancia instrução de SELECT
        $sql = "SELECT * FROM {$this->getEntity()}";
        $sql .= ' WHERE id = :id';
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // cria mensagem de log e executa a consulta
            Transaction::log($sql);
            $statement = $conn->prepare($sql);
            $statement->execute(array(':id' => (int) $id));
            
            // se retornou algum dado
            if ($statement)
            {
                // retorna os dados em forma de objeto
                $object = $statement->fetchObject(get_class($this));
            }
            return $object;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Exclui um objeto da base de dados através de seu ID.
     * @param $id = ID do objeto
     */
    public function delete($id = NULL)
    {
        // o ID é o parâmetro ou a propriedade ID
        $id = $id ? $id : $this->id;
        
        // monsta a string de UPDATE
        $sql  = "DELETE FROM {$this->getEntity()}";
        $sql .= ' WHERE id = :id';
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // faz o log e executa o SQL
            Transaction::log($sql);
            $statement = $conn->prepare($sql);
            $statement->execute(array(':id' => (int) $id));
            // retorna o resultado
            return $statement->rowCount();
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Retorna todos objetos
     */
    public static function all()
    {
        $classname = get_called_class();
        $rep = new Repository($classname);
        return $rep->load(new Criteria);
    }
    
    /**
     * Busca um objeto pelo id
     */
    public static function find($id)
    {
        $classname = get_called_class();
        $ar = new $classname;
        return $ar->load($id);
    }
    
    public function prepare($data)
    {
        $prepared = array();
        foreach ($data as $key => $value)
        {
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key) && (is_scalar($value) || is_null($value)))
            {
                $prepared[$key] = $value;
            }
        }
        return $prepared;
    }

    private function getEntity()
    {
        $class = get_class($this);
        $table = constant("{$class}::TABLENAME");

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table))
        {
            throw new Exception('Nome de tabela invalido');
        }

        return $table;
    }
}
