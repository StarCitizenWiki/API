<?php
/**
 * User: Hannes
 * Date: 03.05.2017
 * Time: 13:47
 */

namespace App\Facades;

/**
 * Class Log
 * @package App\Facades
 */
class Log extends \Illuminate\Support\Facades\Log
{
    /**
     * Extend default warning Function to include Class and Method name
     *
     * @param mixed $message
     * @param array $context
     */
    public static function warning($message, $context = [])
    {
        $context = self::prepareContext($context);
        $context = self::addInfoToContext($context);

        parent::warning($message, $context);
    }

    /**
     * Extend default debug Function to include Class and Method name
     *
     * @param mixed $message
     * @param array $context
     */
    public static function debug($message, $context = [])
    {
        $context = self::prepareContext($context);
        $context = self::addInfoToContext($context);

        parent::debug($message, $context);
    }

    /**
     * Ensures that context is an array
     *
     * @param $context
     *
     * @return array
     */
    private static function prepareContext($context) : array
    {
        if (!is_array($context) && !empty($context)) {
            $context = ['data' => $context];
        }

        return $context;
    }

    /**
     * Adds calling Class and Method to Context
     *
     * @param array $context
     *
     * @return array
     */
    private static function addInfoToContext(array $context) : array
    {
        $context['class'] = debug_backtrace()[2]['class'] ?? debug_backtrace()[1]['class'];
        $context['method'] = debug_backtrace()[2]['function'];

        return $context;
    }
}
