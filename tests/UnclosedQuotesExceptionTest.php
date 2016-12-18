<?php

use Clue\Arguments\UnclosedQuotesException;

class UnclosedQuotesExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testCtorWithOnlyQuotesAppliesMessage()
    {
        $e = new UnclosedQuotesException('"');

        $this->assertEquals('"', $e->getQuotes());
        $this->assertEquals('Still in quotes (")', $e->getMessage());
    }

    public function testCtorAcceptsQuotesAndMessage()
    {
        $e = new UnclosedQuotesException('\'', 'test');

        $this->assertEquals('\'', $e->getQuotes());
        $this->assertEquals('test', $e->getMessage());
    }
}
