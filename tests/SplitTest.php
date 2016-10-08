<?php

use Clue\Arguments;

class SplitTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyString()
    {
        $args = Arguments\split('');

        $this->assertEquals(array(), $args);
    }

    public function testEmptyStringWithWhitespace()
    {
        $args = Arguments\split('    ');

        $this->assertEquals(array(), $args);
    }

    public function testSingleString()
    {
        $args = Arguments\split('hello');

        $this->assertEquals(array('hello'), $args);
    }

    public function testSingleStringWithWhitespace()
    {
        $args = Arguments\split('  hello  ');

        $this->assertEquals(array('hello'), $args);
    }

    public function testSimpleStringWithArgument()
    {
        $args = Arguments\split('hello world');

        $this->assertEquals(array('hello', 'world'), $args);
    }

    public function testSimpleStringWithArgumentsAndWhitespace()
    {
        $args = Arguments\split('  hello  world  ');

        $this->assertEquals(array('hello', 'world'), $args);
    }

    public function testSimpleStringWithArgumentsAndExcessiveWhitespace()
    {
        $args = Arguments\split("\n\n  hello \t  world\r\n");

        $this->assertEquals(array('hello', 'world'), $args);
    }

    public function testSimpleStringWithArgumentWithDoubleQuotes()
    {
        $args = Arguments\split('hello "world"');

        $this->assertEquals(array('hello', 'world'), $args);
    }

    public function testSingleStringWithDoubleQuotes()
    {
        $args = Arguments\split('"hello"');

        $this->assertEquals(array('hello'), $args);
    }

    public function testSingleStringWIthDoubleQuotesAndWhitespace()
    {
        $args = Arguments\split('  " hello "  ');

        $this->assertEquals(array(' hello '), $args);
    }

    public function testSingleStringWithDoubleQuotesAndEscapedQuote()
    {
        $bs = '\\';
        $args = Arguments\split('"he' . $bs . '"llo"');

        $this->assertEquals(array('he"llo'), $args);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSingleStringWithUnbalancedDoubleQuotesThrows()
    {
        Arguments\split('"hello');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSingleStringWithUnbalancedSingleQuotesThrows()
    {
        Arguments\split("'hello");
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSimpleStringWithUnbalancedSingleQuotesThrows()
    {
        Arguments\split("echo let's go");
    }

    public function testSingleStringWithAppendedDoubleQuotes()
    {
        $args = Arguments\split('he"llo"');

        $this->assertEquals(array('hello'), $args);
    }

    public function testDoubleQuotedWithAppendedString()
    {
        $args = Arguments\split('"he"llo');

        $this->assertEquals(array('hello'), $args);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSimpleStringWithUnbalancedDoubleQuotesThrows()
    {
        Arguments\split('hello "world');
    }

    public function testSingleStringWithDoubleQuotesAndDoubleEscape()
    {
        $bs = "\\";
        $args = Arguments\split('"he' . $bs . $bs . '"');

        $this->assertEquals(array('he\\'), $args);
    }

    public function testSingleStringWithDoubleQuotesAndInterpretedEscapes()
    {
        $bs = "\\";
        $args = Arguments\split('"he' . $bs . 'r' . $bs . 'nllo"');

        $this->assertEquals(array("he\r\nllo"), $args);
    }

    public function testSingleStringWithSingleQuotesAndDoubleEscape()
    {
        $bs = "\\";
        $args = Arguments\split("'he" . $bs . $bs . "'");

        $this->assertEquals(array('he\\'), $args);
    }

    public function testSingleStringWithSingleQuotesAndIgnoredEscapes()
    {
        $bs = "\\";
        $args = Arguments\split("'he" . $bs . 'r' . $bs . "nllo'");

        $this->assertEquals(array('he\r\nllo'), $args);
    }

    public function testSingleStringWithSingleQuotesAndInterpretedEscapes()
    {
        $bs = "\\";
        $args = Arguments\split("echo 'let" . $bs . "'s go'");

        $this->assertEquals(array("echo", "let's go"), $args);
    }
}
