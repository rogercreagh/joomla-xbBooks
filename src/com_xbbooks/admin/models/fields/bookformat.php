<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/bookformat.php
 * @version 0.6.5d 29th December 2020
  * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

JFormHelper::loadFieldClass('combo');

class JFormFieldBookformat extends JFormFieldCombo {
	
	protected $type = 'Format';
	
	public function getOptions() {
		
		$options = array();
		
		$db = JFactory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT format AS text, format AS value')
		->from('#__xbbooks')
		->where("format<>''")
		->where("format NOT IN ('Hardback','Paperback','Kindle','eBook')")
		->order('format');
		
		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
