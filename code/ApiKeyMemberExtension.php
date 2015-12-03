<?php

class ApiKeyMemberExtension extends DataExtension
{

	private static $has_many = [
		'ApiKeys' => 'MemberApiKey',
	];

	public function updateCMSFields(FieldList $fields) {
		$gridConfig = $fields->dataFieldByName('ApiKeys')->getConfig();

		// Simplify view
		$gridConfig->removeComponentsByType('GridFieldAddExistingAutocompleter');
		$gridConfig->removeComponentsByType('GridFieldDetailForm');
		$gridConfig->removeComponentsByType('GridFieldEditButton');

		// Better add key button
		$gridConfig->removeComponentsByType('GridFieldAddNewButton');
		$gridConfig->addComponent(new GridFieldAddApiKeyButton('buttons-before-left'));

		// Replace unlink with a real delete
		$gridConfig->removeComponentsByType('GridFieldDeleteAction');
		$gridConfig->addComponent(new GridFieldDeleteAction());
	}
}
