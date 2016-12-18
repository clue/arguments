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

    public function testSingleStringWithEscapedWhitespace()
    {
        $args = Arguments\split('hello\\ world');

        $this->assertEquals(array('hello world'), $args);
    }

    public function testSimpleStringWithArgument()
    {
        $args = Arguments\split('hello world');

        $this->assertEquals(array('hello', 'world'), $args);
    }

    public function testSimpleStringWithArgumentWithInterpretedEscape()
    {
        $args = Arguments\split('hello world\\nthere!');

        $this->assertEquals(array('hello', "world\nthere!"), $args);
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
     * @expectedException Clue\Arguments\UnclosedQuotesException
     */
    public function testSingleStringWithUnbalancedDoubleQuotesThrows()
    {
        Arguments\split('"hello');
    }

    /**
     * @expectedException Clue\Arguments\UnclosedQuotesException
     */
    public function testSingleStringWithUnbalancedSingleQuotesThrows()
    {
        Arguments\split("'hello");
    }

    /**
     * @expectedException Clue\Arguments\UnclosedQuotesException
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
     * @expectedException Clue\Arguments\UnclosedQuotesException
     */
    public function testSimpleStringWithUnbalancedDoubleQuotesThrows()
    {
        Arguments\split('hello "world');
    }

    public function testSimpleStringWithUnbalancedDoubleQuotesThrowsWithCorrectQuotes()
    {
        try {
            Arguments\split('hello "world');
            $this->fail();
        } catch (Arguments\UnclosedQuotesException $e) {
            $this->assertEquals('"', $e->getQuotes());
            $this->assertEquals(6, $e->getPosition());
        }
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

    public function testSingleStringWithInterpretedEscapes()
    {
        $args = Arguments\split('hello\\\\');

        $this->assertEquals(array("hello\\"), $args);
    }

    public function testSingleStringWithInterpretedIncompleteEscapes()
    {
        $args = Arguments\split('hello\\');

        $this->assertEquals(array("hello\\"), $args);
    }

    public function testSingleStringWithInterpretedHexEscapes()
    {
        $args = Arguments\split('hello\x20world');

        $this->assertEquals(array("hello world"), $args);
    }

    public function testSingleStringWithInterpretedIncompleteHexEscapesEnd()
    {
        $args = Arguments\split('hello\x9');

        $this->assertEquals(array("hello\t"), $args);
    }

    public function testSingleStringWithInterpretedIncompleteHexEscapesMiddle()
    {
        $args = Arguments\split('hello\x9world');

        $this->assertEquals(array("hello\tworld"), $args);
    }

    public function testSingleStringWithInterpretedOctalEscapes()
    {
        $args = Arguments\split('hello\040world');

        $this->assertEquals(array("hello world"), $args);
    }

    public function testSingleStringWithInterpretedShortOctalEscapes()
    {
        $args = Arguments\split('hello\40world');

        $this->assertEquals(array("hello world"), $args);
    }

    public function testSingleStringWithUninterpretedNumberIsNotAnOctalEscape()
    {
        $args = Arguments\split('hello\\999world');

        $this->assertEquals(array("hello999world"), $args);
    }

    public function testSingleStringWithInterpretedUnicodeEscapes()
    {
        $args = Arguments\split('hello\u0020world');

        $this->assertEquals(array("hello world"), $args);
    }

    public function testSingleStringWithInterpretedUnicodeEscapesBackslash()
    {
        $args = Arguments\split('hello\u005Cx00');

        $this->assertEquals(array("hello\\x00"), $args);
    }

    public function testSingleStringWithInterpretedUnicodeEscapesEndLowerCase()
    {
        $args = Arguments\split('hell\u00f6');

        $this->assertEquals(array("hellö"), $args);
    }

    public function testSingleStringWithInterpretedUnicodeEscapesEndUpperCase()
    {
        $args = Arguments\split('hell\u00F6');

        $this->assertEquals(array("hellö"), $args);
    }

    public function testSingleStringWithUninterpretedCharacterIsNotAnUnicodeEscape()
    {
        $args = Arguments\split('hell\\uworld');

        $this->assertEquals(array("helluworld"), $args);
    }

    public function testSingleStringWithUninterpretedCharacterIsNotAnUnicodeEscapeEnd()
    {
        $args = Arguments\split('hell\\u');

        $this->assertEquals(array("hellu"), $args);
    }

    // "\n"\n"\n"
    public function testSingleStringWithCombinedDoubleQuotedPartsWithInterpretedEscapes()
    {
        $args = Arguments\split('"\n"\n"\n"');

        $this->assertEquals(array("\n\n\n"), $args);
    }

    // '\n'\n'\n'
    public function testSingleStringWithCombinedSingleQuotedPartsWithInterpretedEscapesOnlyInInnerUnquotedPart()
    {
        $s = "'";
        $args = Arguments\split($s . '\n' . $s . '\n' . $s . '\n' . $s);

        $this->assertEquals(array("\\n\n\\n"), $args);
    }

    // \n'\n'\n
    public function testSingleStringWithCombinedSingleQuotedPartsWithInterpretedEscapesOnlyInOuterUnquotedParts()
    {
        $s = "'";
        $args = Arguments\split('\n' . $s . '\n' . $s . '\n');

        $this->assertEquals(array("\n\\n\n"), $args);
    }
}
