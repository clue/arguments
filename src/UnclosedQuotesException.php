<?php

namespace Clue\Arguments;

use InvalidArgumentException;

class UnclosedQuotesException extends InvalidArgumentException
{
    private $quotes;
    private $position;

    /**
     * @internal
     * @param string     $quotes
     * @param int        $position
     * @param ?string    $message
     * @param int        $code
     * @param ?Exception $previous
     */
    public function __construct($quotes, $position, $message = null, $code = 0, $previous = null)
    {
        if ($message === null) {
            $message = 'Still in quotes (' . $quotes  . ') from position ' . $position;
        }

        parent::__construct($message, $code, $previous);

        $this->quotes = $quotes;
        $this->position = $position;
    }

    /**
     * Returns the quotes this argument started with
     *
     * @return string
     */
    public function getQuotes()
    {
        return $this->quotes;
    }

    /**
     * Returns the character position of the quotes within the input
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
