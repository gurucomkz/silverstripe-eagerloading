<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Team extends DataObject implements TestOnly
{
    use EagerLoaderMultiAccessor;

    private static $table_name = 'TestTeam';
    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $has_one = [
        'Origin' => Origin::class,
    ];
    private static $has_many = [
        'Players' => Player::class,
    ];
}
