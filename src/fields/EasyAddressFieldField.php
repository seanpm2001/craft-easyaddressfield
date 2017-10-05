<?php

namespace studioespresso\easyaddressfield\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use studioespresso\easyaddressfield\assetbundles\easyaddressfield\EasyAddressFieldAsset;
use studioespresso\easyaddressfield\EasyAddressField;
use studioespresso\easyaddressfield\models\EasyAddressFieldModel;
use yii\db\Schema;


class EasyAddressFieldField extends Field implements PreviewableFieldInterface {

	public $geoCode = true;
	public $showCoordinates = false;
	public $fields = array(
		'name'       => false,
		'street'     => true,
		'street2'    => false,
		'postalCode' => true,
		'city'       => true,
		'state'      => false,
		'country'    => true,
	);


	public static function displayName(): string {
		return Craft::t( 'easyaddressfield', 'Easy Address Field' );
	}


	public function getContentColumnType(): string {
		return Schema::TYPE_TEXT;
	}

	/**
	 * @return string
	 * @throws \yii\base\Exception
	 * @throws \Twig_Error_Loader
	 * @throws \RuntimeException
	 */
	public function getSettingsHtml(): string {
		// Render the settings template

		return Craft::$app->getView()->renderTemplate(
			'easyaddressfield/_field/_settings',
			[
				'field' => $this,
			]
		);
	}

	public function rules(): array {


		$addressRules =
			array(
				array(
					array(
						'geoCode'
					),
					'boolean'
				),
				array(
					array(
						'showCoordinates'
					),
					'boolean'
				),
			);


		$rules = parent::rules();
		$rules = array_merge( $rules, $addressRules );

		return $rules;
	}

	/**
	 * @param mixed $value
	 * @param ElementInterface|null $element
	 *
	 * @return mixed|EasyAddressFieldModel
	 */
	public function normalizeValue( $value, ElementInterface $element = null ) {
		if ( is_string( $value ) ) {
			$value = json_decode( $value, true );
		}

		if ( is_array( $value ) && ! empty( array_filter( $value ) ) ) {
			return new EasyAddressFieldModel( $value );
		}

		return null;
	}


	public function getInputHtml( $value, ElementInterface $element = null ): string {
		// Register our asset bundle
		Craft::$app->getView()->registerAssetBundle( EasyAddressFieldAsset::class );

		// Get our id and namespace
		$id           = Craft::$app->getView()->formatInputId( $this->handle );
		$namespacedId = Craft::$app->getView()->namespaceInputId( $id );

		$pluginSettings = EasyAddressField::getInstance()->getSettings();
		$fieldSettings  = $this->getSettings();

		return $this->renderFormFields( $value );
	}

	protected function renderFormFields( EasyAddressFieldModel $value = null ) {
		// Get our id and namespace
		$id           = Craft::$app->getView()->formatInputId( $this->handle );
		$namespacedId = Craft::$app->getView()->namespaceInputId( $id );

		$fieldSettings  = $this->getSettings();
		$pluginSettings = EasyAddressField::getInstance()->getSettings();

		$fieldLabels   = null;
		$addressFields = null;

		return Craft::$app->getView()->renderTemplate(
			'easyaddressfield/_field/_input',
			[
				'name'           => $this->handle,
				'value'          => $value,
				'field'          => $this,
				'id'             => $id,
				'namespacedId'   => $namespacedId,
				'fieldSettings'  => $fieldSettings,
				'pluginSettings' => $pluginSettings,
			]
		);
	}

}