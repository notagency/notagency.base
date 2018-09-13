<?php

namespace Notagency\Base;

class Exception extends \Exception {
    
    public function __construct($message = "", $symbolicCode = "", \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        if (!empty($symbolicCode)) {
            $this->symbolicCode = $symbolicCode;
        }
    }
}

?>
