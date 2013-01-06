JamesiHttpCacheBundle
=====================

This bundle makes two changes to Symfony2's default HTTP cache:

* Allows parts of a view to be cached through ESI, even if the master responsive has a "private" Cache-Control header (Symfony2 will be default force the entire response to be public)
* Makes it possible to use ESI within JSON responses

For documentation, see:

[`Resources/doc/index.md`](https://github.com/jamesisaac/JamesiHttpCacheBundle/blob/master/Resources/doc/index.md)