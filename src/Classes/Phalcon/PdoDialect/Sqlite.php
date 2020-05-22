<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon\PdoDialect;

use Exception;
use Phalcon\Db\Dialect;
use Phalcon\Db\Dialect\Sqlite as PhalconSqlite;

class Sqlite extends PhalconSqlite
{
    /**
     * Sqlite constructor.
     */
    public function __construct()
    {
        $this->registerCustomFunction('CONCAT_WS', [$this, 'concatWs']);
        $this->registerCustomFunction('CONCAT', [$this, 'concat']);
    }

    /**
     * @param Dialect|null $dialect
     * @param $expression
     * @return string
     * @throws Exception
     */
    public function concat(Dialect $dialect, $expression)
    {
        $arguments = [];

        foreach ($expression['arguments'] as $argument) {
            $arguments[] = $dialect->getSqlExpression($argument);
        }

        return implode(' || ', $arguments);
    }

    /**
     * @param Dialect|null $dialect
     * @param $expression
     * @return string
     * @throws Exception
     */
    public function concatWs(Dialect $dialect, $expression)
    {
        $sql = '';

        $count = count($expression['arguments']);

        if (true !== $count >= 2) {
            throw new Exception('CONCAT_WS requires 2 or more parameters');
        }

        if (2 === $count) {
            return $dialect->getSqlExpression($expression['arguments'][1]);
        }

        $separator = array_shift($expression['arguments']);

        --$count;
        foreach ($expression['arguments'] as $argument) {
            $sql .= $dialect->getSqlExpression($argument);
            if (0 !== --$count) {
                $sql .= ' || ' . $dialect->getSqlExpression($separator) . ' || ';
            }
        }

        return $sql;
    }
}