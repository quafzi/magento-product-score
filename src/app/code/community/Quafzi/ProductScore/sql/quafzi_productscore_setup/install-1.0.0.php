<?php
/**
 * Quafzi_ProductScore
 *
 * This file is part of the Quafzi_ProductScore extension.
 * Please do not edit or add to this file if you wish to upgrade it to newer
 * versions in the future.
 *
 * @category   Quafzi_ProductScore
 * @package    Quafzi_ProductScore
 * @author     Thomas Birke <magento@netextreme.de>
 * @copyright  ©2016 by Thomas Birke <magento@netextreme.de>
 * @license    OSL-3.0
 */

/** @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

/**
 * We create three attributes:
 * – one to set score by hand
 * – one to hold calculated scores
 * – one to hold resulting score (which is the one set by hand, using the calculated one as fallback)
 */
function createAttribute($code, $label)
{
	$_attribute_data = array(
		'attribute_code' => $code,
		'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'backend_type' => 'decimal',
		'frontend_input' => 'text', //'boolean',
		'is_unique' => '0',
		'is_required' => '0',
		'apply_to' => array('simple', 'configurable', 'grouped', 'bundle'),
		'is_configurable' => '0',
		'is_searchable' => '0',
		'is_visible_in_advanced_search' => '0',
		'is_comparable' => '0',
		'is_used_for_price_rules' => '0',
		'is_wysiwyg_enabled' => '0',
		'is_html_allowed_on_front' => '1',
		'is_visible_on_front' => '0',
		'used_in_product_listing' => '0',
		'used_for_sort_by' => '1',
		'frontend_label' => $label
	);

	$model = Mage::getModel('catalog/resource_eav_attribute')
        ->addData($_attribute_data)
        ->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
        ->setIsUserDefined(1);

	try {
		$model->save();
    } catch (Exception $e) {
        echo '<p>Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage().'</p>';
    }
}

$this->startSetup();
createAttribute('score_manual', 'Overwriting Product Ranking Score');
createAttribute('score_calculated', 'Calculated Product Ranking Score');
createAttribute('score', 'Product Ranking Score');
$this->endSetup();
