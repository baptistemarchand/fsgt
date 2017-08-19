<?php
declare(strict_types=1);

namespace AppBundle\Exception;

use Exception;

class UserException extends Exception {
    public function __construct($message) {
        parent::__construct($message, 0, null);
    }
}
