<?php

namespace Jamesi\HttpCacheBundle\Tests\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EsiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Checking all the old process functionality still works
     */
    public function testProcess()
    {
        $esi = new \Jamesi\HttpCacheBundle\HttpCache\Esi();

        $request = Request::create('/');
        $response = new Response('foo <esi:comment text="some comment" /><esi:include src="..." alt="alt" onerror="continue" />');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'alt\', true) ?>'."\n", $response->getContent());
        $this->assertEquals('ESI', $response->headers->get('x-body-eval'));

        $response = new Response('foo <esi:include src="..." />');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'\', false) ?>'."\n", $response->getContent());

        $response = new Response('foo <esi:include src="..."></esi:include>');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'\', false) ?>'."\n", $response->getContent());
    }
    
    public function testDidntSupportJson()
    {
        $esi = new \Symfony\Component\HttpKernel\HttpCache\Esi();
    
        $request = Request::create('/');
        $response = new Response('foo <esi:include src="..." />');
        $response->headers->set('Content-Type', 'application/json');
        $esi->process($request, $response);

        $this->assertEquals('foo <esi:include src="..." />', $response->getContent());
    }
    
    public function testSupportsJson()
    {
        $esi = new \Jamesi\HttpCacheBundle\HttpCache\Esi();
    
        $request = Request::create('/');
        $response = new Response('foo <esi:include src="..." />');
        $response->headers->set('Content-Type', 'application/json');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'\', false) ?>'."\n", $response->getContent());
    }
    
    /**
     * Checking original ESI processing behaves as expected
     *
     * @expectedException RuntimeException
     */
    public function testProcessDidntSupportJsonString()
    {
        $esi = new \Symfony\Component\HttpKernel\HttpCache\Esi();
        
        $request = Request::create('/');
        $content = 'foo <esi:comment text="some comment" /><esi:include src="..." alt="alt" onerror="continue" />';
        $response = new Response(json_encode($content));
        $esi->process($request, $response);
    }
    
    /**
     * Checking that JSON tags now work
     */
    public function testProcessJsonString()
    {
        $esi = new \Jamesi\HttpCacheBundle\HttpCache\Esi();
        
        $request = Request::create('/');
        $content = array('foo' => '<esi:comment text="some comment" /><esi:include src="..." alt="alt" onerror="continue" />');
        $response = new Response(json_encode($content));
        $esi->process($request, $response);

        $this->assertEquals('{"foo":<?php echo $this->esi->handle($this, \'...\', \'alt\', true) ?>'."\n".'}', $response->getContent());
        $this->assertEquals('ESI', $response->headers->get('x-body-eval'));
    }
}
