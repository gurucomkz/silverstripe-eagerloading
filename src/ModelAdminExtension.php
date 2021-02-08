<?php

namespace Gurucomkz\EagerLoading;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridFieldConfig;

class ModelAdminExtension extends Extension
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
