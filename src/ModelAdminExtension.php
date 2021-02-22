<?php

namespace Gurucomkz\EagerLoading;

use SilverStripe\Admin\LeftAndMainExtension;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridFieldConfig;

class ModelAdminExtension extends LeftAndMainExtension
{

    /**
     * Adds Data manipulator for eagerloading
     *
     * @param GridFieldConfig $config
     * @return void
     */
    public function updateGridFieldConfig($config)
    {
        $config->addComponent(new GridFieldEagerLoadManipulator());
    }
}
