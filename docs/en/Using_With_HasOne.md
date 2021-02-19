# Using With `$has_one`

This type of relations is supported out of the box without any structural modifications.

Simply add `->with([...relations...])` to the ORM invocation chain when needed.

```php
MyModelClass::get()->with(['Relation1','Relation2'])->filter(...);
```

Once your code starts accessing data from the query, module will load related entries from `Relation1` and `Relation2` with much less queries, than doing that one-by-one as SilverStripe usually does.
