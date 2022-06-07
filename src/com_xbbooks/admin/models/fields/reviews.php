<?php
/*******
 * @package xbBooks
 * @filesource admin/models/fields/books.php
 * @version 0.9.8.7 4th June 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldReviews extends JFormFieldList {
    
    protected $type = 'Reviews';
    
    public function getOptions() {
        
        $options = array();
        
        $db = Factory::getDbo();
        $query  = $db->getQuery(true);
        
        $query->select('id As value')
            ->select('CONCAT(b.title," ", b.rating,"&starf; ",a.title) AS text') 
            ->from('#__xbbookreviews AS a')
            ->leftJoin('#__xbbooks AS b ON b.id = a.book_id')
            ->where('a.state = 1')  //published only
            ->order('b.title ASC'); 
        
        $db->setQuery($query);
        $all = $db->loadObjectList();

        $query->clear();
        $query->select('id As value')
        ->select('CONCAT(b.title," ", b.rating,"&starf; ",a.title) AS text')
        ->from('#__xbbookreviews AS a')
        ->leftJoin('#__xbbooks AS b ON b.id = a.book_id')
        ->where('a.state = 1')  //published only
        ->order('b.title ASC')
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
