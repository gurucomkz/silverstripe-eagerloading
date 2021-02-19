# Using with `$has_many`, `$many_many` and `$belongs_many_many`

Module is able to load such relations out of the bot, but accessing it is a problem because it has to be done through functions.

To enable access to `$has_many`, `$many_many`, `$belongs_many_many` **add the following trait to all your models**:
```php
class MyClass extends DataObject {
    use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;

    // ...
}
```

This trait declares `__call()` method necessary for accessing the eager-loaded data.

## When you have your own `__call()` method

If you have your own `__call()`, please put the contents of `EagerLoaderMultiAccessor::__call()` into it:

```php
class MyClass extends DataObject {

    public function __call($fn, $params)
    {
        // Copy contents of EagerLoaderMultiAccessor::__call() here

        // your code goes here
    }
}
```

If the trait is not included, an exception will be thrown on attempt to use `$has_many`, `$many_many` or `$belongs_meny_many`.
