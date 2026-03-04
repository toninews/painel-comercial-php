<?php
namespace Nexa\Database;

/**
 * Permite definição de critérios
 */
class Criteria
{
    private $properties;
    private $filters; // armazena a lista de filtros
    private $preparedVars;
    
    /**
     * Método Construtor
     */
    function __construct()
    {
        $this->filters = array();
        $this->preparedVars = array();
    }
    
    /**
     * Adiciona uma expressão ao critério
     * @param $variable           Variável/campo
     * @param $compare_operator   Operador de comparação
     * @param $value              Valor a ser comparado
     * @param $logic_operator     Operador lógico
     */
    public function add($variable, $compare_operator, $value, $logic_operator = 'and')
    {
        // na primeira vez, não precisamos concatenar
        if (empty($this->filters))
        {
            $logic_operator = NULL;
        }

        $this->filters[] = [$variable, strtoupper($compare_operator), $value, $logic_operator];
    }
    
    /**
     * Retorna a expressão final
     */
    public function dump()
    {
        $this->preparedVars = array();

        // concatena a lista de expressões
        if (is_array($this->filters) and count($this->filters) > 0)
        {
            $result = array();
            foreach ($this->filters as $filter)
            {
                $logic = $filter[3] ? strtoupper($filter[3]) . ' ' : '';
                $result[] = $logic . $this->buildFilter($filter[0], $filter[1], $filter[2]);
            }

            return '(' . implode(' ', $result) . ')';
        }
    }

    public function getPreparedVars()
    {
        return $this->preparedVars;
    }
    
    /**
     * Define o valor de uma propriedade
     * @param $property = propriedade
     * @param $value    = valor
     */
    public function setProperty($property, $value)
    {
        if (isset($value))
        {
            $this->properties[$property] = $value;
        }
        else
        {
            $this->properties[$property] = NULL;
        }
    }
    
    /**
     * Retorna o valor de uma propriedade
     * @param $property = propriedade
     */
    public function getProperty($property)
    {
        if (isset($this->properties[$property]))
        {
            return $this->properties[$property];
        }
    }

    private function buildFilter($variable, $compareOperator, $value)
    {
        $variable = $this->sanitizeIdentifier($variable);

        if (is_null($value))
        {
            if ($compareOperator === '=')
            {
                return "{$variable} IS NULL";
            }

            if ($compareOperator === '<>' || $compareOperator === '!=')
            {
                return "{$variable} IS NOT NULL";
            }
        }

        if (is_array($value))
        {
            if (!in_array($compareOperator, array('IN', 'NOT IN'))) {
                throw new \InvalidArgumentException('Operador invalido para filtro com array');
            }

            $placeholders = array();

            foreach ($value as $item)
            {
                $placeholder = $this->createNamedPlaceholder();
                $placeholders[] = $placeholder;
                $this->preparedVars[$placeholder] = $item;
            }

            if (empty($placeholders)) {
                throw new \InvalidArgumentException('Filtro com array vazio nao e permitido');
            }

            return "{$variable} {$compareOperator} (" . implode(', ', $placeholders) . ')';
        }

        $compareOperator = $this->sanitizeOperator($compareOperator);
        $placeholder = $this->createNamedPlaceholder();
        $this->preparedVars[$placeholder] = $value;

        return "{$variable} {$compareOperator} {$placeholder}";
    }

    private function createNamedPlaceholder()
    {
        return ':p' . count($this->preparedVars);
    }

    private function sanitizeIdentifier($value)
    {
        if (!is_string($value) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value))
        {
            throw new \InvalidArgumentException('Identificador SQL invalido');
        }

        return $value;
    }

    private function sanitizeOperator($value)
    {
        $allowedOperators = array('=', '<', '>', '<=', '>=', '<>', '!=', 'LIKE', 'IN', 'NOT IN');

        if (!in_array($value, $allowedOperators, true))
        {
            throw new \InvalidArgumentException('Operador SQL invalido');
        }

        return $value;
    }
}
