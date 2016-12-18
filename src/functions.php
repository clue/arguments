<?php

namespace Clue\Arguments;

/**
 * Splits the given command line string into an array of command arguments
 *
 * @param string $command command line string
 * @return string[] array of command line argument strings
 * @throws \RuntimeException
 */
function split($command)
{
    // whitespace characters count as argument separators
    static $ws = array(
        ' ',
        "\r",
        "\n",
        "\t",
        "\v",
    );

    $i = 0;
    $args = array();

    while (true) {
        // skip all whitespace characters
        for(;isset($command[$i]) && in_array($command[$i], $ws); ++$i);

        // command string ended
        if (!isset($command[$i])) {
            break;
        }

        $inQuote = null;
        $quotePosition = 0;
        $argument = '';
        $part = '';

        // read a single argument
        for (; isset($command[$i]); ++$i) {
            $c = $command[$i];

            if ($inQuote === "'") {
                // we're within a 'single quoted' string
                if ($c === '\\' && isset($command[$i + 1]) && ($command[$i + 1] === "'" || $command[$i + 1] === '\\')) {
                    // escaped single quote or backslash ends up as char in argument
                    $part .= $command[++$i];
                    continue;
                } elseif ($c === "'") {
                    // single quote ends
                    $inQuote = null;
                    $argument .= $part;
                    $part = '';
                    continue;
                }
            } else {
                // we're not within any quotes or within a "double quoted" string
                if ($c === '\\' && isset($command[$i + 1])) {
                    if ($command[$i + 1] === 'u') {
                        // this looks like a unicode escape sequence
                        // use JSON parser to interpret this
                        $c = json_decode('"' . substr($command, $i, 6) . '"');
                        if ($c !== null) {
                            // on success => use interpreted and skip sequence
                            $argument .= stripcslashes($part) . $c;
                            $part = '';
                            $i += 5;
                            continue;
                        }
                    }

                    // escaped characters will be interpreted when part is complete
                    $part .= $command[$i] . $command[$i + 1];
                    ++$i;
                    continue;
                } elseif ($inQuote === '"' && $c === '"') {
                    // double quote ends
                    $inQuote = null;

                    // previous double quoted part should be interpreted
                    $argument .= stripcslashes($part);
                    $part = '';
                    continue;
                } elseif ($inQuote === null && ($c === '"' || $c === "'")) {
                    // start of quotes found
                    $inQuote = $c;
                    $quotePosition = $i;

                    // previous unquoted part should be interpreted
                    $argument .= stripcslashes($part);
                    $part = '';
                    continue;
                } elseif ($inQuote === null && in_array($c, $ws)) {
                    // whitespace character terminates unquoted argument
                    break;
                }
            }

            $part .= $c;
        }

        // end of argument reached. Still in quotes is a parse error.
        if ($inQuote !== null) {
            throw new UnclosedQuotesException($inQuote, $quotePosition);
        }

        // add remaining part to current argument
        if ($part !== '') {
            $argument .= stripcslashes($part);
        }

        $args []= $argument;
    }

    return $args;
}
