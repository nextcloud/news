<?php


namespace OCA\News\Controller\Exceptions;

use Throwable;

class NotLoggedInException extends \Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'Unauthorized: User is not logged in!', 0, null);
    }
}
