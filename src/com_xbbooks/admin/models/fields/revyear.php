<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/bookyear.php
 * @version 0.9.5 10th May 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;

JFormHelper::loadFieldClass('list');

class JFormFieldRevyear extends JFormFieldList {
	
	protected $type = 'Revyear';

	public function getOptions() {
		
		$options = array();
		
		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		
		$query->select('DISTINCT YEAR(rev_date) AS text, YEAR(rev_date) AS value')
		->from('#__xbbookreviews')
		->order('rev_date DESC');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}

	
