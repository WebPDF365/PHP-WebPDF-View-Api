<?php

/**
 * Class ViewException
 */
class ViewException extends Exception {  
    // Redefine the exception so message isn't optional.
    public function __construct($message, $code = 0) {  
        // Make sure everything is assigned properly.
        parent::__construct($message, $code);  
    }
}  