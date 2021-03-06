<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use Gurucomkz\EagerLoading\EagerLoaderMultiAccessor;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Player extends DataObject implements TestOnly
{
    use EagerLoaderMultiAccessor;

    private static $table_name = 'TestPlayer';
    private static $db = [
        'Title' => 'Varchar'
    ];

    private static $many_many = [
        'Listens' => Music::class,
    ];

    private static $has_one = [
        'Team' => Team::class,
        'Origin' => Origin::class,
        'Drink' => Drink::class,
    ];

    private static $export_fields = [
        'ID' => 'ID',
        'Title' => 'Title',
        'Team.Title' => 'Team',
        'Origin.Title' => 'Origin',
    ];

    private static $eager_loading = [
        'Listens',
        'Origin',
    ];
}
