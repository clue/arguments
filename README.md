# clue/arguments [![Build Status](https://travis-ci.org/clue/php-arguments.svg?branch=master)](https://travis-ci.org/clue/php-arguments)

The simple way to split your command line string into an array of command arguments in PHP.

You want to build an interactive command line interface (CLI) tool in PHP which
reads line based user input from STDIN and you now want to break this command
line down to its individual command arguments? Then this library is for you!

This is similar to what your bash (or your terminal of choice) does for you when
you execute `./example.php "hello world"` which then spawns your script and
passes it a single argument.
If you only need this during startup, then using your `$argv` may be sufficient.
But many other tools may need similar parsing during their runtime.

**Table of contents**

* [Quickstart example](#quickstart-example)
* [Usage](#usage)
  * [split()](#split)
* [Install](#install)
* [License](#license)

### Quickstart example

The following example code demonstrates how this library can be used to build
a very simple interactive command line interface (CLI) tool that accepts a
command line from user input (via `STDIN`) and then executes some very simple
demo commands:

```php
while (true) {
    $line = fgets(STDIN, 1024);

    $args = Clue\Arguments\split($line);
    $command = array_shift($args);

    if ($command === 'exit') {
        break;
    } elseif ($command === 'sleep') {
        sleep($args[0]);
    } elseif ($command === 'echo') {
        echo join(' ', $args) . PHP_EOL;
    } else {
        echo 'Invalid command' . PHP_EOL;
    }
}
```

See also the [examples](examples).

## Usage

This lightweight library consists only of a single simple function.
Everything resides under the `Clue\Arguments` namespace.

The below examples assume you use an import statement similar to this:

```php
use Clue\Arguments;

Arguments\split(…);
```

Alternatively, you can also refer to them with their fully-qualified name:

```php
\Clue\Arguments\split(…);
``` 

#### split()

The `split(string $line) : array` function can be used to split the given
command line string into any array of individual command argument strings.

For the following example, let's assume we want to handle a simple `addUser`
command like this:

```php
// example command syntax:
// addUser <username> <realname> <homepage> <comment>

$line = 'adduser example Demo example.com Hi!';

$args = Arguments\split($line);

assert(count($args) === 5);
assert($args[0] === 'adduser');
assert($args[1] === 'example');
assert($args[2] === 'Demo');
assert($args[3] === 'example.com');
assert($args[4] === 'Hi!');
```

While this simple example may look like a job for a simple
`$args = explode(' ', $line)` call, the `split()` function is actually
way more sophisticated. It also supports parsing the following:

* Single quoted strings (`'hello world'`) which preserve any whitespace characters and
  only accept escape sequences `\\` and `\'`, e.g. `'let\'s go'`.
* Double quoted strings (`"hello world"`) which preserve any whitespace characters and
  support common escape sequences such as `\t\r\n` etc., e.g. `"hi there\nworld!"`.
* Unquoted strings are terminated at the next (unescaped) whitespace character and
  support common escape sequences such as `\t\r\n` etc., e.g. `hi\ there\nworld!`.
* Ignores excessive whitespace around arguments, such as trailing whitespace or
  multiple spaces between arguments.
* Makes no assumptions about your input encoding, so this works with binary data
  as well as full UTF-8 (Unicode) support.

For example, this means that the following also parses as expected:

```php
$line = 'adduser clue \'Christian Lück\' https://lueck.tv/ "Hällo\tWörld\n"';

$args = Arguments\split($line);

assert(count($args) === 5);
assert($args[0] === 'adduser');
assert($args[1] === 'clue');
assert($args[2] === 'Christian Lück');
assert($args[3] === 'https://lueck.tv');
assert($args[4] === "Hällo\tWörld\n");
```

Validating any of the arguments (checking lengths or value ranges etc.) is left
up to higher levels, i.e. the consumer of this library. This also allows you to
explicitly pass empty arguments like this:

```php
$line = 'sendmail "" clue';

$args = Arguments\split($line);

assert(count($args) === 3);
assert($args[0] === 'sendmail');
assert($args[1] === '');
assert($args[2] === 'clue');
```

Parsing an empty input line or one with only whitespace will return an empty
array:

```php
$line = "\r\n";

$args = Arguments\split($line);

assert(count($args) === 0);
```

Parsing an input line that has a missing quote (i.e. a quoted argument started
without passing an ending quote), this will throw a `RuntimeException`. This
can be useful to ask the user to correct their input:

```php
$line = 'sendmail "hello world';

try {
    Arguments\split($line);
    // throws RuntimeException
} catch (RuntimeException $e) {
    echo 'Please check your input.';
}
```

## Install

The recommended way to install this library is [through Composer](http://getcomposer.org).
[New to Composer?](http://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require clue/arguments:^1.0
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

## License

MIT
