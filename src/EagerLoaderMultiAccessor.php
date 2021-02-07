<?php

namespace Gurucomkz\EagerLoading;

/**
 * Use this trait to allow access to the eager-loaded has_many & many_many fields.
 */
trait EagerLoaderMultiAccessor {
    public function __call($method, $arguments)
    {
        if($method !== 'tryEagerLoadingRelation'){
            if(null !== ($eagerResult = $this->tryEagerLoadingRelation($method))) {
                return $eagerResult;
            }
        }
        return parent::__call($method, $arguments);
    }
}
