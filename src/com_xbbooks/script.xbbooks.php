<?php
/*******
 * @package xbBooks
 * @filesource script.xbbooks.php
 * @version 0.9.4 17th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021,2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
 ******/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

class com_xbbooksInstallerScript 
{
    protected $jminver = '3.9';
    protected $jmaxver = '4.0';
    
    function preflight($type, $parent){
        $jversion = new JVersion();
        $jverthis = $jversion->getShortVersion();
        if ((version_compare($jverthis, $this->jminver,'lt')) || (version_compare($jverthis, $this->jmaxver, 'ge'))) {
            throw new RuntimeException('xbBooks requires Joomla version greater than '.$this->jminver. ' and less than '.$this->jmaxver.'. You have '.$jverthis);
        }
    }
    
    function install($parent) {
    }
    
    function uninstall($parent) {
        $message = 'Uninstalling xbBooks component v.'.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate;
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
        $message = 'Updating xbBooks component to v.'.$parent->get('manifest')->version.' '.$parent->get('manifest')->creationDate;
        $message .= '<br />Visit the <a href="index.php?option=com_xbbooks&view=cpanel" class="btn btn-small btn-info">';
        $message .= 'xbBooks Control Panel</a> page for overview of status.</p>';
        $message .= '<br />For ChangeLog see <a href="http://crosborne.co.uk/xbbooks#changelog" target="_blank">
            www.crosborne.co.uk/xbbooks/changelog</a></p>';
        Factory::getApplication()->enqueueMessage($message,'Info');
    }
    
    function postflight($type, $parent) {
        if ($type=='install') {
            $message = $parent->get('manifest')->name.' ('.$type.') : <br />';
            //create xbbooks image folder
            if (!file_exists(JPATH_ROOT.'/images/xbbooks')) {
                mkdir(JPATH_ROOT.'/images/xbbooks',0775);
                $message .= 'Book images folder created (/images/xbbooks/).<br />';
            } else{
                $message .= '"/images/xbbooks/" already exists.<br />';
            }
            
            // check if cats exist
            $db = Factory::getDBO();
            $query = $db->getQuery(true);
            $query->select('id')->from($db->quoteName('#__categories'))
            ->where($db->quoteName('alias')." = ".$db->quote('uncategorised'))
            ->where($db->quoteName('extension')." = ".$db->quote('com_xbbooks'));
            $db->setQuery($query);
            $uncatok = $db->loadResult();
            $query->clear();
            $query->select('id')->from($db->quoteName('#__categories'))
            ->where($db->quoteName('alias')." = ".$db->quote('imported'))
            ->where($db->quoteName('extension')." = ".$db->quote('com_xbbooks'));
            $db->setQuery($query);
            $impcatok = $db->loadResult();
            
            if (($uncatok >0) && ($impcatok > 0)) {
                $message .= 'Categories "Uncategorised" and "Imported" already exist. ';                
            } else {
                //create default categories using method in the categories model
                $message .= 'Creating xbBooks categories ';
                $category_data['id'] = 0;
                $category_data['parent_id'] = 0;
                $category_data['extension'] = 'com_xbbooks';
                $category_data['published'] = 1;
                $category_data['language'] = '*';
                $category_data['params'] = array('category_layout' => '','image' => '');
                $category_data['metadata'] = array('author' => '','robots' => '');
                
                $basePath = JPATH_ADMINISTRATOR.'/components/com_categories';
                require_once $basePath.'/models/category.php';
                $config  = array('table_path' => $basePath.'/tables');
                $category_model = new CategoriesModelCategory($config);
                if (!$uncatok) {
                    $category_data['title'] = 'Uncategorised';
                    $category_data['alias'] = 'uncategorised';
                    $category_data['description'] = 'Default category for xbBooks items not otherwise assigned';
                    
                    if(!$category_model->save($category_data)){
                        $message .= '<br />[Error creating Uncategorised: '.$category_model->getError().']<br /> ';
                    }else{
                        $message .= '"Uncategorised" (id='. $category_model->getItem()->id.') created ';
                    }                  
                }
                if (!$impcatok) {
                    $category_data['title'] = 'Imported';
                    $category_data['alias'] = 'imported';
                    $category_data['description'] = 'Default category for imported xbBooks items (can be over-ridden on import)';
                    
                    if(!$category_model->save($category_data)){
                        $message .= '<br />[Error creating Imported: '.$category_model->getError().']<br />';
                    }else{
                        //$data = $category_model->getItem();
                        $message .= '"Imported" (id='. $category_model->getItem()->id.') - OK ';
                    }                   
                }                
            }
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
	            www.crosborne.co.uk/xbbooks/doc</a> or use Help button in xbBooks Control Panel</p>';
            echo '<h4>Next steps</h4>';
            if (!$xbpeople) {
                echo '<h4 style="color:red;margin-left:30px;">You must install xbPeople component before you can use xbBooks or any other xbCulture component';
                echo '</h4>';
            } else {
                echo '<p><i>Review &amp; set the options</i>&nbsp;&nbsp;';
                echo '<a href="index.php?option=com_config&view=component&component=com_xbbooks" class="btn btn-small btn-info">xbBooks Options</a></p>';
                echo '<p><i>Check the control panel for an overview</i>&nbsp;&nbsp;';
                echo '<a href="index.php?option=com_xbbooks&view=cpanel" class="btn btn-small btn-success">xbBooks cPanel</a></p>';
                echo '<p><i>Install sample data</i>&nbsp;&nbsp;: ';
                echo 'first set and save option at the top of the <a href="index.php?option=com_config&view=component&component=com_xbbooks#admin">Options</a> Admin tab, then the button will appear in the xbBooks Control Panel toolbar.';
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
}

