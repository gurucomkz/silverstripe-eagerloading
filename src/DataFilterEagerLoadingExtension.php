<?php
namespace Gurucomkz\EagerLoading;

use SilverStripe\ORM\DataExtension;

/**
 *
 * @property-read \SilverStripe\ORM\DataList $owner
 *
 */
class DataFilterEagerLoadingExtension extends DataExtension
{

    public $withList = [];
    public $withListOriginal = [];
    public function with($list)
    {
        // return $this->owner;
        if (!isset($this->owner->withList)) {
            $this->owner->withList = [];
        }
        if (!isset($this->owner->withListOriginal)) {
            $this->owner->withListOriginal = [];
        }
        if (!is_array($list)) {
            $list = [$list];
        }
        $this->owner->withListOriginal = $list;
        $list = array_map(
            function ($e) {
                return explode('.', $e);
            },
            $list
        );
        $this->owner->withList = array_merge($this->owner->withList, $list);
        return EagerLoadedDataList::cloneFrom($this->owner);
    }
}
