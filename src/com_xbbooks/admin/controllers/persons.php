<?php
/*******
 * @package xbBooks
 * @filesource admin/controlers/persons.php
 * @version 1.0.3.1 7th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksControllerPersons extends JControllerAdmin {
    
    public function getModel($name = 'Persons', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);        
        return $model;
    }

    function allpeople() {
    	$this->setRedirect('index.php?option=com_xbpeople&view=persons');
    }

    function personedit() {
        $ids =  Factory::getApplication()->input->get('cid');
        $id=$ids[0];
        $this->setRedirect('index.php?option=com_xbpeople&task=person.edit&id='.$id);
    }
    
    function personnew() {
        $this->setRedirect('index.php?option=com_xbpeople&task=person.edit&id=0');
    }
    
}