<?php

namespace Gurucomkz\EagerLoading\Tests;

use Gurucomkz\EagerLoading\EagerLoadingException;
use Gurucomkz\EagerLoading\Tests\Models\Drink;
use Gurucomkz\EagerLoading\Tests\Models\Music;
use Gurucomkz\EagerLoading\Tests\Models\Origin;
use Gurucomkz\EagerLoading\Tests\Models\Player;
use Gurucomkz\EagerLoading\Tests\Models\Supporter;
use Gurucomkz\EagerLoading\Tests\Models\Team;
use Gurucomkz\EagerLoading\Tests\Models\TeamSupporter;
use SilverStripe\Dev\SapphireTest;

class EagerloadTest extends SapphireTest
{

    protected static $fixture_file = 'fixtures.yml';

    protected static $extra_dataobjects = [
        Origin::class,
        Music::class,
        Team::class,
        Player::class,
        Drink::class,
        Supporter::class,
        TeamSupporter::class,
    ];

    public function testNoTrait()
    {
        $this->assertFalse(
            method_exists(Drink::class, 'addEagerRelation'),
            'Drink Class should not have "addEagerRelation" method'
        );
        try {
            $drinks = Drink::get()->with('Players')->first();
            foreach ($drinks as $drink) {
                $drink->Players()->map()->toArray();
            }
        } catch (EagerLoadingException $th) {
            return;
        }
        $this->fail('No EagerLoadingException raised');
    }

    public function testWrongNames()
    {
        try {
            $record = Drink::get()->with('Bubbles')->first();
            $this->assertNotNull($record);
        } catch (\Exception $th) {
            $this->fail('Wrong names should fail silently');
        }
    }

    public function testHasOne()
    {
        $expectedQueries = 4;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with('Team');

        foreach ($players as $player) {
            $player->Team->Title;
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function testHasMany()
    {
        $expectedQueries = 3;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $teams = Team::get()->with('Players');

        foreach ($teams as $team) {
            $team->Players()->map()->toArray();
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function testManyManyPlain()
    {
        $expectedQueries = 4;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with('Listens');

        foreach ($players as $player) {
            $player->Listens()->map()->toArray();
            // print_r($music);
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function testManyManyThrough()
    {
        $expectedQueries = 4;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $teams = Team::get()->with('Supporters');

        foreach ($teams as $team) {
            $supporters = $team->Supporters()->map()->toArray();
            // print_r(ProxyDBCounterExtension::getQueries());
            switch($team->Title){
                case 'The Hurricanes':
                    $this->assertCount(3, $supporters);
                    $this->assertEquals([
                        'Supporter 1',
                        'Supporter 5',
                        'Supporter 3',
                    ], array_values($supporters));
                    break;
                case 'The Crusaders':
                    $this->assertCount(1, $supporters);
                    $this->assertTrue(in_array('Supporter 1', $supporters));
                    break;
                case 'The Bears':
                    $this->assertCount(5, $supporters);
                    $this->assertEquals([
                        'Supporter 1',
                        'Supporter 4',
                        'Supporter 5',
                        'Supporter 3',
                        'Supporter 2',
                    ], array_values($supporters));
                    break;
            }
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function testBelongsToManyMany()
    {
        $expectedQueries = 4;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $music = Music::get()->with('Players');

        foreach ($music as $genre) {
            $genre->Players()->map()->toArray();
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        // print_r(ProxyDBCounterExtension::getQueries());

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function test2HasOne()
    {
        $expectedQueries = 5;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with(['Team', 'Origin']);

        foreach ($players as $player) {
            $player->Team->Title;
            $player->Origin->Title;
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        // print_r(ProxyDBCounterExtension::getQueries());

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function testHasOneChain()
    {
        $expectedQueries = 7;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with(['Team.Origin']);

        foreach ($players as $player) {
            $player->Team->Origin->Title;
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        // print_r(ProxyDBCounterExtension::getQueries());

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }

    public function testChainOneMany()
    {
        $expectedQueries = 6;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with(['Team.Players']);

        foreach ($players as $player) {
            $player->Team->Players()->map()->toArray();
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries, $post_fetch_count);
    }
}
