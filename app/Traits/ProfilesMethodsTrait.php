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
        $this->addTrace($functionName, "Profiling for {$functionName} started");
    }

    /**
     * Stops the profiler
     *
     * @param String $functionName
     */
    protected function stopProfiling(String $functionName) : void
    {
        $this->log[$functionName]['exec_time'] = microtime(true) - $this->log[$functionName]['exec_time'];
        $this->addTrace($functionName, "Profiling for {$functionName} finished");
    }

    /**
     * Adds Line and Message to Log
     *
     * @param String $functionName
     * @param String $message
     * @param int    $line
     */
    protected function addTrace(String $functionName, String $message, int $line = 0) : void
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
        if (!$this->destructed) {
            app('Log')::debug("Profiling for Class ".__CLASS__." finished", $this->log);
            $this->destructed = true;
        }
    }
}
