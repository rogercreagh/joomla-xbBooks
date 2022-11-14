<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/catsubtree.php
 * @version 0.9.10.2 14th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldCatsubtree extends JFormFieldList {
	
	protected $type = 'Catsubtree';
	
	public function getOptions() {
		
		$params = ComponentHelper::getParams('com_xbbooks');
		$options = array();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : 'com_xbbooks';
		$published = (string) $this->element['published'];
		$language  = (string) $this->element['language'];
		if (!empty($this->element['itemtype'])) {
			$itemtype = (string) $this->element['itemtype'];
			$rootid = $params->get('rootcat_'.$itemtype,0);
			$incroot= $params->get('incroot_'.$itemtype,false);
		} else {
			$rootid = 0;
			$incroot = false;
		}
		
		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		if ($rootid>0) {
			$query->select('*')->from('#__categories')->where('id='.$rootid);
			$db->setQuery($query);
			$rootcat=$db->loadObject();			
		}
		$start = $incroot ? '>=' : '>';
		
		$query->clear();
		$query->select('id AS value, title AS text, level')->from('#__categories')
			->where('extension = '.$db->quote($extension));
		if ($rootid>0) {
			$query->where(' lft'.$start.$rootcat->lft.' AND rgt <='.$rootcat->rgt);			
		}
		if ($published) {
			$query->where('published = 1');
		}
		$query->order('lft');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		foreach ($options as &$item) {
			$adj = $incroot ? 0 : 1;
			$startlevel = $rootid>0 ? $rootcat->level + $adj :1;
			if ($item->level>0) {
				$item->text = str_repeat('- ', $item->level - $startlevel).$item->text;				
			}
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
		
}
	