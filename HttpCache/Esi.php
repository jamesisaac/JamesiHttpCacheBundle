<?php

namespace Jamesi\HttpCacheBundle\HttpCache;

use Symfony\Component\HttpKernel\HttpCache\Esi as BaseEsi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A modified Esi class which can deal with ESI tags within encoded json
 *
 * {@inheritDoc}
 */
class Esi extends BaseEsi
{
    protected $contentTypes;

    /**
     * Old constructor with added application/json support by default
     *
     * {@inheritDoc}
     */
    public function __construct(array $contentTypes = array('text/html', 'text/xml', 'application/xml', 'application/json'))
    {
        $this->contentTypes = $contentTypes;
    }
    
    /**
     * Returns our custom class
     *
     * {@inheritDoc}
     */
    public function createCacheStrategy()
    {
        return new \Jamesi\HttpCacheBundle\HttpCache\EsiResponseCacheStrategy();
    }
    
    /**
     * Re-implimenting because handleEsiIncludeTag and contentTypes are private
     *
     * {@inheritDoc}
     */
    public function process(Request $request, Response $response)
    {
        $this->request = $request;
        $type = $response->headers->get('Content-Type');
        if (empty($type)) {
            $type = 'text/html';
        }

        $parts = explode(';', $type);
        if (!in_array($parts[0], $this->contentTypes)) {
            return $response;
        }

        // we don't use a proper XML parser here as we can have ESI tags in a plain text response
        $content = $response->getContent();
        $content = str_replace(array('<?', '<%'), array('<?php echo "<?"; ?>', '<?php echo "<%"; ?>'), $content);
        $content = preg_replace_callback('#<esi\:include\s+(.*?)\s*(?:/|</esi\:include)>#', array($this, 'handleEsiIncludeTag'), $content);
        $content = preg_replace('#<esi\:comment[^>]*(?:/|</esi\:comment)>#', '', $content);
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
    
    /**
     * Re-implimenting because the method is declared private
     *
     * {@inheritDoc}
     */
    protected function handleEsiIncludeTag($attributes)
    {
        // Strip any backslashes, which will appear in the case of a json response
        $attributes[1] = stripslashes($attributes[1]);
        
        $options = array();
        preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $attributes[1], $matches, PREG_SET_ORDER);
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