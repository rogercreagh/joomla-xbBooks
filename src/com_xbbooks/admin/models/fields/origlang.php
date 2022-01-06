<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/origlang.php
 * @version 0.9.6.c 6th January 2022
 * @since v0.6.1
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

JFormHelper::loadFieldClass('combo');

class JFormFieldOriglang extends JFormFieldCombo {
	
	protected $type = 'Origlang';
	
	public function getOptions() {
		
		$options = array();
		
		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT orig_lang AS text, orig_lang AS value')
		->from('#__xbbooks')
		->where("orig_lang<>''")
		->order('orig_lang');
		
		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
