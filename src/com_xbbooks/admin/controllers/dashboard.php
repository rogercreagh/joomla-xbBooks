<?php
/*******
 * @package xbBooks
 * @filesource admin/controllers/dashboard.php
 * @version 1.0.1.3 5th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class XbbooksControllerDashboard extends JControllerAdmin {

    public function getModel($name = 'Dashboard', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config );
        return $model;
    }
    
    function films() {
        $status = XbcultureHelper::checkComponent('com_xbfilms');
        if ($status == true) {
            $this->setRedirect('index.php?option=com_xbfilms&view=dashboard');
        } elseif ($status === 0) {
            Factory::getApplication()->enqueueMessage('<span class="xbhlt" style="padding:5px 10px;">xbFilms '.Text::_('XBCULTURE_COMP_DISABLED').'</span>', 'warning');
            $this->setRedirect('index.php?option=com_installer&view=manage&filter[search]=xbfilms');
        } else {
            Factory::getApplication()->enqueueMessage('<span class="xbhlt" style="padding:5px 10px;">xbFilms '.Text::_('XBCULTURE_COMP_MISSING').'</span>', 'info');
            $this->setRedirect('index.php?option=com_xbbooks&view=dashboard');
        }
    }
    
    function events() {
        $status = XbcultureHelper::checkComponent('com_xbevents');
        if ($status == true) {
            $this->setRedirect('index.php?option=com_xbevents');
        } elseif ($status === 0) {
            Factory::getApplication()->enqueueMessage('<span class="xbhlt" style="padding:5px 10px;">xbEvents'.Text::_('XBCULTURE_COMP_DISABLED').'</span>', 'warning');
            $this->setRedirect('index.php?option=com_installer&view=manage&filter[search]=xbevents');
        } else {
            Factory::getApplication()->enqueueMessage('<span class="xbhlt" style="padding:5px 10px;">xbEvents '.Text::_('XBCULTURE_COMP_MISSING').'</span>', 'info');
            $this->setRedirect('index.php?option=com_xbbooks&view=dashboard');
        }
    }
        
    function people() {
    	$this->setRedirect('index.php?option=com_xbpeople&view=dashboard');
    }
    
    function sample() {

        $filename = 'xbbooks-sample-data.sql';
        $src = JPATH_ROOT.'/media/com_xbbooks/samples/'.$filename;
        $dest = JPATH_COMPONENT_ADMINISTRATOR ."/uploads/". $filename;
        File::copy($src, $dest);
        
        $bimpcat = XbcultureHelper::getIdFromAlias('#__categories', 'imported', 'com_xbbooks');
        //if ($fimpcat==0) $fimpcat=1;
        $pimpcat = XbcultureHelper::getIdFromAlias('#__categories', 'imported', 'com_xbpeople');
        //if ($pimpcat==0) $pimpcat=1;
        $dummypost = array('setpub'=>1, 
            'impcat'=>XbcultureHelper::createCategory('sample-books','', $bimpcat,'com_xbbooks','Sample book data - anything in this category will be deleted when xbBooks Sample Data is removed'),
            'imppcat'=>XbcultureHelper::createCategory('sample-bookpeople','',$pimpcat,'com_xbpeople','Sample book people data - anything in this category will be deleted when xbBooks Sample Data is removed'),
            'poster_path'=>'/images/xbbooks/samples/books/',
            'portrait_path'=>'/images/xbbooks/samples/people/',
            'reviewer'=>'');              
        $impmodel = $this->getmodel('importexport');
        //TODO move this to model as new function
        $wynik = $impmodel->mergeSql($filename,$dummypost);
        if ($wynik['errs'] == '') {
        	if ($wynik['donecnt'] > 0 ) {
        		$mess='Sample data installed. ';
        		if ($wynik['#__xbbooks']>0) { $mess .= $wynik['#__xbbooks'].' books, ';}
        		if ($wynik['#__xbbookreviews']>0) { $mess .= $wynik['#__xbbookreviews'].' reviews assigned to samples-books category.<br />';}
        		if ($wynik['#__xbpersons']>0) { $mess .= $wynik['#__xbpersons'].' people assigned to samples-bookpeople category.';}
        		if ($wynik['#__xbbookperson']>0) { $mess .= $wynik['#__xbbookperson'].' people-book links created, ';}
        		$msgtype = 'success';
        	} else {
        		$mess = 'Nothing to import, possibly items already exist in other categories. ';
        		$msgtype = 'info';
        	}
        	$mess .= $wynik['mess'];
        	//copy sample images folder to images
        	$src = '/media/com_xbbooks/samples/images/';
        	$dest = '/images/xbbooks/samples';
        	if (Folder::exists(JPATH_ROOT.$dest))
        	{
        		$mess .= '<br />'.Text::sprintf('XBCULTURE_SAMPLE_IMAGES_EXIST', $dest) ;
        		$msgtype = 'info';
        	} else {
        		if (Folder::copy(JPATH_ROOT.$src,JPATH_ROOT.$dest)){
        			$mess .= '<br /> Sample images copied to '.$dest;
        		} else {
        			$mess .= '<br />Warning, problem copying sample images to'.$dest;
        			$msgtype = 'warning';
        		}
        	}
        } else {
        	$mess = $wynik['errs'];
        	$msgtype = 'error';
        }
        Factory::getApplication()->enqueueMessage($mess,$msgtype);
        $this->setRedirect('index.php?option=com_xbbooks&view=dashboard');
    }
    
    function unsample() {
    	$impmodel = $this->getmodel('importexport');
    	$wynik = $impmodel->uninstallSample();
    	$this->setRedirect('index.php?option=com_xbbooks&view=dashboard');
    }
    
}	
