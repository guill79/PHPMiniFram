<?php

namespace Fram\Database;

class NoRecordException extends \Exception
{
    protected $message = 'No record found in database';

    public function __construct()
    {
        parent::__construct($this->message, 1);
    }
}
