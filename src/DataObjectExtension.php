<?php
namespace Gurucomkz\EagerLoading;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;

class DataObjectExtension extends DataExtension {

    public $_eagerLoadingCache = [];

    public function addEagerRelation($relationName, $content)
    {
        $this->_eagerLoadingCache[$relationName] = ArrayList::create($content);
    }

    /**
     * must be called from
     *
     * @param string $method
     * @param array $arguments
     * @return Object|void Void when nothing found
     */
    public function tryEagerLoadingRelation($method)
    {
        if(!isset($this->_eagerLoadingCache[$method])) {
            return null;
        }
        return $this->_eagerLoadingCache[$method];
    }

}
