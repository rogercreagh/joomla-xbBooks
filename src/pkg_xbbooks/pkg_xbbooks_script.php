<?php
/**
 * @package xbBooks-Package
 * @filesource pkg_xbbooks_script.php
 * @version 0.9.8.3 26th May January 2022
 * @desc install, upgrade and uninstall actions
 * @author Roger C-O
 * @copyright (C) Roger Creagh-Osborne, 2019
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

class pkg_xbbooksInstallerScript
{
    protected $jminver = '3.9';
    protected $jmaxver = '4.0';
    
    function preflight($type, $parent)
    {
        $jversion = new Version();
        $jverthis = $jversion->getShortVersion();
        if ((version_compare($jverthis, $this->jminver,'lt')) || (version_compare($jverthis, $this->jmaxver, 'ge'))) {
            throw new RuntimeException('xbBooks requires Joomla version greater than or equal to '.$this->jminver. ' and less than '.$this->jmaxver.'. You have '.$jverthis);
        }
     }
    
    function install($parent)
    {
    }
    
    function uninstall($parent) {
        $db = Factory::getDBO();
     	$db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote('com_xbfilms'));
     	$res = $db->loadResult();    	
     	if ($res) {
     	    $message = 'xbFilms is still installed. If you wish to uninstall xbBooks then just uninstall the component for now.';
     	    //books component could have been uninstalled manually so we'll redirect to xbfilms dashboard as we know that exists
     	    $targ = Uri::base().'index.php?option=com_xbfilms&view=cpanel&err='.urlencode($message);
   	        //ndex.php?option=com_installer&view=manage&filter_search=xb //but this would miss the message
     	    header("Location: ".$targ);
     	    exit();
     	}
        $oldval = Factory::getSession()->set('xbpkg', 'books');

     	echo '<div style="padding: 7px; margin: 0 0 8px; list-style: none; -webkit-border-radius: 4px; -moz-border-radius: 4px;
	border-radius: 4px; background-image: linear-gradient(#ffffff,#efefef); border: solid 1px #ccc;">';
    	echo '<h4>xbBooks Package Uninstalled</h4>';
    	$db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote('com_xbbooks'));
    	if ($db->loadResult()) {
    	    echo '<p>xbBooks component uninstalled.</p>';
    	} else {
    	    echo '<p>xbBooks component appears to have already been uninstalled.</p>';
    	}
    	$db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote('com_xbpeople'));
    	if ($db->loadResult()) {
    	    echo '<p>xbPeople component uninstalled.</p>';
    	} else {
    	    echo '<p>xbPeople component appears to have already been uninstalled.</p>';
    	}
    	echo '</div>';
    }
    
    function update($parent)
    {
    	echo '<div style="padding: 7px; margin: 0 0 8px; list-style: none; -webkit-border-radius: 4px; -moz-border-radius: 4px;
	border-radius: 4px; background-image: linear-gradient(#ffffff,#efefef); border: solid 1px #ccc;">';
    	echo '<h3>xbBooks Package updated to version ' . $parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate . ' with components</h3>';
    	echo '<ul><li>xbBooks v.' . $parent->get('manifest')->xbbooks_version . '</li>';
    	echo '<li>xbPeople v.' . $parent->get('manifest')->xbpeople_version . '</li>';
    	echo '</ul>';
    	echo '<p>For details see <a href="https://crosborne.co.uk/xbbooks/changelog" target="_blank">
            www.crosborne.co.uk/xbbooks/changelog</a></p>';
    	echo '<p class="xbhlt">NB: Please refresh the xbFilms package cache on the Extensions|Manage page or the previous manifest may be used and cause uninstall errors</p>';
    	echo '</div>';
    }
    
    function postflight($type, $parent)
    {
    	if ($type=='install') {
	    	echo '<div style="padding: 7px; margin: 0 0 8px; list-style: none; -webkit-border-radius: 4px; -moz-border-radius: 4px;
		border-radius: 4px; background-image: linear-gradient(#ffffff,#efefef); border: solid 1px #ccc;">';
	    	echo '<h3>xbBooks Package installed</h3>';
	    	echo '<p>Package version '.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate.'<br />';
	    	echo 'Extensions included: </p>';
	    	echo '<ul><li><b>xbBooks '.$parent->get('manifest')->xbbooks_version.'</b>: manage/display books details and reviews</li>';
	    	echo '<li><b>xbPeople '.$parent->get('manifest')->xbpeople_version.'</b>: manage/display people and characters</li>';
	    	echo '</ul>';
	    	echo '<p>For help and information see <a href="https://crosborne.co.uk/xbbooks/doc" target="_blank">
	            www.crosborne.co.uk/xbbooks/doc</a></p>';
	    	echo '<h4>Next steps</h4>';
	    	echo '<p><b>Important</b> Before starting review &amp; set the component options&nbsp;&nbsp;';
	    	echo '<a href="index.php?option=com_config&view=component&component=com_xbbooks" class="btn btn-small btn-info">xbBooks Options</a>';
	    	echo '<br /><i>After saving the options you will exit to the Dashboard for an overview</i>';
	    	echo '</p>';
	    	echo '<p><b>Dashboard</b> <i>The Dashboard view provides an overview of the component status</i>&nbsp;&nbsp;: ';
	    	echo '<a href="index.php?option=com_xbbooks&view=cpanel">xbBooks Dashboard</a> (<i>but save the options first!</i>)';
	    	echo '</p>';
	    	echo '<p><b>Sample Data</b> <i>You can install some sample data</i>&nbsp;&nbsp;: ';
	    	echo 'first check the option to show sample data button on the <a href="index.php?option=com_config&view=component&component=com_xbbooks#admin">Options Admin</a> tab, ';
	    	echo 'then an [Install/Remove Sample Data] button will appear in the xbbooks Dashboard toolbar.';
	    	echo '</p>';
	    	echo '<p><b>Import Data</b> <i>you can import data from CSV or SQL file</i>&nbsp;&nbsp;: ';
	    	echo 'visit the <a href="index.php?option=com_xbbooks&view=importexport#imp">Data Management Import</a> tab.';
	    	echo 'Be sure to read the <a href="https://crosborne.uk/xbbooks/doc#impcsv">documentation</a> first if importing from CSV';
	    	echo '</p>';
	    	echo '</div>';
	    	
	    	$message = $parent->get('manifest')->name .' v.'.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate.' has been installed';
	    	
	    	Factory::getApplication()->enqueueMessage($message, 'message');
    	}
    }
    
}
