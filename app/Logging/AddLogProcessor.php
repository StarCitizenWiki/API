<?php declare(strict_types = 1);

namespace App\Logging;

use App\Logging\Processors\UserInfoProcessor;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

/**
 * Class AddLogProcessor
 */
class AddLogProcessor
{
    /**
     * @param Logger $monolog
     */
    public function __invoke($monolog)
    {
        $monolog->pushProcessor(new UserInfoProcessor());
        $monolog->pushProcessor(new WebProcessor());
    }
}
