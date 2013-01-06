<?php

namespace Jamesi\HttpCacheBundle\HttpCache;

use Symfony\Component\HttpKernel\HttpCache\Esi as BaseEsi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Jamesi\HttpCacheBundle\HttpCache\EsiResponseCacheStrategy;

/**
 * A modified Esi class which can deal with ESI tags within encoded json
 */
class Esi extends BaseEsi
{
    protected $contentTypes;
    
    /**
     * Content type of the current request
     */
    protected $contentType;

    public function __construct(array $contentTypes = array())
    {
        $this->contentTypes = $contentTypes;
    }
    
    /**
     * Returns a new cache strategy instance - in this case, our custom class
     */
    public function createCacheStrategy()
    {
        return new EsiResponseCacheStrategy();
    }
    
    /**
     * Replaces a Response ESI tags with the included resource content.
     *
     * @param Request  $request  A Request instance
     * @param Response $response A Response instance
     */
    public function process(Request $request, Response $response)
    {
        $this->request = $request;
        $type = $response->headers->get('Content-Type');
        if (empty($type)) {
            $type = 'text/html';
        }

        $parts = explode(';', $type);
        $this->contentType = $parts[0];
        if (!in_array($this->contentType, $this->contentTypes)) {
            return $response;
        }

        // we don't use a proper XML parser here as we can have ESI tags in a plain text response
        $content = $response->getContent();
        $content = preg_replace_callback('#"?<esi\:include\s+(.*?)\s*.?/>"?#', array($this, 'handleEsiIncludeTag'), $content);
        $content = preg_replace('#<esi\:comment[^>]*/>#', '', $content);
        $content = preg_replace('#<esi\:remove>.*?</esi\:remove>#', '', $content);

        $response->setContent($content);
        $response->headers->set('X-Body-Eval', 'ESI');

        // remove ESI/1.0 from the Surrogate-Control header
        if ($response->headers->has('Surrogate-Control')) {
            $value = $response->headers->get('Surrogate-Control');
            if ('content="ESI/1.0"' == $value) {
                $response->headers->remove('Surrogate-Control');
            } elseif (preg_match('#,\s*content="ESI/1.0"#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#,\s*content="ESI/1.0"#', '', $value));
            } elseif (preg_match('#content="ESI/1.0",\s*#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#content="ESI/1.0",\s*#', '', $value));
            }
        }
    }
    
    private function handleEsiIncludeTag($attributes)
    {
        // Strip any backslashes, which will appear in the case of a json response
        $string = stripslashes($attributes[1]);
        
        $options = array();
        preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $string, $matches, PREG_SET_ORDER);
        foreach ($matches as $set) {
            $options[$set[1]] = $set[2];
        }

        if (!isset($options['src'])) {
            throw new \RuntimeException('Unable to process an ESI tag without a "src" attribute.');
        }

        return sprintf('<?php echo $this->esi->handle($this, \'%s\', \'%s\', %s) ?>'."\n",
            $options['src'],
            isset($options['alt']) ? $options['alt'] : null,
            isset($options['onerror']) && 'continue' == $options['onerror'] ? 'true' : 'false'
        );
    }
}