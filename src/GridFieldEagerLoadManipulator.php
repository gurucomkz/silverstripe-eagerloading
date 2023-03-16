<?php
namespace Gurucomkz\EagerLoading;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_DataManipulator;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\SS_List;

/**
 * Adds support for $eager_loading config on the gridfields.
 * Usage:
 * class MyModelAdmin extends ModelAdmin {
 *   protected function getGridFieldConfig(): GridFieldConfig
 *   {
 *      $config->addComponent(new GridFieldLazyLoadManipulator());
 *   }
 * }
 */

class GridFieldEagerLoadManipulator implements GridField_DataManipulator
{
    /**
     * Manipulate the {@link SS_List} as needed by this grid modifier.
     *
     * @param GridField $gridField
     * @param SS_List $dataList
     * @return SS_List
     */
    public function getManipulatedData(GridField $gridField, SS_List $dataList)
    {
        /** @var DataList|DataFilterEagerLoadingExtension $dataList */
        $class = $dataList->dataClass();
        $config = Config::forClass($class);
        $vars = $gridField->getForm()->getController()->getRequest()->requestVars();
        $eager = $config->get('eager_loading');
        $exportString = _t('SilverStripe\\Forms\\GridField\\GridField.CSVEXPORT', 'Export to CSV');
        if (in_array($exportString, $vars)) {
            $export_fields = $config->get('export_fields');
            if ($export_fields) {
                foreach ($export_fields as $field => $_title) {
                    $parts = explode('.', $field);
                    if (count($parts) < 2) {
                        continue;
                    }
                    $main = implode('.', array_slice($parts, 0, -1));
                    if (!in_array($main, $eager)) {
                        $eager[] = $main;
                    }
                }
            }
        }
        if ($eager) {
            return $dataList->with($eager);
        }
        return $dataList;
    }
}
