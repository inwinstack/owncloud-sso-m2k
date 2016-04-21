<?php

namespace OCA\SingleSignOn;

class ValidToken implements ISingleSignOnRequest {
    private $connection;
    private $errorMsg;
    
    public function __construct($connection){
        $this->connection = $connection;
    }

    public function name() {
        return ISingleSignOnRequest::VALIDTOKEN;    
    }

    public function send($data = null) {
        return true;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }
}

