<?php
/**
 * User: Hannes
 * Date: 29.01.2017
 * Time: 13:20
 */

namespace App\Exceptions;

use Exception;

<<<<<<< HEAD:app/Exceptions/EmptyResponseException.php
class EmptyResponseException extends Exception
=======
class InvalidDataException extends \Exception
>>>>>>> Add Validity check to request:app/Exceptions/InvalidDataException.php
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}