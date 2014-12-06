JamesiHttpCacheBundle
=====================

[![Build Status](https://travis-ci.org/jamesisaac/JamesiHttpCacheBundle.png?branch=master)](https://travis-ci.org/jamesisaac/JamesiHttpCacheBundle)

This bundle makes two changes to Symfony2's default HTTP cache (currently
supporting Symfony versions 2.1 and 2.2):

* Allows parts of a view to be cached through ESI, even if the master response
  has a "private" Cache-Control header (Symfony2 will be default force the
  entire response to be public).  This replicates the ``sf_cache_key`` behaviour
  of Symfony of 1.4 which allowed for easy partial caching.
  (**Important**: Use release 0.1.1 if you want this feature.  It has been
  removed from later versions, as Symfony now supports it out of the box).
* Makes it possible to use ESI within JSON responses

**Disclaimer**: Please only use this bundle if you have a solid understanding
of ESI caching, as it removes some of the safeguards put in place by default
with Symfony2.  If you were to, for example, include a user's private content
via ESI without a cache key that's unique to them, that content is likely to leak
through to other users.

For documentation, see:

[`Resources/doc/index.md`](https://github.com/jamesisaac/JamesiHttpCacheBundle/blob/master/Resources/doc/index.md)
