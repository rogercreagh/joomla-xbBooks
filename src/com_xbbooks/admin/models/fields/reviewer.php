<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/reviewer.php
 * @version 0.6.1g 15th November 2020
 * @since v0.6.1
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

JFormHelper::loadFieldClass('combo');

class JFormFieldReviewer extends JFormFieldCombo {
	
	protected $type = 'Reviewer';
	
	public function getOptions() {
		
		$options = array();
		
		$db = JFactory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT reviewer AS text, reviewer AS value')
		->from('#__xbbookreviews')
		->where("reviewer<>''")
		->order('reviewer');
		
		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
