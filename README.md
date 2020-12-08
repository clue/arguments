# clue/arguments

[![CI status](https://github.com/clue/arguments/workflows/CI/badge.svg)](https://github.com/clue/arguments/actions)

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

* [Support us](#support-us)
* [Quickstart example](#quickstart-example)
* [Usage](#usage)
  * [split()](#split)
  * [UnclosedQuotesException](#unclosedquotesexception)
* [Install](#install)
* [Tests](#tests)
* [License](#license)
* [More](#more)

## Support us

We invest a lot of time developing, maintaining and updating our awesome
open-source projects. You can help us sustain this high-quality of our work by
[becoming a sponsor on GitHub](https://github.com/sponsors/clue). Sponsors get
numerous benefits in return, see our [sponsoring page](https://github.com/sponsors/clue)
for details.

Let's take these projects to the next level together! 🚀

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
  support common escape sequences such as `\t\r\n` etc., unicode escape sequences
  such as `\u0020`, hex escape sequences such as `\x20` and
  octal escape sequences such as `\040`, e.g. `"hi there\nworld!"`.
* Unquoted strings are terminated at the next (unescaped) whitespace character and
  support common escape sequences just like double quoted strings, e.g. `hi\ there\nworld!`.
* Ignores excessive whitespace around arguments, such as trailing whitespace or
  multiple spaces between arguments.
* Makes very few assumptions about your input encoding otherwise, so that input
  is allowed to contain raw binary data as well as full UTF-8 (Unicode) support.
  Unicode characters may either be given as normal UTF-8 strings such as
  `hällö` or can be given as unicode escape sequences in double quoted and
  unquoted strings, e.g. `h\u00e4ll\u00f6`. 

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

Parsing an input line that has unbalanced quotes (i.e. a quoted argument started
without passing ending quotes), this will throw an
[`UnclosedQuotesException`](#unclosedquotesexception).
This can be useful to ask the user to correct their input:

```php
$line = 'sendmail "hello world';

try {
    Arguments\split($line);
    // throws RuntimeException
} catch (Arguments\UnclosedQuotesException $e) {
    echo 'Please check your input.';
}
```

See also the following chapter if you want to (try to) correct the user input
line automatically.

### UnclosedQuotesException

The `UnclosedQuotesException` will be raised by the [`split()`](#split)
function when the input line has unbalanced quotes (i.e. a quoted argument
started without passing ending quotes).

This class extends PHP's [`InvalidArgumentException`](https://www.php.net/manual/en/class.invalidargumentexception.php).

The `getQuotes(): string` method can be used to get the quotes this argument
started with:

```php
$quotes = $e->getQuotes();
```

For example, this can be used to (try to) correct the user input line like this:

```php
$line = 'sendmail "hello world';

try {
    $args = Arguments\split($line);
    // throws RuntimeException
} catch (Arguments\UnclosedQuotesException $e) {
    // retry parsing with closing quotes appended
    $args = Arguments\split($line . $e->getQuotes());
}
```

> Note: The input line may end with a backslash in which case the appended
closing quotes will actually be marked as escaped.
Either handle these yourself or wrap this block in another `try-catch`.

The `getPosition(): int` method can be used to get the character position of
the quotes within the input string.
In the previous example, this will be at `$line[9]`:

```php
$pos = $e->getPosition();

assert($pos === 9);
assert($line[$pos] === $e->getQuotes());
```

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org/).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This project follows [SemVer](https://semver.org/).
This will install the latest supported version:

```bash
$ composer require clue/arguments:^2.1
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 8+ and
HHVM.
It's *highly recommended to use PHP 7+* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

This project is released under the permissive [MIT license](LICENSE).

> Did you know that I offer custom development services and issuing invoices for
  sponsorships of releases and for contributions? Contact me (@clue) for details.

## More

* If you want to register/route available commands and their arguments, you may
  want to look into using [clue/commander](https://github.com/clue/commander).

* If you want to build an interactive CLI tool, you may want to look into using
  [clue/reactphp-stdio](https://github.com/clue/reactphp-stdio) in order to react
  to commands from STDIN.
