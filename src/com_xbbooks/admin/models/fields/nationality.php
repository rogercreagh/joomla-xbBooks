<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/nationality.php
 * @version 0.9.6.c 6th January 2022
 * @since v0.5.7
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

JFormHelper::loadFieldClass('combo');

class JFormFieldNationality extends JFormFieldCombo {
	
	protected $type = 'Nationality';
	
	public function getOptions() {
		
		$options = array();
		
		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT nationality AS text, nationality AS value')
		->from('#__xbpersons')
		->where("nationality<>''")
		->order('nationality');
		
		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
