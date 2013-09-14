<?php

namespace Jamesi\HttpCacheBundle\HttpCache;

use Symfony\Component\HttpKernel\HttpCache\EsiResponseCacheStrategy as BaseStrategy;
use Symfony\Component\HttpFoundation\Response;

/**
 * A modified strategy which doesn't force all pages using ESI to be public 
 *
 * {@inheritDoc}
 */
class EsiResponseCacheStrategy extends BaseStrategy
{
    private $cacheable = true;
    private $embeddedResponses = 0;
    private $ttls = array();
    private $maxAges = array();
    
    /**
     * {@inheritDoc}
     */
    public function add(Response $response)
    {
        if ($response->isValidateable()) {
            $this->cacheable = false;
        } else {
            $this->ttls[] = $response->getTtl();
            $this->maxAges[] = $response->getMaxAge();
        }
        
        $this->embeddedResponses++;
    }
    
    /**
     * {@inheritDoc}
     */
    public function update(Response $response)
    {
        // if we have no embedded Response, do nothing
        if (0 === $this->embeddedResponses) {
            return;
        }

        if (!$this->cacheable) {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');

            return;
        }
        
        $this->ttls[] = $response->getTtl();
        $this->maxAges[] = $response->getMaxAge();

        if (null !== $maxAge = min($this->maxAges)) {
            // This is the change to default behaviour - we only call
            // setSharedMaxAge if the response was already public
            if ($response->headers->hasCacheControlDirective('public')) {
                $response->setSharedMaxAge($maxAge);
            } else {
                $response->setMaxAge($maxAge);
            }
            $response->headers->set('Age', $maxAge - min($this->ttls));
        }
        // We also no longer want to force to private maxAge to be 0 here
    }
}