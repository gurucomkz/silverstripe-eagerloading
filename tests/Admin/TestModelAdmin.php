<?php

namespace Gurucomkz\EagerLoading\Tests\Admin;

use Gurucomkz\EagerLoading\Tests\Models\Drink;
use Gurucomkz\EagerLoading\Tests\Models\Music;
use Gurucomkz\EagerLoading\Tests\Models\Origin;
use Gurucomkz\EagerLoading\Tests\Models\Player;
use Gurucomkz\EagerLoading\Tests\Models\Team;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Dev\TestOnly;

class TestModelAdmin extends ModelAdmin implements TestOnly
{

    private static $url_segment = 'eltestadmin';

    private static $menu_title = 'eltestadmin';

    private static $managed_models = [
        Player::class,
        Team::class,
        Drink::class,
        Music::class,
        Origin::class,
    ];
}
