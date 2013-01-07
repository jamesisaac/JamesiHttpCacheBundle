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
 * 
 * This does things in a unique way because the structure of this class
 * changed between Symfony 2.0 and 2.1 - this approach should work with
 * both versions.
 */
class HttpCache extends BaseHttpCache
{
    protected $cacheDir;
    protected $kernel;

    /**
     * Modified constructor which creates the custom Esi class for all Symfony versions
     * 
     * {@inheritDoc}
     */
    public function __construct(HttpKernelInterface $kernel, $cacheDir = null)
    {
        $this->kernel = $kernel;
        $this->cacheDir = $cacheDir;
        
        $store = new Store($this->cacheDir ?: $this->kernel->getCacheDir().'/http_cache');

        BaseParentHttpCache::__construct($kernel, $store, $this->createEsi(), array_merge(array('debug' => $kernel->isDebug()), $this->getOptions()));
    }
    
    protected function createEsi()
    {
        return new \Jamesi\HttpCacheBundle\HttpCache\Esi();
    }
}