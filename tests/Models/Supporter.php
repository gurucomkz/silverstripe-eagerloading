<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use SilverStripe\ORM\DataObject;

class Supporter extends DataObject
{
    private static $table_name = 'Supporter';

    private static $db = [
        'Title' => 'Varchar',
    ];
    private static $belongs_many_many = [
        'Supports' => Team::class,
    ];
}
