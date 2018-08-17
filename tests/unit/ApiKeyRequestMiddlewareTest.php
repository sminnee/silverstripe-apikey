<?php

namespace Sminnee\ApiKey\UnitTests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use Sminnee\ApiKey\MemberApiKey;
use Sminnee\ApiKey\ApiKeyMemberExtension;
use Sminnee\ApiKey\ApiKeyRequestMiddleware;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Security\Security;

class ApiKeyRequestMiddlewareTest extends SapphireTest
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
    protected static $fixture_file = 'ApiKeyRequestMiddlewareTest.yml';

    /**
    * @var array
    */
    protected static $required_extensions = [
        Member::class => [
            ApiKeyMemberExtension::class,
        ],
    ];

    public function setUp()
    {
        parent::setUp();
        $this->member = $this->objFromFixture(Member::class, 'admin');
        $this->key = MemberApiKey::createKey($this->member->ID);
        $this->headerName = Config::inst()->get(ApiKeyRequestMiddlware::class, 'header_name');
    }

    public function testPassthroughProcess()
    {
        // pass through with no login
        $request = new HTTPRequest('GET', Director::absoluteBaseURL() . '/api/v1');
        $middleware = new ApiKeyRequestMiddleware();

        $calls = 0;
        $delegate = function () use (&$calls) {
            $calls++;
        };
        $middleware->process($request, $delegate);
        $this->assertEquals(1, $calls);
    }

    public function testBadProcess()
    {
        Security::getCurrentUser()->logout();
        $calls = 0;
        $delegate = function () use (&$calls) {
            $calls++;
        };
        $middleware = new ApiKeyRequestMiddleware();

        $this->expectException(HTTPResponse_Exception::class);
        $this->expectExceptionMessage(
            'Bad X-API-Key'
        );

        $request = new HTTPRequest('GET', Director::absoluteBaseURL() . '/api/v1');
        $request->addHeader($this->headerName, 'fakey');
        $middleware->process($request, $delegate);
        $this->assertEquals(0, $calls);
    }

    public function testSuccessProcess()
    {
        Security::getCurrentUser()->logout();
        $calls = 0;
        $delegate = function () use (&$calls) {
            $calls++;
        };
        $middleware = new ApiKeyRequestMiddleware();

        $request = new HTTPRequest('GET', Director::absoluteBaseURL() . '/api/v1');
        $request->addHeader($this->headerName, $this->key->ApiKey);
        $middleware->process($request, $delegate);
        $this->assertEquals(1, $calls);
        $loggedInUser = Security::getCurrentUser();
        $this->assertEquals($this->member->ID, $loggedInUser->ID);
    }
}
