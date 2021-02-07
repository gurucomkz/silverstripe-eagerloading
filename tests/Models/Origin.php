<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Origin extends DataObject implements TestOnly
{
    use EagerLoaderMultiAccessor;

    private static $table_name = 'TestOrigin';
    private static $db = [
        'Title' => 'Varchar'
    ];

    private static $has_many = [
        'Teams' => Team::class,
        'Players' => Player::class,
    ];
}
