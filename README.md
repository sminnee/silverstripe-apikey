# SilverStripe API Key

[![Build Status](https://travis-ci.org/sminnee/silverstripe-apikey.svg?branch=master)](https://travis-ci.org/sminnee/silverstripe-apikey)
[![codecov](https://codecov.io/gh/sminnee/silverstripe-apikey/branch/master/graph/badge.svg)](https://codecov.io/gh/sminnee/silverstripe-apikey)

This module provides a way of creating an managing API keys within SilverStripe. This can be useful for building RESTful
and other APIs.

## Requirements

 * SilverStripe ^4.0
 * PHP 5.5+

## Installation

```
composer require sminnee/silverstripe-apikey
```

## How it works

 * Extensions the the `SecurityAdmin` provide interfaces for seeing API keys, and generating new ones. API keys are
   allocated member-by-member.
 * A `RequestMiddleware` will look for an API key header (default: `X-API-Key`) and if it is present, authenticate the
   user so that Member::currentUser() will return the corresponding member. This should be configured by non-GraphQL
   requests.
 * A `ApiKeyAuthenticator` should be configured for [GraphQL](https://github.com/silverstripe/silverstripe-graphql)
   request and will return the authenticated member for GraphQL contexts to use, while not applying it to the CMS
   session.

## Regular use

For regular module usage, use the `RequestMiddleware` class. The configuration to apply it is in this module's `apikey.yml`,
but is commented out.

Copy the configuration and add it to your `mysite/_config/apikey.yml` file.

This will protect all of your frontend routes.

## GraphQL

The GraphQL authenticator will work separately from the `RequestMiddleware`. If using this module for GraphQL, you will
probably want to disable the `RequestMiddleware`. If you run both at the same time you will find that:

 * Authentication exceptions are thrown outside of the GraphQL context (i.e. not wrapped in JSON output)
 * Successful requests will register two "times used" each, since it's authenticated in two places

## Limitations

 * You can't limit the rights that the API key has to be more granular than "all rights of the given user".
 * Keys can't be disabled, only deleted
 * No support for storing encrypted ("read-once") keys

## Status

This should be considered experimental for now, and used with care. It has not received a security audit.
