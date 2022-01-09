<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/books.php
 * @version 0.9.6.f 9th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldBooks extends JFormFieldList {
    
    protected $type = 'Books';
    
    public function getOptions() {
        
        $options = array();
        
        $db = Factory::getDbo();
        $query  = $db->getQuery(true);
        
        $query->select('id As value')
            ->select('CONCAT(title, IF (state <>1, " (unpub)", "") ) AS text') 
            ->from('#__xbbooks')
            ->where('state IN (0,1)')  //exclude trashed and archived
            ->order('state DESC, title ASC'); //pub published first and unpublished at end
        
        $db->setQuery($query);
        $all = $db->loadObjectList();

        $query->clear();
        $query->select('id As value')
        ->select('CONCAT(title, " (", state, ")") AS text')
        ->from('#__xbbooks')
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
