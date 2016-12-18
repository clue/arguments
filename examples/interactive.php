<?php

require __DIR__ . '/../vendor/autoload.php';

echo 'Hello! Try the sleep, echo and exit commands.' . PHP_EOL;

while (true) {
    echo '> ';
    $line = fgets(STDIN, 1024);

    // stop loop if STDIN is no longer readable
    if ($line === false) {
        break;
    }

    // try to parse command line or complain
    try {
        $args = Clue\Arguments\split($line);
    } catch (Clue\Arguments\UnclosedQuotesException $e) {
        echo 'Invalid command line. Missing quotes?' . PHP_EOL;
        continue;
    }

    // skip empty lines
    if (!$args) {
        continue;
    }

    // simple example command processing
    $command = array_shift($args);
    if ($command === 'exit') {
        echo 'Bye!' . PHP_EOL;
        break;
    } elseif ($command === 'sleep') {
        if (count($args) !== 1 || !is_numeric($args[0])) {
            echo 'Expects single integer argument.' . PHP_EOL;
            continue;
        }
        echo 'Sleeping…';
        sleep($args[0]);
        echo ' Done.' . PHP_EOL;
    } elseif ($command === 'echo') {
        echo implode(' ', $args) . PHP_EOL;
    } else {
        echo 'Unknown command.' . PHP_EOL;
    }
}
