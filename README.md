# filter-php
A filtering implementation for verifying correct data and performing typical modifications to data.

[![Build Status](http://img.shields.io/travis/traderinteractive/filter-php.svg?style=flat)](https://travis-ci.org/traderinteractive/filter-php)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/traderinteractive/filter-php.svg?style=flat)](https://scrutinizer-ci.com/g/traderinteractive/filter-php/)
[![Code Coverage](http://img.shields.io/coveralls/traderinteractive/filter-php.svg?style=flat)](https://coveralls.io/r/traderinteractive/filter-php)

[![Latest Stable Version](http://img.shields.io/packagist/v/traderinteractive/filter.svg?style=flat)](https://packagist.org/packages/traderinteractive/filter)
[![Total Downloads](http://img.shields.io/packagist/dt/traderinteractive/filter.svg?style=flat)](https://packagist.org/packages/traderinteractive/filter)
[![License](http://img.shields.io/packagist/l/traderinteractive/filter.svg?style=flat)](https://packagist.org/packages/traderinteractive/filter)

## Features
 * Compact, readable specification
 * Filter with any php callable such as
  * Anonymous function
  * Class function
  * Built-in function
 * Optional/Required support, field and global level
 * Default support
 * Chaining filters
 * Optionally returns unknown fields
 * Filter alias support

## Components

This package is a partial metapackage aggregating the following components:

* [traderinteractive/filter-arrays](https://github.com/traderinteractive/filter-arrays-php)
* [traderinteractive/filter-bools](https://github.com/traderinteractive/filter-bools-php)
* [traderinteractive/filter-dates](https://github.com/traderinteractive/filter-dates-php)
* [traderinteractive/filter-floats](https://github.com/traderinteractive/filter-floats-php)
* [traderinteractive/filter-ints](https://github.com/traderinteractive/filter-ints-php)
* [traderinteractive/filter-strings](https://github.com/traderinteractive/filter-strings-php)

## Example
```php
class AppendFilter
{
    public function filter($value, $extraArg)
    {
        return $value . $extraArg;
    }
}
$appendFilter = new AppendFilter();

$trimFunc = function($val) { return trim($val); };

list($status, $result, $error, $unknowns) = TraderInteractive\Filterer::filter(
    [
        'field one' => [[$trimFunc], ['substr', 0, 3], [[$appendFilter, 'filter'], 'boo']],
        'field two' => ['required' => true, ['floatval']],
        'field three' => ['required' => false, ['float']],
        'field four' => ['required' => true, 'default' => 1, ['uint']],
    ],
    ['field one' => ' abcd', 'field two' => '3.14']
);

var_dump($status);
var_dump($result);
var_dump($error);
var_dump($unknowns);
```
prints

```php
bool(true)
array(3) {
  'field one' =>
  string(6) "abcboo"
  'field two' =>
  double(3.14)
  'field four' =>
  int(1)
}
NULL
array(0) {
}
```

## Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`traderinteractive/filter` to your project's `composer.json` file such as:

```sh
composer require traderinteractive/filter
```

## Documentation
Found in the [source](src/Filterer.php) itself, take a look!

### Filterer
At the core of this library is a `Filterer` class that can validate the structure of an array and map the data through filters.  This behavior
is defined by a specification of the different filters to apply and some additional options.

#### Specification
The specification is an array of key => filter specification pairs.

The keys define the known fields in the array.  Any fields in the array that are not in the specification are treated as "unknown" fields and
may cause validation to fail, depending on the value of the `allowUnknowns` option.

The filter specification for a single field is also an array.  It can contain two special keys:
* `required` defines whether this field is a required element of the array.  This value overrides the global filter specification's
  `defaultRequired` option.
* `default` defines what the default value of this field is if none is given.  A field with a default value will be guaranteed to be in the
  result.  The `required` value does not affect `default` behavior.

The rest of the specification for the field are the filters to apply.

The first element in the filter is the filter to run.  This can either be something that passes `is_callable` (e.g., `'trim'` or
`[$object, 'method']`) or it can be one of our predefined aliases (e.g., `'float'`).

The rest of the elements in the filter are the extra arguments to the filter (the value being filtered is always the first argument).

A filter specification can contain any number of filters and the result of each filter is piped in as the input to the next filter.  The result
of the final filter is set in the result array.

The example above should help clarify all this.

### Included Filters
Of course, any function can potentially be used as a filter, but we include some useful filters with aliases for common circumstances.

#### Filterer::ofScalars
Aliased in the filterer as `ofScalars`, this filter verifies that the argument is an array (possibly empty) of scalar items that each pass the
given filters (given in the same format as used by `Filterer::filter`.

The following checks that `$value` is an array of unsigned integers.
```php
$value = \TraderInteractive\Filter\Filterer::ofScalars($value, [['uint']]);
```

#### Filterer::ofArrays
Aliased in the filterer as `ofArrays`, this filter verifies that the argument is an array (possibly empty) of arrays that each pass the given
filters (given in the same format as used by `Filterer::filter`.

The following checks that `$value` is an array of items that each have an `id` key with a numeric value.  No other keys would be allowed.  For
example, the following is valid input: `[['id' => '1'], ['id' => '2']]`.
```php
$value = \TraderInteractive\Filter\Filterer::ofArrays($value, ['id' => [['uint']]]);
```

#### Filterer::ofArray
Aliased in the filterer as `ofArray`, this filter verifies that the argument is an array that passes the given specification.  This is
essentially a flipped version of `Filterer::filter` that allows for testing nested associative arrays.

#### Arrays::in
Aliased in the filterer as `in`, this filter is a wrapper around `in_array` including support for strict equality testing.

The following does a strict check for `$value` against the 3 accepted values.
```php
\TraderInteractive\Filter\Arrays::in($value, ['a', 'b', 'c']);
```

#### Arrays::filter
Aliased in the filterer as `array`, this filter verifies that the argument is an array and checks the length of the array against bounds.  The
default bounds are 1+, so an empty array fails by default.

The following checks that the `$value` is an array with exactly 3 elements.
```php
\TraderInteractive\Filter\Arrays::filter($value, 3, 3);
```

#### Arrays::flatten
Aliased in the filterer as `flatten`, this filter flattens a multi-dimensional array to a single dimension.  The order of values will be
maintained, but the keys themselves will not.  For example:
```php
$value = \TraderInteractive\Filter\Arrays::flatten([[1, 2], [3, [4, 5]]]);
assert($value === [1, 2, 3, 4, 5]);
```

#### Booleans::filter
Aliased in the filterer as `bool`, this filter verifies that the argument is a boolean value or a string that maps to one.  The second parameter
can be set to `true` to allow null values through without an error (they will stay null and not get converted to false).  The last parameters
are lists of strings for true values and false values.  By default, the strings "true" and "false" map to their boolean counterparts.

The following example converts `$value` to a boolean allowing the strings "on" and "of".
```php
$enabled = \TraderInteractive\Filter\Booleans::filter($value, false, ['on'], ['off']);
```
#### Booleans::convert
Aliased in the filterer as `bool-convert`, this filter will convert a given boolean value into the provided true or false conditions. By default the
return values are the strings 'true' and 'false'

The following converts the boolean `$value` to either 'yes' or 'no'
```php
$answer = \TraderInteractive\Filter\Booleans::convert($value, 'yes', 'no');
```

#### Floats/Ints/UnsignedInt::filter
Aliased in the filterer as `float`, `int`, and `uint`, respectively, these filters verify that the arguments are of the proper numeric type and
allow for bounds checking.  The second parameter to each of them can be set to `true` to allow null values through without an error (they will
stay null and not get converted to false).  The next two parameters are the min and max bounds and can be used to limit the domain of allowed
numbers.

Non-numeric strings will fail validation, and numeric strings will be cast.

The float parameter has an additional parameter that can be set to `true` to cast integers to floats.  Without this, integers will fail
validation.

The following checks that `$value` is an integer between 1 and 100 inclusive, and returns the integer (after casting it if it was a string).
```php
$value = \TraderInteractive\Filter\UnsignedInt::filter($value, false, 1, 100);
```

#### Strings::filter
Aliased in the filterer as `string`, this filter verifies that the argument is a string.  The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).  The last parameters specify the length bounds of the
string. The default bounds are 1+, so an empty string fails by default.

The following checks that `$value` is a non-empty string.
```php
\TraderInteractive\Filter\Strings::filter($value);
```

#### Strings::explode
Aliased in the filterer as `explode`, this filter is essentially a wrapper around the built-in [`explode`](http://www.php.net/explode) method
with the value first in order to work with the `Filterer`.  It also defaults to using `,` as a delimiter.  For example:
```php
$value = \TraderInteractive\Filter\Strings::explode('abc,def,ghi');
assert($value === ['abc', 'def', 'ghi']);
```

#### Url::filter
Aliased in the filterer as `url`, this filter verifies that the argument is a URL string according to
[RFC2396](http://www.faqs.org/rfcs/rfc2396). The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).

The following checks that `$value` is a URL.
```php
\TraderInteractive\Filter\Url::filter($value);
```

#### Email::filter
Aliased in the filterer as `email`, this filter verifies that the argument is an email.

The following checks that `$value` is an email.
```php
\TraderInteractive\Filter\Email::filter($value);
```

#### DateTime::filter
Aliased in the filterer as `date`, this will filter the value as a `\DateTime` object. The value can be any string that conforms to [PHP's valid date/time formats](http://php.net/manual/en/datetime.formats.php)

The following checks that `$value` is a date/time.
```php
$dateTime = \TraderInteractive\Filter\DateTime::filter('2014-02-04T11:55:00-0500');
```

#### DateTime::format
Aliased in the filterer as `date-format`, this will filter a given `\DateTime' value to a string based on the given format.

The following returns formatted string for a given `\DateTime` `$value`
```php
$formatted = \TraderInteractive\Filter\DateTime::format($value, 'Y-m-d H:i:s');
```

#### DateTimeZone::filter
Aliased in the filterer as `date`, this will filter the value as a `\DateTimeZone` object. The value can be any [supported timezone name](http://php.net/manual/en/timezones.php)

The following checks that `$value` is a timezone
```php
$timezone = \TraderInteractive\Filter\DateTimeZone::filter('America/New_York');
```

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/traderinteractive/filter-php/pulls)
 * [Issues](https://github.com/traderinteractive/filter-php/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```bash
./build.php
```

There is also a [docker](http://www.docker.com/)-based
[fig](http://www.fig.sh/) configuration that will execute the build inside a
docker container.  This is an easy way to build the application:
```sh
fig run build
```

For more information on our build process, read through out our [Contribution Guidelines](CONTRIBUTING.md).
