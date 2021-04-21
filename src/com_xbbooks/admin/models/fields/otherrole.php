<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/otherrole.php
 * @version 0.6.4e 7th December 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

JFormHelper::loadFieldClass('combo');

class JFormFieldOtherrole extends JFormFieldCombo {
	
	protected $type = 'Otherrole';
	
	public function getOptions() {
		
		$options = array();
		
		$db = JFactory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT role_note AS text, role_note AS value')
		->from('#__xbbookperson')
		->where("role = 'other'")
		->order('text');
		
		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
