#filter-php
[![Build Status](https://travis-ci.org/dominionenterprises/filter-php.png)](https://travis-ci.org/dominionenterprises/filter-php)

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

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/dominionenterprises/filter-php/pulls)
 * [Issues](https://github.com/dominionenterprises/filter-php/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```bash
./build.php
```
