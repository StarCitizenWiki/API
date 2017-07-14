<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 14.07.2017
 * Time: 13:20
 */

namespace App\Traits;

use \App\Facades\Log;

/**
 * Trait ExecutionTimeTrait
 * @package App\Traits
 */
trait ExecutionTimeTrait
{
    protected static $startTime;

    protected static $endTime;

    protected static $totalExecutionTime;

    protected static function startExecutionTimer() : void
    {
        Log::debug('Start '.debug_backtrace()[1]['function']);
        self::$startTime = microtime(true);
    }

    protected static function endExecutionTimer() : void
    {
        self::$endTime =  microtime(true);
        Log::debug('End '.debug_backtrace()[1]['function'], self::getExecutionTimeAsAssocArray());
    }

    /**
     * @return float
     */
    protected static function getExecutionTime() : float
    {
        if (is_null(self::$endTime)) {
            self::endExecutionTimer();
        }

        self::$totalExecutionTime = self::$endTime - self::$startTime;

        return self::$totalExecutionTime;
    }

    /**
     * @return array
     */
    protected static function getExecutionTimeAsAssocArray() : array
    {
        $data = [
            'total' => self::getExecutionTime().' ms',
            'start_time' => self::$startTime,
            'end_time' => self::$endTime,
        ];

        return ['execution_time' => $data];
    }
}
