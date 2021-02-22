<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Music extends DataObject implements TestOnly
{
    use EagerLoaderMultiAccessor;
    private static $table_name = 'TestMusic';
    private static $db = [
        'Title' => 'Varchar'
    ];

    private static $belongs_many_many = [
        'Players' => Player::class,
    ];

    private static $export_fields = [
        'ID' => 'ID',
        'Title' => 'Title',
    ];
}
