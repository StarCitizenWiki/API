<?php
/**
 * User: Hannes
 * Date: 28.01.2017
 * Time: 23:14
 */

namespace App\Exceptions;

use Exception;

<<<<<<< HEAD:app/Exceptions/MissingExtensionException.php
class MissingExtensionException extends Exception
=======
class InvalidDataException extends Exception
>>>>>>> Inject BaseStarCitizenAPI instead of Extending it:app/Exceptions/InvalidDataException.php
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}