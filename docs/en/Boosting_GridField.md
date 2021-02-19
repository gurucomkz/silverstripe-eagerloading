# Boosting GridField Output

You can add a property to your DataObject class with a list of relations to eagerly load when browsing this class entities with a GridGield (in CMS area).

```php

class YourClass extends DataObject
{
    private static $eager_loading = [
        'Relation1',
        'Relation1.Relation4',
        'Relation2',
        'Relation3',
    ];

    private static $has_one = [
        'Relation1' => SomeClassOne::class,
    ];
    private static $has_many = [
        'Relation2' => SomeClassTwo::class,
    ];
    private static $many_many = [
        'Relation3' => SomeClassThree::class,
    ];
}

class SomeClassOne extends DataObject
{
    private static $has_one = [
        'Relation4' => SomeClassFour::class,
    ];
}
```
