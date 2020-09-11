<?php declare(strict_types=1);

namespace KikCMS\Services\Cms;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Models\QueryLog;
use KikCMS\ObjectLists\QueryLogMap;
use Phalcon\Events\Event;

class QueryLogService extends Injectable
{
    /** @var int */
    private $index = 1;

    /** @var array */
    private $timingMap = [];

    /** @var QueryLogMap */
    private $queryLogMap;

    /**
     * Setup the query logger
     */
    public function setup()
    {
        $this->queryLogMap = new QueryLogMap();

        $this->eventsManager->attach('db:beforeQuery', function () {
            $this->startTiming();
        });

        $this->eventsManager->attach('db:afterQuery', function ($event, $connection) {
            $this->addToLog($event, $connection);
        });

        register_shutdown_function(function () {
            $this->saveLog();
        });
    }

    /**
     * @param Event $event
     * @param $connection
     * @noinspection PhpUnusedParameterInspection
     */
    private function addToLog(Event $event, $connection)
    {
        $query = $connection->getSQLStatement();
        $hash  = md5($query);
        $time  = (microtime(true) - $this->timingMap[$this->index]) * 1000;

        if ($this->queryLogMap->has($hash)) {
            $this->queryLogMap->get($hash)->called++;
            $this->queryLogMap->get($hash)->time += $time;
        } else {
            $queryLog         = new QueryLog();
            $queryLog->query  = $query;
            $queryLog->called = 1;
            $queryLog->time   = $time;
            $queryLog->hash   = $hash;

            $this->queryLogMap->add($queryLog, $hash);
        }

        $this->index++;
    }

    /**
     * Store the log entries in the db
     */
    private function saveLog()
    {
        $total = 0;

        // prevent the log inserts to be logged
        $queryLogMap = clone $this->queryLogMap;

        foreach ($queryLogMap as $queryLog) {
            $queryLog->save();
            $total += $queryLog->called;
        }

        if($this->config->isDev()){
            dlog('Query count: ' . $total);
        }
    }

    /**
     * Start timing the current query
     */
    private function startTiming()
    {
        $this->timingMap[$this->index] = microtime(true);
    }
}
