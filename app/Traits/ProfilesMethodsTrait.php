<?php
/**
 * User: Hannes
 * Date: 17.07.2017
 * Time: 18:35
 */

namespace App\Traits;

/**
 * Trait ProfilesClassTrait
 * @package App\Traits
 */
trait ProfilesMethodsTrait
{
    protected $log = [];
    protected $curID;
    protected $idStack = [];

    protected $destructed = false;

    /**
     * starts the profiler
     *
     * @param String $functionName
     */
    protected function startProfiling(String $functionName) : void
    {
        $this->curID = $functionName;
        $this->idStack[] = $this->curID;
        $this->log[$functionName]['exec_time'] = microtime(true);
        $this->addTrace("Profiling for {$functionName} started", $functionName);
    }

    /**
     * Stops the profiler
     *
     * @param String $functionName
     */
    protected function stopProfiling(String $functionName) : void
    {
        $this->log[$functionName]['exec_time'] = microtime(true) - $this->log[$functionName]['exec_time'];
        $this->addTrace("Profiling for {$functionName} finished", $functionName);
    }

    /**
     * Adds Line and Message to Log
     *
     * @param String $message
     * @param String $functionName
     * @param int    $line
     */
    protected function addTrace(String $message, String $functionName, int $line = 0) : void
    {
        if ($line > 0) {
            $message = "[Line: {$line}] {$message}";
        }

        $this->log[$functionName]['log'][] = $message;
    }

    /**
     * Adds Monolog Processors and writes Log-Data to debug log
     */
    public function __destruct()
    {
        if (!$this->destructed && config('app.log_profiling')) {
            app('Log')::debug("Profiling for Class ".__CLASS__." finished", $this->log);
            $this->destructed = true;
        }
    }
}
