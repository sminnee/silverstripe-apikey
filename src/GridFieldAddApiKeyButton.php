<?php

namespace Sminnee\ApiKey;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use Sminnee\ApiKey\MemberApiKey;

/**
 * Adds the API key creation button to a GridField
 *
 * @package forms
 * @subpackage fields-gridfield
 */

class GridFieldAddApiKeyButton implements GridField_HTMLProvider, GridField_ActionProvider
{
    /**
     * Fragment to write the button to
     */
    protected $targetFragment;

    /**
     * @param string $targetFragment The HTML fragment to write the button into
     * @param array $exportColumns The columns to include in the export
     */
    public function __construct($targetFragment = "after")
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * Place the export button in a <p> tag below the field
     */
    public function getHTMLFragments($gridField)
    {
        $button = new GridField_FormAction(
            $gridField,
            'addapikey',
            _t('GridFieldAddApiKeyButton.CREATE_API_KEY', 'Create API Key'),
            'addapikey',
            null
        );
        $button->addExtraClass('btn btn-primary font-icon-plus');

        return array(
            $this->targetFragment => $button->Field(),
        );
    }

    public function getActions($gridField)
    {
        return array('addApiKey');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'addapikey') {
            MemberApiKey::createKey($gridField->getForm()->getRecord()->ID);
        }
    }
}
