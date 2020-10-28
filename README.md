# Silverstripe EagerLoading

Attempt to solve [N+1 problem](https://stackoverflow.com/questions/97197/what-is-the-n1-selects-problem-in-orm-object-relational-mapping) in SilverStripe 4.

Usage: 
```php
MyModelClass::get()->with(['Relation1','Relation2'])->filter(...);
```

As you can see, it does not require huge configuration - only one function to be added to the query builder chain : `->with([..relations...])`.
This will result in the final DataList to be presented by the `EagerLoadedDataList` class that handles the eager loading.

The module takes advantange of `DataList::getGenerator()` to query for and attach the related records only when needed.

Currently supports only HasOne & HasMany
For HasMany REQUIRES a 'RelationName()' & 'setRelationName()' functions explicitly declared as advised by https://github.com/unclecheese/silverstripe-eager-loader:
```php
public function setAttendees(array $attendees)
{
	$this->cachedAttendees = ArrayList::create($attendees);

	return $this;
}

public function Attendees()
{
	if ($this->cachedAttendees) {
		return $this->cachedAttendees;
	}

	return parent::Attendees();
}
```

## Admin GridField Eager Loading

You can declare `private static $eager_loading` in your model listing to leverage the feature in the ModelAdmin's GridField output.

Additionally, it tries to detect the fact that you are doing the CSV Export and scans the `$export_fields` for suitable relations and loads them 
in advance in attempt to speed up the export.
