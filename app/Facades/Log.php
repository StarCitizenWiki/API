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
     * Passes all static calls (debug, info, ...) to Parent
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $message = $args[0] ?? "";
        $context = $args[1] ?? [];
        $context = self::prepareContext($context);
        $context = self::addInfoToContext($context);

        return parent::__callStatic($method, [$message, $context]);
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
        $context['object'] = [
            'class' => debug_backtrace()[2]['class'] ?? debug_backtrace()[1]['class'],
            'method' => debug_backtrace()[2]['function'],
        ];

        return $context;
    }
}
