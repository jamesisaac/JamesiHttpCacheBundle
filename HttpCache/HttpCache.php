<?php

namespace Jamesi\HttpCacheBundle\HttpCache;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache;
use Symfony\Component\HttpKernel\HttpCache\HttpCache as BaseParentHttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;

/**
 * Modified HttpCache which also allows ESI in json responses.
 * 
 * Base your AppCache class off this, and remember to set the esi.class
 * parameter too! 
 */
class HttpCache extends BaseHttpCache
{
    /**
     * ESI within JSON can be switched on/off with the inclusion of
     * "application/json" in this array
     */
    protected $esiContentTypes = array('text/html', 'text/xml', 'application/xml', 'application/json');
    
    /**
     * Modified constructor which creates the custom Esi class, and additionally
     * adds "application/json" as a valid type
     * 
     * {@inheritDoc}
     */
    public function __construct(HttpKernelInterface $kernel)
    {
        $store = new Store($kernel->getCacheDir().'/http_cache');
        $esi = new Esi($this->esiContentTypes);

        BaseParentHttpCache::__construct($kernel, $store, $esi, array_merge(array('debug' => $kernel->isDebug()), $this->getOptions()));
    }
}