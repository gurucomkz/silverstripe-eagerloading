# Boosting CSV Export

The module tries to detect the fact that you are doing the CSV Export and scans the `private static $export_fields` for suitable relations and loads them in advance in attempt to speed up the export.

In the following example relations `Subscribers` and `Address` would be loaded as a whole for every exported rows and instead of being fetched one-by-one.

```php
class YourClass extends DataObject
{
    private static $has_one = [
        'Subscriber' => Subscriber::class,
    ];

    private static $has_many = [
        'Address' => Address::class,
    ];

    private static $export_fields = [
        'Created' => 'Date',
        'ID' => 'Order ID',
        'Subscriber.FirstName' => 'Recipient first name',
        'Subscriber.LastName' => 'Recipient last name',
        'Address.Address' => 'Recipient street address',
        'Address.Region' => 'Recipient state',
        'Address.Country' => 'Recipient country',
        'Address.PostalCode' => 'Recipient postcode',
    ];
}
```
