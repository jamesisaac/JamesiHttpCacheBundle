<?php

namespace Jamesi\HttpCacheBundle\Tests\HttpCache;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HttpCacheTest extends WebTestCase
{
    public function testConstruct()
    {
        $client = static::createClient();
        $kernel = $client->getKernel();
    
        $cache = new \Jamesi\HttpCacheBundle\HttpCache\HttpCache($kernel);
        
        $this->assertTrue($cache->getEsi() instanceof \Jamesi\HttpCacheBundle\HttpCache\Esi);
    }
}
