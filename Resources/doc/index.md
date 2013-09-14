JamesiHttpCacheBundle
=====================

This bundle makes two changes to Symfony2's default HTTP cache (currently
supporting Symfony 2.1 and 2.2):

* Allows parts of a view to be cached through ESI, even if the master response
  has a "private" Cache-Control header (Symfony2 will be default force the
  entire response to be public).  This replicates the ``sf_cache_key`` behaviour
  of Symfony of 1.4 which allowed for easy partial caching.
* Makes it possible to use ESI within JSON responses

**Disclaimer**: Please only use this bundle if you have a solid understanding
of ESI caching, as it removes some of the safeguards put in place by default
with Symfony2.  If you were to, for example, include a user's private content
via ESI without a cache key that's unique to them, that content is likely to leak
through to other users.

## Installation

### Include using composer

Add the bundle to composer.json

``` js
"require": {
    // ...
    "jamesi/http-cache-bundle": "*@dev"
}
```

Update the packages through composer:

``` bash
$ php composer.phar update
```

### Enable the bundle

Add the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Jamesi\HttpCacheBundle\JamesiHttpCacheBundle(),
    );
}
```

### Set the ESI class

Add the following to parameters.yml:

``` yaml
parameters:
    # ...
    esi.class: Jamesi\HttpCacheBundle\HttpCache\Esi
```

### Base AppCache on the new class

``` php
<?php
// app/AppCache.php

require_once __DIR__.'/AppKernel.php';

// use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Jamesi\HttpCacheBundle\HttpCache\HttpCache;

class AppCache extends HttpCache
{
}
```

## Usage

### Using ESI within a private response

``` php
<?php
// Controller.php
<?php

public function indexAction()
{
    return $this->render('index.html.twig');
}

public function componentAction()
{
    $response = new Response();
    $response->setSharedMaxAge(600);
    
    return $this->render('_component.html.twig');
}
```

``` jinja
{# index.html.twig #}

{% render 'component' with {}, {'standalone': true} %}
```

If the bundle is configured correctly, the master response won't have a
public Cache-Control header, and the "component" response will have been
cached and served via ESI.

*Cache keys* can be passed in via the first set of parameters, which cause
ESI to store/retrieve a unique response:

``` jinja
{# Symfony 2.1 style #}
{% render 'component' with {'user_id': id}, {'standalone': true} %}

{# Symfony 2.2+ style #}
{{ render_esi(controller('..:component', { 'token': token})) }}
```

### Using ESI within JSON

Useful for sending an AJAX response with a large number of objects which
can be independently cached

``` php
// Controller.php
<?php

public function indexAction()
{
    $jsonObjects = array();
    foreach ($objects as $object) {
        $content = $this->get('templating.helper.actions')
            ->render('viewObjectAsJson', 
                array('id' => $object->getId())
                array('standalone' => true));
                
        $jsonObjects[] = $this->get('jamesi_http_cache.helper')
            ->processJsonContent($content);
    }
    
    $responseContent = array(
        'foo'       => 'bar',
        'objects'   => $jsonObjects,
    );
    
    $response = new Response();
    $response->setContent(json_encode($responseContent));
    
    return $response;
}
```
