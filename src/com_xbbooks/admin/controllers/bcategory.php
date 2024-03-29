<?php
/*******
 * @package xbBooks
 * @filesource admin/controlers/bcategory.php
 * @version 0.9.4 14th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksControllerBcategory extends JControllerAdmin {
    
    public function getModel($name = 'Category', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);        
        return $model;
    }
    
    function bcategories() {
    	$this->setRedirect('index.php?option=com_xbbooks&view=bcategories');
    }

}