<?php

namespace Sminnee\ApiKey;

use InvalidArgumentException;
use LogicException;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\ORM\FieldType\DBDatetime;

class MemberApiKey extends DataObject
{
    private static $table_name = 'MemberApiKey';

    private static $db = [
        'ApiKey' => 'Varchar',
        'LastUsed' => 'DBDatetime',
        'TimesUsed' => 'Int',
    ];

    private static $indexes = [
        'ApiKeyIdx' => ['type' => 'unique', 'columns' => ['ApiKey'] ],
    ];

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $summary_fields = [
        'ApiKey',
        'LastUsed',
        'TimesUsed',
    ];

    /**
     * Defines the length of randomly-generated keys
     */
    private static $key_length = 48;

    /**
     * MemberApiKey factory. Writes to the database.
     *
     * @param int $memberID The member to create a key for
     * @return MemberApiKey
     */
    public static function createKey($memberID)
    {
        // Basic argument validation
        if (!$memberID || !is_numeric($memberID)) {
            throw new InvalidArgumentException('Please pass a numeric $memberID');
        }

        // Find a unique key
        $key = self::randKey();
        while (MemberApiKey::get()->filter(['ApiKey' => $key])->count() > 0) {
            $key = self::randKey();
        }

        // Construct record
        $obj = new MemberApiKey();
        $obj->MemberID = $memberID;
        $obj->ApiKey = $key;
        $obj->write();

        return $obj;
    }

    /**
     * Find the relevant MemberApiKey object for the given key
     */
    public static function findByKey($key)
    {
        $matches = MemberApiKey::get()->filter(['ApiKey' => $key]);
        switch ($matches->count()) {
            case 1:
                return $matches->first();

            case 0:
                return null;

            default:
                throw new LogicException("Multiple MemberApiKey records for '$key' - database corrupt!");
        }
    }

    /**
     * Mark the given key as used.
     * Keeps usage stats up-to-date
     */
    public function markUsed()
    {
        $this->LastUsed = DBDatetime::now();
        $this->TimesUsed++;
        $this->write();
    }

    /**
     * Helper function to generate a random key
     */
    protected static function randKey()
    {
        $key = '';
        $src = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $keyLength = Config::inst()->get(self::class, 'key_length');

        for ($i = 0; $i < $keyLength; $i++) {
            $key .= $src[rand(0, strlen($src)-1)];
        }

        return $key;
    }
}
