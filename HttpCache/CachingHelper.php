<?php

namespace Jamesi\HttpCacheBundle\HttpCache;

class CachingHelper
{
    /**
     * Helper for dealing with cached JSON responses.
     *
     * When rendering a json template as standalone:true in another json
     * controller, pass the content through this before adding it to the
     * response array.
     * 
     * If the content is simply an esi:include tag, we don't want to
     * json_decode it, as it's not valid json content so will return "null".
     * 
     * If the content is already valid json (e.g. if esi is disabled), then
     * it should be json_decoded.
     * 
     * @param string $content
     * @return string|mixed
     */
    public function processJsonContent($content)
    {
        if (preg_match('#^<esi\:include\s+(.*?)\s*.?/>$#', $content)) {
            return $content;
        } else {
            return json_decode($content, true);
        }
    }
}