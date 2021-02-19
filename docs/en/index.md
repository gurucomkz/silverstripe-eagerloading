# Silverstripe EagerLoading

Module solves the [N+1 problem](https://stackoverflow.com/questions/97197/what-is-the-n1-selects-problem-in-orm-object-relational-mapping) in SilverStripe 4.

Normally SilverStripe uses Lazy Loading and fetches the required information only when it is actually accessed.

I.e.
```php
$items = MyModelClass::get(); # no query yet

foreach ($items as $item) { # makes a DB query for all $items
    echo $item->Feature1->Title; # makes DB query for ONE Feature1
    echo $item->Feature2->Title; # makes DB query for ONE Feature2
    foreach ($items->Feature3() as $subitem) { # makes a DB query for ALL Feature3
        echo $subitem->Title; # makes DB query for ONE Feature2
    }
}
```
With `K` rows in `MyModelClass`, `N` number of relations that are directly accessed every `for` iterations:
`K * (1 + N)` DB queries.

Using this module the code above can be reduced to `K + N * 3`.

## Solution

```php
MyModelClass::get()->with(['Relation1','Relation2'])->filter(...);
```

It does not require huge configuration - only one function to be added to the query builder chain : `->with([..relations...])`.

This will result in the final DataList to be presented by the `EagerLoadedDataList` class that handles the eager loading.

The module takes advantage of `DataList::getGenerator()` to query for and attach the related records only when needed.

## Examples

* [Using with $has_one / $belongs_to](Using_With_HasOne.md)
* [Using with $has_many / $many_many / $belongs_many_many](Using_With_HasMany.md)
* [Boosting GridField output](Boosting_GridField.md)
* [Boosting CSV export](Boosting_CSV_Export.md)
