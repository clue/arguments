<?php

namespace Clue\Arguments;

use RuntimeException;

class UnclosedQuotesException extends RuntimeException
{
    private $quotes;

    /**
     * @internal
     * @param string     $quotes
     * @param ?string    $message
     * @param int        $code
     * @param ?Exception $previous
     */
    public function __construct($quotes, $message = null, $code = 0, $previous = null)
    {
        if ($message === null) {
            $message = 'Still in quotes (' . $quotes  . ')';
        }

        parent::__construct($message, $code, $previous);

        $this->quotes = $quotes;
    }

    /**
     * Returns the qutoes this argument started with
     *
     * @return string
     */
    public function getQuotes()
    {
        return $this->quotes;
    }
}
