<?php
/*******
 * @package xbBooks
 * @filesource admin/controlers/tag.php
 * @version 0.6.2a 17th November 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class XbbooksControllerTag extends JControllerAdmin {
    
    public function getModel($name = 'Tag', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);        
        return $model;
    }
    
    function tags() {
    	$this->setRedirect('index.php?option=com_xbbooks&view=tags');
    }

    function tagedit() {
    	$id =  Factory::getApplication()->input->get('tid');
    	$this->setRedirect('index.php?option=com_tags&task=tag.edit&id='.$id);
    }
    
}