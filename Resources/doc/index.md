This bundle makes two changes to Symfony2's default HTTP cache:

* Allows parts of a view to be cached through ESI, even if the master responsive has a "private" Cache-Control header (Symfony2 will be default force the entire response to be public)
* Makes it possible to use ESI within JSON responses

## Installation

### Include using composer

Add the bundle to composer.json

``` json
"require": {
    // ...
    "jamesi/http-cache-bundle": "*"
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

Add the following to parameters.ini:

``` yaml
parameters:
    # ...
    esi.class: Jamesi\HttpCacheBundle\HttpCache\Esi
```

### Base AppCache on the new class

``` php
<?php
// app/AppCache.php

// use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Jamesi\HttpCacheBundle\HttpCache\HttpCache;
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
    $response = new Resopnse();
    $response->setSharedMaxAge(600);
    
    return $this->render('_component.html.twig');
}
```

``` twig
{# index.html.twig #}

{% render 'component' with {}, {'standalone: true} %}
```

If the bundle is configured correctly, the master response won't have a
public Cache-Control header, and the "component" response will have been
cached and served via ESI.

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
        'objects'   => $json_objects,
    );
    
    $response = new Response();
    $response->setContent(json_encode($responseContent));
    
    return $response;
}
```
