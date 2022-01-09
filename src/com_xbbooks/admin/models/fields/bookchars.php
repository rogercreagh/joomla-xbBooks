<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/bookchars.php
 * @version 0.9.6.f 9th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * Provides an object list of characters who are in a book and state=published
 */
class JFormFieldBookchars extends JFormFieldList {
    
    protected $type = 'Characters';
    
    public function getOptions() {
        
    	$params = ComponentHelper::getParams('com_xbbooks');
    	//poss param to show state " (", state, ")"
    	$options = array();
        
        $db = Factory::getDbo();
        $query  = $db->getQuery(true);
        
        $query->select('c.id As value')
	        ->select('c.name AS text')
	        ->from('#__xbcharacters AS c')
	        ->join('LEFT', '#__xbbookcharacter AS bc ON bc.char_id = c.id')
	        ->where('bc.id IS NOT NULL')
	        ->where('state = 1')
	        ->order('text');
        // Get the options.
        $db->setQuery($query);
        $options = $db->loadObjectList();
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
