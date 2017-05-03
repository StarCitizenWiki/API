<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 08.03.2017
 * Time: 13:46
 */

namespace App\Exceptions;

use Exception;

/**
 * Class MissingTransformerException
 * Exteption to throw if a transformer is not set but used
 *
 * @package App\Exceptions
 */
class MissingTransformerException extends Exception
{

}
