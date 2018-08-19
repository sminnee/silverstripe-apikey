<?php

namespace Sminnee\ApiKey\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use Sminnee\ApiKey\MemberApiKey;
use Sminnee\ApiKey\ApiKeyMemberExtension;
use InvalidArgumentException;
use SilverStripe\ORM\Connect\DatabaseException;
use SilverStripe\ORM\FieldType\DBDatetime;

class MemberApiKeyTest extends SapphireTest
{
    /**
     * @var Member
     */
    private $member;

    /**
     * @var string
     */
    protected static $fixture_file = 'MemberApiKeyTest.yml';

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
    }

    public function testCreateKey()
    {
        // invalid key creation
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Please pass a numeric $memberID'
        );
        MemberApiKey::createKey('non numeric');

        // create key for member
        $id = $this->member->ID;

        /** @var MemberApiKey $key */
        $key = MemberApiKey::createKey($id);

        $this->assertInstanceOf(MemberApiKey::class, $key);
        $this->assertGreaterThan(0, strlen($key->ApiKey));
    }

    public function testFindByKey()
    {
        $key = MemberApiKey::createKey($this->member->ID);

        $keyObject = MemberApiKey::findByKey($key->ApiKey);
        $this->assertEquals($key->ID, $keyObject->ID);

        $keyObject = MemberApiKey::findByKey('falsy');
        $this->assertNull($keyObject);

        // should not be able to create a duplicate key
        $keyObject = MemberApiKey::create();
        $keyObject->ApiKey = 'fakey';
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage(
            "Duplicate entry 'fakey'"
        );
        $keyObject->write();
    }

    public function testMarkUsed()
    {
        $key = MemberApiKey::createKey($this->member->ID);
        $date = '2018-08-17 13:56:38';
        DBDatetime::set_mock_now($date);

        $key->markUsed();

        $this->assertEquals($date, $key->LastUsed);
        $this->assertGreaterThan(0, $key->TimesUsed);

        DBDatetime::clear_mock_now();
    }
}
