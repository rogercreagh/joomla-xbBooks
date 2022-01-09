<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/people.php
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

class JFormFieldPeople extends JFormFieldList {
    
    protected $type = 'People';
    
    /**
     * @desc gets a list of all people with three most recently added at top,
     * then published ones sorted by title, then any unpublished ones at the end
     * {@inheritDoc}
     * @see JFormFieldList::getOptions()
     */
    public function getOptions() {
        
    	$params = ComponentHelper::getParams('com_xbbooks');
    	$people_sort = $params->get('people_sort');
    	$names = ($people_sort == 0) ? 'firstname, " ", lastname' : 'lastname, ", ", firstname ';
    	//poss param to show state " (", state, ")"
    	$options = array();
        
        $db = Factory::getDbo();
        $query  = $db->getQuery(true);
        
        $query->select('id As value')
        ->select('CONCAT('.$names.', IF (state <>1, " (unpub)", "") ) AS text')
	        ->from('#__xbpersons')
	        ->where('state IN (0,1)')
	        ->order('state DESC, text ASC');
        // Get the options.
        $db->setQuery($query);
        $all = $db->loadObjectList();

        $query->clear();
        $query->select('id As value')
        ->select('CONCAT('.$names.') AS text')
        ->from('#__xbpersons')
        ->order('created DESC')
        ->setLimit('3');
        $recent = $db->loadObjectList();
        //add a separator between recent and alpha
        $blank = new stdClass();
        $blank->value = 0;
        $blank->text = '------------';
        $recent[] = $blank;
        
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $recent, $all);
        return $options;
    }
}
