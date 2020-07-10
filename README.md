# filter-php
A filtering implementation for verifying correct data and performing typical modifications to data.

[![Build Status](https://travis-ci.org/traderinteractive/filter-php.svg?branch=master)](https://travis-ci.org/traderinteractive/filter-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/traderinteractive/filter-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/traderinteractive/filter-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/traderinteractive/filter-php/badge.svg?branch=master)](https://coveralls.io/github/traderinteractive/filter-php?branch=master)

[![Latest Stable Version](https://poser.pugx.org/traderinteractive/filter/v/stable)](https://packagist.org/packages/traderinteractive/filter)
[![Latest Unstable Version](https://poser.pugx.org/traderinteractive/filter/v/unstable)](https://packagist.org/packages/traderinteractive/filter)
[![License](https://poser.pugx.org/traderinteractive/filter/license)](https://packagist.org/packages/traderinteractive/filter)

[![Total Downloads](https://poser.pugx.org/traderinteractive/filter/downloads)](https://packagist.org/packages/traderinteractive/filter)
[![Daily Downloads](https://poser.pugx.org/traderinteractive/filter/d/daily)](https://packagist.org/packages/traderinteractive/filter)
[![Monthly Downloads](https://poser.pugx.org/traderinteractive/filter/d/monthly)](https://packagist.org/packages/traderinteractive/filter)

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

The filter specification for a single field is also an array.  It can contain predefined [filter options](#filter-options).

The rest of the specification for the field are the filters to apply.

The first element in the filter is the filter to run.  This can either be something that passes `is_callable` (e.g., `'trim'` or
`[$object, 'method']`) or it can be one of our predefined aliases (e.g., `'float'`).

The rest of the elements in the filter are the extra arguments to the filter (the value being filtered is always the first argument).

A filter specification can contain any number of filters and the result of each filter is piped in as the input to the next filter.  The result
of the final filter is set in the result array.

The example above should help clarify all this.

# Filterer Options

## allowUnknowns

#### Summary

Flag to allow elements in unfiltered input that are not present in the filterer specification.

#### Types

  * bool

#### Default

The default value is `false`

#### Constant

```php
TraderInteractive\FiltererOptions::ALLOW_UNKNOWNS
```

#### Example

```php
$options = [
    TraderInteractive\FiltererOptions::ALLOW_UNKNOWNS => true,
];

$filterer = new TraderInteractive\Filterer($specification, $options);
```

## defaultRequired

#### Summary

Flag for the default required behavior of all elements in the filterer specification. If `true` all elements in the specification are required unless they have the required filter option set.

#### Types

  * bool

#### Default

The default value is `false`

#### Constant

```php
TraderInteractive\FiltererOptions::DEFAULT_REQUIRED
```

#### Example

```php
$options = [
    TraderInteractive\FiltererOptions::DEFAULT_REQUIRED => true,
];

$filterer = new TraderInteractive\Filterer($specification, $options);
```

## responseType

#### Summary

Specifies the type of response which The Filterer::filter method will return. It can be `array` or `\TraderInteractive\FilterResponse`

#### Types

  * string

#### Default

The default value is `array`

#### Constant

```php
TraderInteractive\FiltererOptions::RESPONSE_TYPE
```

#### Example

```php
$options = [
    TraderInteractive\FiltererOptions::RESPONSE_TYPE => \TraderInteractive\FilterResponse::class,
];

$filterer = new TraderInteractive\Filterer($specification, $options);
```
# Filter Options

## required

#### Summary

Defines whether this field is a required element of the array.  This value overrides the global filter specification's `defaultRequired` option.

#### Types

  * bool
  
#### Default

The default value depends on the `defaultRequired` Filterer Option.

#### Constant

```php
TraderInteractive\FilterOptions::IS_REQUIRED
```

#### Example

```php
$specificaton = [
    'id' => [TraderInteractive\FilterOptions::IS_REQUIRED => true, ['uint']],
];
```

## default

#### Summary
  
Defines what the default value of this field is if none is given.  A field with a default value will be guaranteed to be in the result.  The `required` value does not affect `default` behavior.

#### Types
  * string
  
#### Default

There is no default value for this option.

#### Constant

```php
TraderInteractive\FilterOptions::DEFAULT_VALUE
```

#### Example
```php
$specificaton = [
    'subscribe' => [TraderInteractive\FilterOptions::DEFAULT_VALUE => true, ['bool']],
    'status' => [TraderInteractive\FilterOptions::DEFAULT_VALUE => 'A', ['string', false, 1, 1]],
];
```

## error

#### Summary

Defines a custom error message to be returned if the value fails filtering. Within the error string, `{value}` can be used as a placeholder for the value that failed filtering.

#### Types

  * string
  
#### Default

There is no default value for this option.

#### Constant

```php
TraderInteractive\FilterOptions::CUSTOM_ERROR
```

#### Example

```php
$specificaton = [
    'price' => [
        TraderInteractive\FilterOptions::CUSTOM_ERROR => 'Price {value} was not between 0 and 100', 
        ['uint', false, 0, 100],
    ],
];
```

## conflictsWith

#### Summary

Defines any input fields with which a given field will conflict. Used when one field can be given in input or another but not both.

#### Types

   * string
   
#### Default   

There is no default value for this option.

#### Constant

```php
TraderInteractive\FilterOptions::CONFLICTS_WITH
```

#### Example

```php
$specification = [
    'id' => [
        TraderInteractive\FilterOptions::CONFLICTS_WITH => 'code',
        [['uint']],
    ],
    'code' => [
        TraderInteractive\FilterOptions::CONFLICTS_WITH => 'id',
        [['string']],
    ],
];
```

## uses

#### Summary

Specifies an array of input values that should be used as part of a field's filter specification.

#### Types

   * string[]
   
#### Default   

The default value for this option is an empty array.

#### Constant

```php
TraderInteractive\FilterOptions::USES
```

#### Example

```php
$specification = [
    'base' => [
        [['float']],
    ],
    'exponent' => [
        ['uint'], 
        [
            TraderInteractive\FilterOptions::USES => 'base',
            'pow',
        ],
    ],
];
```

The exponent filter spec will call the PHP function `pow()` with the value provided and the result of the filtered `base`

## throwOnError

#### Summary

If true the Filterer will throw any exception caught when filtering a value instead of returning the error in the filter response.

#### Types

   * boolean
   
#### Default   

The default value for this option is `false`

#### Constant

```php
TraderInteractive\FilterOptions::THROW_ON_ERROR
```

#### Example

```php
$idFilter = function ($id) : int {
    if (!is_int($id)) {
       throw new NotFoundException("id '{$id}' was not found"); 
    }
    
    return $id;
};
$specification = [
    'id' => [
        \TraderInteractive\FilterOptions::THROW_ON_ERROR => true,
        [$idFilter],
    ],
];
```

If the `id` value given in the input is not an integer the Filterer::execute() will throw the `NotFoundException`

## returnOnNull

#### Summary

Flag to break the filter chain if a resulting value is `null` Useful for nullable fields which require additional filtering if the value is not null.

#### Types

   * boolean
   
#### Default   

The default value for this option is `false`

#### Constant

```php
TraderInteractive\FilterOptions::RETURN_ON_NULL
```

#### Example

```php
$validCodes = ['A', 'I', 'X'];
$specification = [
    'code' => [
        \TraderInteractive\FilterOptions::RETURN_ON_NULL => true,
        ['string', true],
        ['strtoupper'],
        ['in', $validCodes],
    ],
];
```

If the `code` value is `null` then the resulting filtered value will be null. Otherwise the value must be one of the `$validCode` values.

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

#### Arrays::arrayize
Aliased in the filterer as `arrayize`, this filter returns this original input if it is an array, otherwise returns input wrapped in an array. If the original input is null, an empty array is returned.
```php
$value = \TraderInteractive\Filter\Arrays::arrayize('a string value');
assert($value === ['a string value']);
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

#### Strings::concat
Aliased in the filterer as `concat`, this filter concatenates the given $value, $prefix and $suffix and returns the resulting string.
```php
$value = \TraderInteractive\Filter\Strings::concat('middle', 'begining_', '_end');
assert($value === 'begining_middle_end');
```

#### Strings::explode
Aliased in the filterer as `explode`, this filter is essentially a wrapper around the built-in [`explode`](http://www.php.net/explode) method
with the value first in order to work with the `Filterer`.  It also defaults to using `,` as a delimiter.  For example:
```php
$value = \TraderInteractive\Filter\Strings::explode('abc,def,ghi');
assert($value === ['abc', 'def', 'ghi']);
```

#### Strings::stripTags
Aliased in the filterer as `strip-tags`, this filter is essentially a wrapper around the built-in [`strip_tags`](http://php.net/manual/en/function.strip-tags.php) function. However, unlike the
native function the stripTags method will return null when given a null value.
```php
$value = \TraderInteractive\Filter\Strings::stripTags('A string with <p>tags</p>');
assert($value === 'a string with tags');
```

#### Strings::translate
Aliased in the filterer as `translate`, this filter will accept a string value and return its translated value found in the given $valueMap.
```php
$value = \TraderInteractive\Filter\Strings::tranlsate('bar', ['foo' => 'translated to bar', 'bar' => 'translated to foo']);
assert($value === 'translated to foo');
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

#### Json::validate
Aliased in the filter as `json`, checks that the JSON is valid and returns the original value.

The following ensures that `$value` is valid JSON
```php
$value = \TraderInteractive\Filter\Json::validate('{"foo": "bar"}');
```

#### Json::parse
Aliased in the filter as `json-decode`, checks that the JSON is valid and returns the decoded result.

The following decodes the given value and returns the result.
```php
$value = \TraderInteractive\Filter\Json::parse('{"foo": "bar"}');
assert($value === ['foo' => 'bar']);
```

#### PhoneFilter::filter
Aliased in the filter as `phone`, this will filter a given value as a phone. Returning the phone is the specified format.

The following filters the given string into a formatted phone string
```php
$value = \TraderInteractive\Filter\PhoneFilter::filter('234.567.8901', false, '({area}) {exchange}-{station}');
assert($value === '(234) 567-8901');
```

#### XmlFilter::filter
Aliased in the filter as `xml`, this will ensure the given string value is valid XML, returning the original value.

The following ensures the given string is valid xml.
```php
$value = <<<XML
<?xml version="1.0"?>
<books>
    <book id="bk101">
        <author>Gambardella, Matthew</author>
        <title>XML Developers Guide</title>
        <genre>Computer</genre>
        <price>44.95</price>
        <publish_date>2000-10-01</publish_date>
        <description>An in-depth look at creating applications with XML.</description>
    </book>
    <book id="bk102">
        <author>Ralls, Kim</author>
        <title>Midnight Rain</title>
        <genre>Fantasy</genre>
        <price>5.95</price>
        <publish_date>2000-12-16</publish_date>
        <description>A former architect battles corporate zombies</description>
    </book>
</books>
XML;
$xml = \TraderInteractive\Filter\XmlFilter::filter($value);
```

#### XmlFilter::extract
Aliased in the filter as `xml-extract`, this will ensure the given string value is valid XML then extract and return the element found at the given xpath.

The following ensures the given string is valid xml and returns the title element of the first book.
```php
$value = <<<XML
<?xml version="1.0"?>
<books>
    <book id="bk101">
        <author>Gambardella, Matthew</author>
        <title>XML Developers Guide</title>
        <genre>Computer</genre>
        <price>44.95</price>
        <publish_date>2000-10-01</publish_date>
        <description>An in-depth look at creating applications with XML.</description>
    </book>
    <book id="bk102">
        <author>Ralls, Kim</author>
        <title>Midnight Rain</title>
        <genre>Fantasy</genre>
        <price>5.95</price>
        <publish_date>2000-12-16</publish_date>
        <description>A former architect battles corporate zombies</description>
    </book>
</books>
XML;
$xpath = "/books/book[@id='bk101']/title";
$titleXml = \TraderInteractive\Filter\XmlFilter::extract($value, $xpath);
assert($titleXml === '<title>XML Developers Guide</title>');
```

#### XmlFilter::validate
Aliased in the filter as `xml-validate`, this will ensure the given string value is valid XML and also confirms to the given XSD file. The original value is returned.

The following ensures the given string is valid xml and matches books.xsd.
```php
$value = <<<XML
<?xml version="1.0"?>
<books>
    <book id="bk101">
        <author>Gambardella, Matthew</author>
        <title>XML Developers Guide</title>
        <genre>Computer</genre>
        <price>44.95</price>
        <publish_date>2000-10-01</publish_date>
        <description>An in-depth look at creating applications with XML.</description>
    </book>
    <book id="bk102">
        <author>Ralls, Kim</author>
        <title>Midnight Rain</title>
        <genre>Fantasy</genre>
        <price>5.95</price>
        <publish_date>2000-12-16</publish_date>
        <description>A former architect battles corporate zombies</description>
    </book>
</books>
XML;
$xml = \TraderInteractive\Filter\XmlFilter::validate($value, 'books.xsd');
```

## Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/traderinteractive/filter-php/pulls)
 * [Issues](https://github.com/traderinteractive/filter-php/issues)

## Project Build
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
