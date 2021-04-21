<?php
/*******
 * @package xbBooks
 * @filesource admin/controlers/characters.php
 * @version 0.8.5 22nd March 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

class XbbooksControllerCharacters extends JControllerAdmin {
    
    public function getModel($name = 'Characters', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);        
        return $model;
    }

    function people() {
    	$this->setRedirect('index.php?option=com_xbpeople&view=characters');
    }
    
}