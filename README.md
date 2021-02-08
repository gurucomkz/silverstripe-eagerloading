# Silverstripe EagerLoading (UNSTABLE)

Attempt to solve [N+1 problem](https://stackoverflow.com/questions/97197/what-is-the-n1-selects-problem-in-orm-object-relational-mapping) in SilverStripe 4.

Usage: 
```php
MyModelClass::get()->with(['Relation1','Relation2'])->filter(...);
```

As you can see, it does not require huge configuration - only one function to be added to the query builder chain : `->with([..relations...])`.
This will result in the final DataList to be presented by the `EagerLoadedDataList` class that handles the eager loading.

The module takes advantange of `DataList::getGenerator()` to query for and attach the related records only when needed.

## Features

### `$has_one`

Out of the box - no changes needed.

### `$has_many`, `$many_many`, `$belongs_meny_many`

Add the following trait to all your models to use `$has_many`, `$many_many`, `$belongs_meny_many`:
```php
class MyClass extends DataObject {
    use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;

    // ...
}
```

This trait declares `__call()` method necessary for accessing the eager-loaded data.

If you have your own `__call()`, please put the contents of `EagerLoaderMultiAccessor::__call()` into it (traits do not override already declared methods).

If the trait is not included, an exception will be thrown on attempt to use `$has_many`, `$many_many` or `$belongs_meny_many`.
## Admin GridField Eager Loading

You can declare `private static $eager_loading` in your model listing to leverage the feature in the ModelAdmin's GridField output.

Additionally, it tries to detect the fact that you are doing the CSV Export and scans the `$export_fields` for suitable relations and loads them 
in advance in attempt to speed up the export.

## TODO
* Detect 'LIMIT' constraints and load only relevant daya instead of all.
* for `->with(['RelLevel1.RelLevel2'])` - do not query for `RelLevel1` IDs twice.
* for `->with(['RelLevel1','RelLevel1.RelLevel2'])` - do not query for `RelLevel1` IDs thrice.
