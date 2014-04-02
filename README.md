#filter-php
[![Build Status](https://travis-ci.org/dominionenterprises/filter-php.png)](https://travis-ci.org/dominionenterprises/filter-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dominionenterprises/filter-php/badges/quality-score.png?s=aafae56e65e9f626709043710df934d248ff09ce)](https://scrutinizer-ci.com/g/dominionenterprises/filter-php/)
[![Code Coverage](https://scrutinizer-ci.com/g/dominionenterprises/filter-php/badges/coverage.png?s=47a4c24cc8ecf67a8bdb1923fa3e6dc8087727b2)](https://scrutinizer-ci.com/g/dominionenterprises/filter-php/)

[![Latest Stable Version](https://poser.pugx.org/dominionenterprises/filter/v/stable.png)](https://packagist.org/packages/dominionenterprises/filter)
[![Total Downloads](https://poser.pugx.org/dominionenterprises/filter/downloads.png)](https://packagist.org/packages/dominionenterprises/filter)
[![Latest Unstable Version](https://poser.pugx.org/dominionenterprises/filter/v/unstable.png)](https://packagist.org/packages/dominionenterprises/filter)
[![License](https://poser.pugx.org/dominionenterprises/filter/license.png)](https://packagist.org/packages/dominionenterprises/filter)

A filtering implementation for verifying correct data and performing typical modifications to data.

##Features
 * Compact, readable specification
 * Filter with any php callable such as
  * Anonymous function
  * Class function
  * Built-in function
 * Optional/Required support, field and global level
 * Chaining filters
 * Optionally returns unknown fields
 * Filter alias support

##Example
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

list($status, $result, $error, $unknowns) = DominionEnterprises\Filterer::filter(
    [
        'field one' => [[$trimFunc], ['substr', 0, 3], [[$appendFilter, 'filter'], 'boo']],
        'field two' => ['required' => true, ['floatval']],
        'field three' => ['required' => false, ['float']],
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
array(2) {
  'field one' =>
  string(6) "abcboo"
  'field two' =>
  double(3.14)
}
NULL
array(0) {
}
```

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`dominionenterprises/filter` to your project's `composer.json` file such as:

```json
{
    "require": {
        "dominionenterprises/filter": "~1.0"
    }
}
```

##Documentation
Found in the [source](src/DominionEnterprises/Filterer.php) itself, take a look!

### Included Filters
Of course, any function can potentially be used as a filter, but we include some useful filters with aliases for common circumstances.

#### Arrays::in
Aliased in the filterer as `in`, this filter is a wrapper around `in_array` including support for strict equality testing.

The following does a strict check for `$value` against the 3 accepted values.
```php
\DominionEnterprises\Filter\Arrays::in($value, ['a', 'b', 'c']);
```

#### Arrays::filter
Aliased in the filterer as `array`, this filter verifies that the argument is an array and checks the length of the array against bounds.  The
default bounds are 1+, so an empty array fails by default.

The following checks that the `$value` is an array with exactly 3 elements.
```php
\DominionEnterprises\Filter\Arrays::filter($value, 3, 3);
```

#### Arrays::ofScalars
Aliased in the filterer as `ofScalars`, this filter verifies that the argument is an array (possibly empty) of scalar items that each pass the
given filters (given in the same format as used by `Filterer::filter`.

The following checks that `$value` is an array of unsigned integers.
```php
$value = \DominionEnterprises\Filter\Arrays::ofScalars($value, [['uint']]);
```

#### Arrays::ofArrays
Aliased in the filterer as `ofArrays`, this filter verifies that the argument is an array (possibly empty) of arrays that each pass the given
filters (given in the same format as used by `Filterer::filter`.

The following checks that `$value` is an array of items that each have an `id` key with a numeric value.  No other keys would be allowed.  For
example, the following is valid input: `[['id' => '1'], ['id' => '2']]`.
```php
$value = \DominionEnterprises\Filter\Arrays::ofArrays($value, ['id' => [['uint']]]);
```

#### Arrays::ofArray
Aliased in the filterer as `ofArray`, this filter verifies that the argument is an array that passes the given specification.  This is
essentially a flipped version of `Filterer::filter` that allows for testing nested associative arrays.

#### Bool::filter
Aliased in the filterer as `bool`, this filter verifies that the argument is a boolean value or a string that maps to one.  The second parameter
can be set to `true` to allow null values through without an error (they will stay null and not get converted to false).  The last parameters
are lists of strings for true values and false values.  By default, the strings "true" and "false" map to their boolean counterparts.

The following example converts `$value` to a boolean allowing the strings "on" and "of".
```php
$enabled = \DominionEnterprises\Filter\Bool::filter($value, false, ['on'], ['off']);
```

#### Float/Int/UnsignedInt::filter
Aliased in the filterer as `float`, `int`, and `uint`, respectively, these filters verify that the arguments are of the proper numeric type and
allow for bounds checking.  The second parameter to each of them can be set to `true` to allow null values through without an error (they will
stay null and not get converted to false).  The next two parameters are the min and max bounds and can be used to limit the domain of allowed
numbers.

Non-numeric strings will fail validation, and numeric strings will be cast.

The float parameter has an additional parameter that can be set to `true` to cast integers to floats.  Without this, integers will fail
validation.

The following checks that `$value` is an integer between 1 and 100 inclusive, and returns the integer (after casting it if it was a string).
```php
$value = \DominionEnterprises\Filter\UnsignedInt($value, false, 1, 100);
```

#### String::filter
Aliased in the filterer as `string`, this filter verifies that the argument is a string.  The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).  The last parameters specify the length bounds of the
string. The default bounds are 1+, so an empty string fails by default.

The following checks that `$value` is a non-empty string.
```php
\DominionEnterprises\Filter\String($value);
```

#### Url::filter
Aliased in the filterer as `url`, this filter verifies that the argument is a URL string according to
[RFC2396](http://www.faqs.org/rfcs/rfc2396).

The following checks that `$value` is a URL.
```php
\DominionEnterprises\Filter\Url::filter($value);
```

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/dominionenterprises/filter-php/pulls)
 * [Issues](https://github.com/dominionenterprises/filter-php/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```bash
./build.php
```
