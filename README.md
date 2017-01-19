# SilverStripe API Key

This module provides a way of creating an managing API keys within SilverStripe. This can be useful for building RESTful
and other APIs.

## Requirements

 * SilverStripe ^4.0
 * PHP 5.5+

## How it works

 * Extensions the the `SecurityAdmin` provide interfaces for seeing API keys, and generating new ones. API keys are
   allocated member-by-member.
 * A `RequestFilter` will look for an API key header (default: `X-API-Key`) and if it is present, authenticate the
   user so that Member::currentUser() will return the corresponding member. This should be configured by non-GraphQL
   requests.
 * A `ApiKeyAuthenticator` should be configured for [GraphQL](https://github.com/silverstripe/silverstripe-graphql)
   request and will return the authenticated member for GraphQL contexts to use, while not applying it to the CMS
   session.

## Limitations

 * You can't limit the rights that the API key has to be more granular than "all rights of the given user".
 * Keys can't be disabled, only deleted
 * No support for storing encrypted ("read-once") keys

## Status

This should be considered experimental for now, and used with care. It has not received a security audit.
