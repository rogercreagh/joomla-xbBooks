<?php
/*******
 * @package xbBooks
 * @filesource script.xbbooks.php
 * @version 0.9.6.a 16th December 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021,2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Table\Table;

class com_xbbooksInstallerScript 
{
    protected $jminver = '3.10';
    protected $jmaxver = '4.0';
    protected $extension = 'com_xbbooks';
    protected $ver = 'v0';
    protected $date = '';
    
    function preflight($type, $parent){
        $jversion = new Version();
        $jverthis = $jversion->getShortVersion();
        if ((version_compare($jverthis, $this->jminver,'lt')) || (version_compare($jverthis, $this->jmaxver, 'ge'))) {
            throw new RuntimeException('xbBooks requires Joomla version greater than '.$this->jminver. ' and less than '.$this->jmaxver.'. You have '.$jverthis);
        }
        $message='';
        if ($type=='update') {
        	$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbbooks/xbbooks.xml'));
        	$this->ver = $componentXML['version'];
        	$this->date = $componentXML['creationDate'];
        	$message = 'Updating xbBooks component from '.$componentXML['version'].' '.$componentXML['creationDate'];
        	$message .= ' to '.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate;
        }
        if ($message!='') { Factory::getApplication()->enqueueMessage($message,'');}
    }
    
    function install($parent) {
    }
    
    function uninstall($parent) {
    	$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbbooks/xbbooks.xml'));
    	$message = 'Uninstalling xbBooks component v.'.$componentXML['version'].' '.$componentXML['creationDate'];
    	Factory::getApplication()->enqueueMessage($message,'Info');
    	
    	$dest='/images/xbbooks';
        if (JFolder::exists(JPATH_ROOT.$dest)) {
            if (JFolder::delete(JPATH_ROOT.$dest)){
                $message = 'Images deleted ok';
                Factory::getApplication()->enqueueMessage($message,'Info');
            } else {
                $message = 'Problem deleting xbBooks images folder "/images/xbbooks" - please check in Media manager';
                Factory::getApplication()->enqueueMessage($message,'Error');
            }
        }
        $message = '<br /><b>NB</b> xbBooks uninstall: People and Characters data tables, and images/xbpeople folder have <b>not</b> been deleted.';
        Factory::getApplication()->enqueueMessage($message,'Info');
    }
    
    function update($parent) {
    	$message = '<br />Visit the <a href="index.php?option=com_xbbooks&view=cpanel" class="btn btn-small btn-info">';
    	$message .= 'xbBooks Dashboard</a> page for overview of status.</p>';
    	$message .= '<br />For ChangeLog see <a href="http://crosborne.co.uk/xbbooks/changelog" target="_blank">
            www.crosborne.co.uk/xbbooks/changelog</a></p>';
    	Factory::getApplication()->enqueueMessage($message,'Message');
    }
    
    function postflight($type, $parent) {
    	$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbpeople/xbpeople.xml'));
    	if ($type=='install') {
    		$message = 'xbBooks '.$componentXML['version'].' '.$componentXML['creationDate'].'<br />';
    		
    		//create xbbooks image folder
            if (!file_exists(JPATH_ROOT.'/images/xbbooks')) {
                mkdir(JPATH_ROOT.'/images/xbbooks',0775);
                $message .= 'Book images folder created (/images/xbbooks/).<br />';
            } else{
                $message .= '"/images/xbbooks/" already exists.<br />';
            }
            
            // create default categories using category table
            $cats = array(
            		array("title"=>"Uncategorised","desc"=>"default fallback category for all xbBooks items"),
            		array("title"=>"Imported","desc"=>"default category for xbBooks imported data"));
            $message .= $this->createCategory($cats);
            
            // we assume people default categories are already installed by xbpeople
            // we assume that indicies for xbpersons and xbcharacter tables have been handled by xbpeople install
            
            Factory::getApplication()->enqueueMessage($message,'Info');
            
            //check if people available
            $xbpeople = true;
            $db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote('com_xbpeople'));
            if (!$db->loadObject()) {
                // we could check those indicies here
                $xbpeople = false;
                $peepmess = '<p>Without xbPeople, xbBooks will not function correctly.';
                $peepmess .= '<br />To install it now copy this url <b> https://www.crosborne.uk/downloads?download=11 </b>, and paste the link into the box on the ';
                $peepmess .= '<a href="index.php?option=com_installer&view=install#url">Install from URL page</a>, ';
                $peepmess .= 'or <a href="https://www.crosborne.uk/downloads?download=11">download here</a> and drag and drop onto the install box on this page.';
                $peepmess .= '</p>';
                echo '<div class="alert alert-error">';
                echo '<h4>Warning - xbPeople Component appears not to be installed</h4>';
                echo $peepmess;
                echo '</div>';
            }
            $oldval = Factory::getSession()->set('xbpeople_ok', $xbpeople);
            
            echo '<div style="padding: 7px; margin: 0 0 8px; list-style: none; -webkit-border-radius: 4px; -moz-border-radius: 4px;
		border-radius: 4px; background-image: linear-gradient(#ffffff,#efefef); border: solid 1px #ccc;">';
            echo '<h3>xbBooks Component installed</h3>';
            echo '<p>version '.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate.'<br />';
            echo '<p>For help and information see <a href="https://crosborne.co.uk/xbbooks/doc" target="_blank">
	            www.crosborne.co.uk/xbbooks/doc</a> or use Help button in xbBooks Dashboard toolbar</p>';
            echo '<h4>Next steps</h4>';
            if (!$xbpeople) {
                echo '<h4 style="color:red;margin-left:30px;">You must install xbPeople component before you can use xbBooks or any other xbCulture component';
                echo '</h4>';
            } else {
                echo '<p><i>Review &amp; set the options</i>&nbsp;&nbsp;';
                echo '<a href="index.php?option=com_config&view=component&component=com_xbbooks" class="btn btn-small btn-info">xbBooks Options</a></p>';
                echo '<p><i>Check the control panel for an overview</i>&nbsp;&nbsp;';
                echo '<a href="index.php?option=com_xbbooks&view=cpanel" class="btn btn-small btn-success">xbBooks Dashboard</a></p>';
                echo '<p><i>Install sample data</i>&nbsp;&nbsp;: ';
                echo 'first set and save option at the top of the <a href="index.php?option=com_config&view=component&component=com_xbbooks#admin">Options</a> Admin tab, then the button will appear in the xbBooks Dashboard toolbar.';
                echo '</p>';
                echo '<p><i>Import Data from CSV or SQL file</i>&nbsp;&nbsp;: ';
                echo 'visit the <a href="index.php?option=com_xbbooks&view=importexport#imp">Data Management</a> Import tab.';
                echo 'Be sure to read the <a href="https://docs.crosborne.uk/books/xbculture/page/import-csv">documentation</a> first if importing from CSV';
                echo '</p>';
            }
            echo '</div>';
            $oldval = Factory::getSession()->set('xbbooks_ok', true);
        }
    }

    public function createCategory($cats) {
    	$message = 'Creating '.$this->extension.' categories. ';
    	foreach ($cats as $cat) {
    		$db = Factory::getDBO();
    		$query = $db->getQuery(true);
    		$query->select('id')->from($db->quoteName('#__categories'))
    		->where($db->quoteName('title')." = ".$db->quote($cat['title']))
    		->where($db->quoteName('extension')." = ".$db->quote('com_xbbooks'));
    		$db->setQuery($query);
    		if ($db->loadResult()>0) {
    			$message .= '"'.$cat['title'].' already exists<br /> ';
    		} else {
    			$category = Table::getInstance('Category');
    			$category->extension = $this->extension;
    			$category->title = $cat['title'];
    			if (array_key_exists('alias', $cat)) { $category->alias = $cat['alias']; }
    			$category->description = $cat['desc'];
    			$category->published = 1;
    			$category->access = 1;
    			$category->params = '{"category_layout":"","image":"","image_alt":""}';
    			$category->metadata = '{"page_title":"","author":"","robots":""}';
    			$category->language = '*';
    			// Set the location in the tree
    			$category->setLocation(1, 'last-child');
    			// Check to make sure our data is valid
    			if ($category->check()) {
    				if ($category->store(true)) {
    					// Build the path for our category
    					$category->rebuildPath($category->id);
    					$message .= $cat['title'].' id:'.$category->id.' created ok. ';
    				} else {
    					throw new Exception(500, $category->getError());
    					//return '';
    				}
    			} else {
    				throw new Exception(500, $category->getError());
    				//return '';
    			}
    		}
    	}
    	return $message;
    }
    
}

