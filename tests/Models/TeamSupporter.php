<?php

namespace Gurucomkz\EagerLoading\Tests\Models;

use SilverStripe\ORM\DataObject;

class TeamSupporter extends DataObject
{
    private static $table_name = 'TeamSupporter';

    private static $db = [
        'Ranking' => 'Int',
    ];

    private static $has_one = [
        'Team' => Team::class,
        'Supporter' => Supporter::class,
    ];

    private static $default_sort = '"TeamSupporter"."Ranking" ASC';
}
