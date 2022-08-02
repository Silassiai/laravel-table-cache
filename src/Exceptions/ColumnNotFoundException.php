<?php

namespace Silassiai\LaravelTableCache\Exceptions;

use Exception;

class ColumnNotFoundException extends Exception
{
    public function __construct(string $column, string $table)
    {
        parent::__construct();

        $this->message = "Column [$column] not found in table [$table].";
    }
}
