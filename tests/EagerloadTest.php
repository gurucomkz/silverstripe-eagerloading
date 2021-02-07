<?php

namespace Gurucomkz\EagerLoading\Tests;

use Gurucomkz\EagerLoading\ProxyDBCounterExtension;
use Gurucomkz\EagerLoading\Tests\Models\Music;
use Gurucomkz\EagerLoading\Tests\Models\Origin;
use Gurucomkz\EagerLoading\Tests\Models\Player;
use Gurucomkz\EagerLoading\Tests\Models\Team;
use SilverStripe\Dev\SapphireTest;

class EagerloadTest extends SapphireTest
{

    protected static $fixture_file = 'fixtures.yml';

    protected static $extra_dataobjects = [
        Origin::class,
        Music::class,
        Team::class,
        Player::class,
    ];

    public function testHasOne()
    {
        $expectedQueries = 4;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with('Team');

        foreach($players as $player) {
            $team = $player->Team->Title;
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    }

    public function testHasMany()
    {
        $expectedQueries = 3;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $teams = Team::get()->with('Players');

        foreach($teams as $team) {
            $players = $team->Players()->map()->toArray();
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    }

    public function testManyMany()
    {
        $expectedQueries = 4;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with('Listens');

        foreach($players as $player) {
            $music = $player->Listens()->map()->toArray();
            // print_r($music);
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    }

    // public function testBelongsToManyMany()
    // {
    //     $expectedQueries = 4;
    //     ProxyDBCounterExtension::resetQueries();
    //     $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

    //     $music = Music::get()->with('Players');

    //     foreach($music as $genre) {
    //         $players = $genre->Players()->map()->toArray();
    //     }
    //     $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

    //     // print_r(ProxyDBCounterExtension::getQueries());

    //     $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    // }

    public function test2HasOne()
    {
        $expectedQueries = 5;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with(['Team','Origin']);

        foreach($players as $player) {
            $team = $player->Team->Title;
            $origin = $player->Origin->Title;
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        // print_r(ProxyDBCounterExtension::getQueries());

        $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    }

    public function testHasOneChain()
    {
        $expectedQueries = 7;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with(['Team.Origin']);

        foreach($players as $player) {
            $team = $player->Team->Origin->Title;
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        // print_r(ProxyDBCounterExtension::getQueries());

        $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    }

    public function testChainOneMany()
    {
        $expectedQueries = 6;
        ProxyDBCounterExtension::resetQueries();
        $pre_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $players = Player::get()->with(['Team.Players']);

        foreach($players as $player) {
            $team = $player->Team->Players()->map()->toArray();
        }
        $post_fetch_count = ProxyDBCounterExtension::getQueriesCount();

        $this->assertEquals($pre_fetch_count + $expectedQueries,$post_fetch_count);
    }

}
