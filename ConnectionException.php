<?php

namespace Source\Crud;

use Exception;

class ConnectionException extends Exception
{ // Redefine a exceção de forma que a mensagem não seja opcional
    public function __construct($message, $code = 0)
    {
        // garante que tudo foi inicilizado corretamente
        parent::__construct($message, $code);
    }

    // string personalizada para representação do objeto
    public function __toString()
    {
        return ": [{$this->code}]: {$this->message}\n";
    }

    public function getError()
    {
        return ["message" => $this->getMessage(), "codeError" => $this->getCode()];
    }
}
