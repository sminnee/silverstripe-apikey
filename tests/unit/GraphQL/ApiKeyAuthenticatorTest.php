<?php

namespace Sminnee\ApiKey\Tests\GraphQL;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use Sminnee\ApiKey\MemberApiKey;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Director;
use Sminnee\ApiKey\GraphQL\ApiKeyAuthenticator;
use SilverStripe\GraphQL\Auth\Handler;
use Sminnee\ApiKey\ApiKeyMemberExtension;
use SilverStripe\Core\Config\Config;
use Sminnee\ApiKey\ApiKeyRequestMiddlware;
use SilverStripe\ORM\ValidationException;

class ApiKeyAuthenticatorTest extends SapphireTest
{
    /**
     * @var Member
     */
    private $member;

    /**
     * @var MemberApiKey
     */
    private $key;

    /**
     * @var string
     */
    private $headerName;

    /**
     * @var string
     */
    protected static $fixture_file = 'ApiKeyAuthenticatorTest.yml';

    /**
    * @var array
    */
    protected static $required_extensions = [
        Member::class => [
            ApiKeyMemberExtension::class,
        ],
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->member = $this->objFromFixture(Member::class, 'admin');
        $this->key = MemberApiKey::createKey($this->member->ID);
        $this->headerName = Config::inst()->get(ApiKeyRequestMiddlware::class, 'header_name');
        Handler::config()->authenticators = [
            [
                'class' => ApiKeyAuthenticator::class,
                'priority' => 30,
            ],
        ];
    }

    public function testNoKey()
    {
        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');
        $authenticator = new ApiKeyAuthenticator();

        $applicable = $authenticator->isApplicable($request);
        $this->assertFalse($applicable);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'API key header not readable - please check system logs.'
        );
        $authenticator->authenticate($request);

        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');
        $request = $request->addHeader($this->headerName, null);
        $authenticator = new ApiKeyAuthenticator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Specified API key is not assigned to any members.'
        );
        $authenticator->authenticate($request);
    }

    public function testInvalidKey()
    {
        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');
        $request = $request->addHeader($this->headerName, 'borkborkbork');
        $authenticator = new ApiKeyAuthenticator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Specified API key was not found.'
        );
        $authenticator->authenticate($request);
    }

    public function testDanglingKey()
    {
        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');
        $request = $request->addHeader($this->headerName, 'fakey');
        $authenticator = new ApiKeyAuthenticator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Specified API key is not assigned to any members.'
        );
        $authenticator->authenticate($request);
    }

    public function testValidKey()
    {
        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');
        $request = $request->addHeader($this->headerName, $this->key->ApiKey);
        $authenticator = new ApiKeyAuthenticator();

        $member = $authenticator->authenticate($request);
        $this->assertEquals('APIuser', $member->FirstName);
    }

    public function testIntegrationUnauthenticated()
    {
        // No key supplied
        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');

        $response = Director::test(
            '/graphql',
            [],
            [],
            'POST',
            '',
            [],
            [],
            $request
        );

        $body = json_decode($response->getBody());
        $errors = $body->errors;
        $this->assertCount(1, $errors);
        $this->assertEquals('Authentication required', $errors[0]->message);
    }

    public function testIntegrationAuthenticated()
    {
        // Successful Authentication but no Authorisation
        $request = new HTTPRequest('POST', Director::absoluteBaseURL() . '/graphql');

        $response = Director::test(
            '/graphql',
            [],
            [],
            'POST',
            '',
            [
                $this->headerName => $this->key->ApiKey,
            ],
            [],
            $request
        );

        $body = json_decode($response->getBody());
        $errors = $body->errors;
        $this->assertCount(1, $errors);
        $this->assertEquals('Not authorised', $errors[0]->message);
    }
}
