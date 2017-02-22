<?php

namespace Sminnee\ApiKey;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\ORM\DataExtension;
use Sminnee\ApiKey\MemberApiKey;

class ApiKeyMemberExtension extends DataExtension
{
    private static $has_many = [
        'ApiKeys' => MemberApiKey::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $grid = $fields->dataFieldByName('ApiKeys');
        if (!$grid) {
            return;
        }

        $gridConfig = $grid->getConfig();

        // Simplify view
        $gridConfig->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
        $gridConfig->removeComponentsByType(GridFieldDetailForm::class);
        $gridConfig->removeComponentsByType(GridFieldEditButton::class);

        // Better add key button
        $gridConfig->removeComponentsByType(GridFieldAddNewButton::class);
        $gridConfig->addComponent(new GridFieldAddApiKeyButton('buttons-before-left'));

        // Replace unlink with a real delete
        $gridConfig->removeComponentsByType(GridFieldDeleteAction::class);
        $gridConfig->addComponent(new GridFieldDeleteAction());
    }
}
