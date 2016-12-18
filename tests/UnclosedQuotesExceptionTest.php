<?php

use Clue\Arguments\UnclosedQuotesException;

class UnclosedQuotesExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testCtorWithOnlyQuotesAppliesMessage()
    {
        $e = new UnclosedQuotesException('"', 2);

        $this->assertEquals('"', $e->getQuotes());
        $this->assertEquals('Still in quotes (") from position 2', $e->getMessage());
    }

    public function testCtorAcceptsQuotesAndMessage()
    {
        $e = new UnclosedQuotesException('\'', 2, 'test');

        $this->assertEquals('\'', $e->getQuotes());
        $this->assertEquals('test', $e->getMessage());
    }
}
