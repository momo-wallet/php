<?php


namespace MService\Payment\Shared\Utils;

use Exception;

class MoMoException extends Exception
{
    public function getErrorMessage(): string
    {
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
            . ":\n" . $this->getMessage()
            . ":\n" . $this->getTraceAsString()
            . "\n";

        return $errorMsg;
    }
}
