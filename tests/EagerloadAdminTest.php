<?php

namespace Gurucomkz\EagerLoading\Tests;

use Gurucomkz\EagerLoading\GridFieldEagerLoadManipulator;
use Gurucomkz\EagerLoading\Tests\Admin\TestModelAdmin;
use Gurucomkz\EagerLoading\Tests\Models\Drink;
use Gurucomkz\EagerLoading\Tests\Models\Music;
use Gurucomkz\EagerLoading\Tests\Models\Origin;
use Gurucomkz\EagerLoading\Tests\Models\Player;
use Gurucomkz\EagerLoading\Tests\Models\Team;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\GridField\FormAction\StateStore;
use SilverStripe\Security\Permission;

class EagerloadAdminTest extends FunctionalTest
{

    protected static $fixture_file = 'fixtures.yml';

    protected static $extra_dataobjects = [
        Player::class,
        Origin::class,
        Music::class,
        Team::class,
        Drink::class,
    ];

    protected static $extra_controllers = [
        TestModelAdmin::class,
    ];

    public function testModelAdminOpens()
    {
        $this->autoFollowRedirection = false;
        $this->logInAs('admin');
        $this->assertTrue((bool)Permission::check("ADMIN"));
        $this->assertEquals(200, $this->get('admin/eltestadmin')->getStatusCode());
    }

    public function testAdminContansExtension()
    {
        $admin = new TestModelAdmin();
        $request = new HTTPRequest('GET', '/');
        $request->setSession(new Session([]));
        $admin->setRequest($request);
        $admin->doInit();

        $form = $admin->getEditForm();
        $field = $form->Fields()->fieldByName('Gurucomkz-EagerLoading-Tests-Models-Player');
        $this->assertNotNull($field, 'A GridField has been found on the form.');

        $config = $field->getConfig();

        $this->assertNotNull(
            $config->getComponentByType(GridFieldEagerLoadManipulator::class),
            'GridFieldEagerLoadManipulator added'
        );
    }

    public function testAdminExport()
    {
        $exportActionStateID = $this->getActionSessionKey('export');
        $exportActionText = _t('SilverStripe\\Forms\\GridField\\GridField.CSVEXPORT', 'Export to CSV');
        $getVars = [
            'action_gridFieldAlterAction?StateID=' . $exportActionStateID => $exportActionText,
        ];

        $this->autoFollowRedirection = false;
        $this->logInAs('admin');
        $this->assertTrue((bool)Permission::check("ADMIN"));
        $modelClassName = "Gurucomkz-EagerLoading-Tests-Models-Player";
        $getLink = "admin/eltestadmin/$modelClassName/EditForm/field/$modelClassName?" . http_build_query($getVars);

        $this->assertEquals(200, $this->get($getLink)->getStatusCode());
    }

    public function getActionSessionKey($action)
    {
        $this->autoFollowRedirection = false;
        $this->logInAs('admin');
        $modelClassName = "Gurucomkz-EagerLoading-Tests-Models-Player";
        $getLink = "admin/eltestadmin/$modelClassName/EditForm/field/$modelClassName";

        $body = $this->get($getLink)->getBody();

        preg_match_all('/"action_gridFieldAlterAction\?StateID=([^"]+)"/', $body, $matches);

        /** @var StateStore $store */
        $store = Injector::inst()->create(StateStore::class . '.' . 'Gurucomkz-EagerLoading-Tests-Models-Player');

        foreach ($matches[1] as $actionCode) {
            $actionData = $store->load($actionCode);
            if ($action == $actionData['actionName']) {
                return $actionCode;
            }
        }
        return null;
    }
}
