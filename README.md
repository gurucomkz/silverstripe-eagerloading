# SilverStripe 4 EagerLoading

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gurucomkz/silverstripe-eagerloading/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gurucomkz/silverstripe-eagerloading/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/gurucomkz/silverstripe-eagerloading/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gurucomkz/silverstripe-eagerloading/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/gurucomkz/silverstripe-eagerloading/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gurucomkz/silverstripe-eagerloading/?branch=master)

Attempt to solve [N+1 problem](https://stackoverflow.com/questions/97197/what-is-the-n1-selects-problem-in-orm-object-relational-mapping) in SilverStripe 4.

## Deprecated

SilverStripe 5 [has this feature built-in](https://docs.silverstripe.org/en/5/changelogs/5.1.0/#eager-loading). Please use SilverStripe 5.


## Usage
```php
MyModelClass::get()->with(['Relation1','Relation2'])->filter(...);
```

It does not require huge configuration - only one function to be added to the query builder chain : `->with([..relations...])`.
This will result in the final DataList to be presented by the `EagerLoadedDataList` class that handles the eager loading.

The module takes advantage of `DataList::getGenerator()` to query for and attach the related records only when needed.

## Installation
```
composer require gurucomkz/eagerloading
```
Every DataObject that has has_one/many_many/belongs_many_many which you wish to have eagerloaded must include `EagerLoaderMultiAccessor` (see below).
## Features

* [Using with $has_one / $belongs_to](docs/en/Using_With_HasOne.md)
* [Using with $has_many / $many_many / $belongs_many_many](docs/en/Using_With_HasMany.md)
* [Boosting GridField output](docs/en/Boosting_GridField.md)
* [Boosting CSV export](docs/en/Boosting_CSV_Export.md)

Read the docs for full explanation.
## Quick start

### 1. Add the following trait to all your models to use `$has_many`, `$many_many`, `$belongs_meny_many`:
```php
class MyClass extends DataObject {
    use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;

    // ...
}
```

If you have your own `__call()` read [Using with $has_many/$many_many](docs/en/Using_With_HasMany.md).

### 2. Declare `private static $eager_loading` to boost ModelAdmin's GridField output.

```php
class YourClass extends DataObject
{
    private static $eager_loading = [
        'Relation1',
        'Relation1.Relation4',
        'Relation2',
        'Relation3',
    ];
}
```
## TODO
* for `->with(['RelLevel1.RelLevel2'])` - do not query for `RelLevel1` IDs twice.
* for `->with(['RelLevel1','RelLevel1.RelLevel2'])` - do not query for `RelLevel1` IDs thrice.

## Reporting Issues
Please [create an issue](https://github.com/gurucomkz/silverstripe-eagerloading/issues) for any bugs you've found, or features you're missing.
