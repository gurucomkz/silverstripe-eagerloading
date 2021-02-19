<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Drink extends DataObject implements TestOnly
{
    private static $table_name = 'TestDrink';
    private static $db = [
        'Title' => 'Varchar'
    ];

    private static $has_many = [
        'Players' => Player::class,
    ];
}
