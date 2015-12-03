SilverStripe API Key
====================

This module provides a way of creating an managing API keys within SilverStripe. This can be useful for building RESTful
and other APIs.

How it works
------------

 * Extensions the the `SecurityAdmin` provide interfaces for seeing API keys, and generating new ones. API keys are
   allocated member-by-member.
 * A `RequestFilter` will look for an API key header (default: `X-API-Key`) and if it is present, authenticate the
   user so that Member::currentUser() will return the corresponding member.

Limitations
-----------

 * Doesn't actually exist yet, I've just written the readme.
 * You can't limit the rights that the API key has to be more granular than "all rights of the given user".

Status
------

This should be considered experimental for now, and used with care. It has not received a security audit.
