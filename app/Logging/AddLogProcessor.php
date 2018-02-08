<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.02.2018
 * Time: 16:41
 */

namespace App\Logging;

use App\Logging\Processors\UserInfoProcessor;
use Monolog\Processor\WebProcessor;

/**
 * Class AddLogProcessor
 * @package App\Logging
 */
class AddLogProcessor
{
    /**
     * @param \Monolog\Logger $monolog
     */
    public function __invoke($monolog)
    {
        $monolog->pushProcessor(new UserInfoProcessor());
        $monolog->pushProcessor(new WebProcessor());
    }
}
