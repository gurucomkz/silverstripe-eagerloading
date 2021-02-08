<?php

namespace Gurucomkz\EagerLoading;

use SilverStripe\ORM\ArrayList;

/**
 * Use this trait to allow access to the eager-loaded has_many & many_many fields.
 *
 */
trait EagerLoaderMultiAccessor
{
    public function __call($method, $arguments)
    {
        if ($method !== 'tryEagerLoadingRelation') {
            if (null !== ($eagerResult = $this->tryEagerLoadingRelation($method))) {
                return $eagerResult;
            }
        }
        return parent::__call($method, $arguments);
    }

    public $_eagerLoadingCache = [];

    public function addEagerRelation($relationName, $content)
    {
        if (!isset($this->_eagerLoadingCache)) {
            $this->_eagerLoadingCache = [];
        }
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
        if (!isset($this->_eagerLoadingCache[$method])) {
            return null;
        }

        return $this->_eagerLoadingCache[$method];
    }
}
