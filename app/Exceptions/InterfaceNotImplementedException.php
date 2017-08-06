<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.03.2017
 * Time: 20:13
 */

namespace App\Exceptions;

use BadMethodCallException;

/**
 * Class InterfaceNotImplementedException
 * Extension to throw if an required Interface is not implemented
 *
 * @package App\Exceptions
 */
class InterfaceNotImplementedException extends BadMethodCallException
{

}
