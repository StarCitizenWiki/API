<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 17.07.2017
 * Time: 18:35
 */

namespace App\Traits;

/**
 * Trait ProfilesClassTrait
 *
 * @package App\Traits
 */
trait ProfilesMethodsTrait
{
    protected $log = [];
    protected $curID;
    protected $idStack = [];

    protected $destructed = false;

    /**
     * Adds Monolog Processors and writes Log-Data to debug log
     */
    public function __destruct()
    {
        if (!$this->destructed && env('APP_LOG_PROFILING', false)) {
            app('Log')::debug("Profiling for Class ".__CLASS__." finished", $this->log);
            $this->destructed = true;
        }
    }

    /**
     * starts the profiler
     *
     * @param string $functionName
     */
    protected function startProfiling(string $functionName): void
    {
        $this->curID = $functionName;
        $this->idStack[] = $this->curID;
        $this->log[$functionName]['exec_time'] = microtime(true);
        $this->addTrace("Profiling for {$functionName} started", $functionName);
    }

    /**
     * Adds Line and Message to Log
     *
     * @param string $message
     * @param string $functionName
     * @param int    $line
     */
    protected function addTrace(string $message, string $functionName, int $line = 0): void
    {
        if ($line > 0) {
            $message = "[Line: {$line}] {$message}";
        }

        $this->log[$functionName]['log'][] = $message;
    }

    /**
     * Stops the profiler
     *
     * @param string $functionName
     */
    protected function stopProfiling(string $functionName): void
    {
        $this->log[$functionName]['exec_time'] = microtime(true) - $this->log[$functionName]['exec_time'];
        $this->addTrace("Profiling for {$functionName} finished", $functionName);
    }
}
