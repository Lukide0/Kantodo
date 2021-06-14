<?php

namespace Kantodo\Core\Exception;

use Exception;

class NotAuthorizedException extends Exception {
    public $code = 401;
}

?>