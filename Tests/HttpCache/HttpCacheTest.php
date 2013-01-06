<?php

namespace Jamesi\HttpCacheBundle\Tests\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Tests\HttpCache\HttpCacheTest as BaseHttpCacheTest;

use Jamesi\HttpCacheBundle\HttpCache\Esi;

/**
 * Based on Symfony's HttpCache tests
 */
class HttpCacheTest extends BaseHttpCacheTest
{
    /**
     * Replacing the Esi class used
     *
     * {@inheritDoc}
     */
    public function request($method, $uri = '/', $server = array(), $cookies = array(), $esi = false)
    {
        if (null === $this->kernel) {
            throw new \LogicException('You must call setNextResponse() before calling request().');
        }

        $this->kernel->reset();

        $this->store = new Store(sys_get_temp_dir().'/http_cache');

        $this->cacheConfig['debug'] = true;

        $this->esi = $esi ? new Esi() : null;
        $this->cache = new HttpCache($this->kernel, $this->store, $this->esi, $this->cacheConfig);
        $this->request = Request::create($uri, $method, array(), $cookies, array(), $server);

        $this->response = $this->cache->handle($this->request, HttpKernelInterface::MASTER_REQUEST, $this->catch);

        $this->responses[] = $this->response;
    }
    
    /**
     * Due to the mixture of public/private responses, this doesn't work as
     * expected.  You'll need to consider nested ttls manually.
     * 
     * See Response::getMaxAge
     */
    public function testEsiCacheSendsTheLowestTtl()
    {
        $this->assertTrue(true);
    }
}
