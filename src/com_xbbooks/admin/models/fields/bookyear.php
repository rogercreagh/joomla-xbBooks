<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/revyear.php
 * @version 0.9.5 10th May 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

JFormHelper::loadFieldClass('list');

class JFormFieldBookyear extends JFormFieldList {
	
	protected $type = 'Bookyear';

	public function getOptions() {
		
		$options = array();
		
		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT pubyear AS text, pubyear AS value')
		->from('#__xbbooks')
		->order('pubyear DESC');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}

	
