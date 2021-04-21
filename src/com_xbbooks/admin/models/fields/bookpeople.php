<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/people.php
 * @version 0.6.5d 29th December 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

JFormHelper::loadFieldClass('list');

/**
 * Provides an object list of people who are in a book and state=published
 */
class JFormFieldBookpeople extends JFormFieldList {
    
    protected $type = 'People';
    
    public function getOptions() {
        
    	$params = ComponentHelper::getParams('com_xbbooks');
    	$people_sort = $params->get('people_sort');
    	$select = ($people_sort == 0) ? 'CONCAT(firstname, " ", lastname) AS text' : 'CONCAT(lastname, ", ", firstname ) AS text';
    	//poss param to show state " (", state, ")"
    	$options = array();
        
        $db = Factory::getDbo();
        $query  = $db->getQuery(true);
        
        $query->select('DISTINCT p.id As value')
	        ->select($select)
	        ->from('#__xbpersons AS p')
	        ->join('LEFT', '#__xbbookperson AS bp ON bp.person_id = p.id')
	        ->where('bp.id IS NOT NULL')
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
