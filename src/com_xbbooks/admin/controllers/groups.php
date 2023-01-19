<?php
/*******
 * @package xbBooks
 * @filesource admin/controlers/groups.php
 * @version 1.0.3.4 19th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class XbbooksControllerGroups extends JControllerAdmin {
    
    public function getModel($name = 'roups', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);        
        return $model;
    }

    function allgroups() {
    	$this->setRedirect('index.php?option=com_xbpeople&view=groups');
    }

    function groupedit() {
        $ids =  Factory::getApplication()->input->get('cid');
        $id=$ids[0];
        $this->setRedirect('index.php?option=com_xbpeople&task=group.edit&id='.$id);
    }
    
    function groupnew() {
        $this->setRedirect('index.php?option=com_xbpeople&task=group.edit&id=0');
    }
    
    function publish() {
        $jip =  Factory::getApplication()->input;
        $pid =  $jip->get('cid');
        BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_xbpeople/models', 'XbpeopleModel');
        $pmodel = BaseDatabaseModel::getInstance('Group', 'XbpeopleModel');
        $task = ($jip->get('task')=='unpublish') ? 0 : 1;
        $wynik = $pmodel->publish($pid, $task);
        $redirectTo =('index.php?option='.$jip->get('option').'&task=display&view=groups');
        $this->setRedirect($redirectTo );
    }
    
}