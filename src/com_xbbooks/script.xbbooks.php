<?php
/*******
 * @package xbBooks
 * @filesource script.xbbooks.php
 * @version 1.0.4.0e 17th February 2023
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
use Joomla\CMS\Component\ComponentHelper;

class com_xbbooksInstallerScript 
{
    protected $jminver = '3.10';
    protected $jmaxver = '4.0';
    protected $extension = 'com_xbbooks';
    protected $ver = 'v0';
    protected $date = '';
    protected $pminver = '1.0.3.14';
    
    function preflight($type, $parent){
        $jversion = new Version();
        $jverthis = $jversion->getShortVersion();
        if ((version_compare($jverthis, $this->jminver,'lt')) || (version_compare($jverthis, $this->jmaxver, 'ge'))) {
            throw new RuntimeException('xbBooks requires Joomla version greater than '.$this->jminver. ' and less than '.$this->jmaxver.'. You have '.$jverthis);
        }
        $message='';
        $xbp = $this->checkXbPeople($this->pminver);
        
        if ($xbp === false ) {
            $message = 'Component xbPeople appears not to be installed. Please install and enable it before installing xbBooks.';
            Factory::getApplication()->enqueueMessage($message);
            throw new RuntimeException('xbPeople not found: install aborted');
        }
        if ($xbp === 0 ) {
            Factory::getApplication()->enqueueMessage('Component xbPeople appears to be disabled. Please enable it and save options before running xbFilms.','alert');
        } elseif (is_array($xbp)) {
            $message = 'xbPeople version '.$xbp['version'].' is out of date. Please update xbPeople to '.$this->pminver.' or higher before installing xbFilms.';
            Factory::getApplication()->enqueueMessage($message);
            throw new RuntimeException('xbPeople version out of date: install aborted' );
        } elseif ($xbp !==1) {
            Factory::getApplication()->enqueueMessage('unknow value checking com_xbpeople');
            throw new RuntimeException();
        }
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
        $app = Factory::getApplication();
        //clear the packageuninstall flag if it is set
        $oldval = Factory::getSession()->clear('xbpkg');
        
        $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbbooks/xbbooks.xml'));
    	$message = 'Uninstalling xbBooks component v.'.$componentXML['version'].' '.$componentXML['creationDate'];
    	//are we also clearing data?
    	$savedata = ComponentHelper::getParams('com_xbbooks')->get('savedata',0);
    	if ($savedata == 0) {
    	    if ($this->uninstalldata()) {
    	        $message .= ' ... xbBooks data tables deleted';
    	    }
    	    $dest='/images/xbbooks';
    	    if (JFolder::exists(JPATH_ROOT.$dest)) {
    	        if (JFolder::delete(JPATH_ROOT.$dest)){
    	            $message .= ' ... images/xbbooks folder deleted';
    	        } else {
    	            $err = 'Problem deleting xbBooks images folder "/images/xbbooks" - please check in Media manager';
    	            $app->enqueueMessage($err,'Error');
    	        }
    	    }
    	} else {
    	    $message .= ' xbBooks data tables and images folder have <b>NOT</b> been deleted. CATEGORIES may be recovered on re-install, but TAG links will be lost although tags have not been deleted.';
    	    // allow categories to be recovered with same id
    	    $db = Factory::getDbo();
    	    $db->setQuery(
    	        $db->getQuery(true)
    	        ->update('#__categories')
    	        ->set('extension='.$db->q('!com_xbbooks!'))
    	        ->where('extension='.$db->q('com_xbbooks'))
    	        )
    	        ->execute();
    	        $cnt = $db->getAffectedRows();
    	        
    	        if ($cnt>0) {
    	            $message .= '<br />'.$cnt.' xbBooks category extensions renamed as "<b>!</b>com_xbbooks<b>!</b>". They will be recovered on reinstall with original ids.';
    	        }
    	}
    	$app->enqueueMessage($message,'Info');
    }
    
    function update($parent) {
    	$message = '<br />Visit the <a href="index.php?option=com_xbbooks&view=dashboard" class="btn btn-small btn-info">';
    	$message .= 'xbBooks Dashboard</a> page for overview of status.</p>';
    	$message .= '<br />For ChangeLog see <a href="http://crosborne.co.uk/xbbooks/changelog" target="_blank">
            www.crosborne.co.uk/xbbooks/changelog</a></p>';
    	Factory::getApplication()->enqueueMessage($message,'Message');
    	//for site files preceed with / for admin files leave the / off, for media files tba
    	$delfiles = '';
    	$delfiles .= 'models/fields/allpeople.php,models/fields/bookchars.php,models/fields/bookpeople.php,models/fields/bookyear.php';
        $delfiles .= ',models/fields/catsubtree.php,models/fields/characters.php,models/fields/nationality.php';
        $delfiles .= ',models/fields/natlist.php,models/fields/people.php,models/fields/revformat.php';
        $delfiles .= ',models/fields/revyear.php,models/forms/booklist.xml,models/forms/peoplelist.xml';
        $delfiles .= ',controllers/character.php,controllers/person.php,models/character.php,models/person.php';
        $delfiles .= ',models/forms/character.xml,models/forms/person.xml,tables/character.php,tables/person.php';
        $delfiles .= ',views/character,views/person,views/review/tmpl/view.php,views/books/tmpl/modal.php';
        $delfiles .= ',views/characters/tmpl/default_batch_body.php,views/characters/tmpl/default_batch_footer.php';
        $delfiles .= ',views/groups/tmpl/default_batch_body.php,views/groups/tmpl/default_batch_footer.php';
        $delfiles .= ',views/persons/tmpl/default_batch_body.php,views/persons/tmpl/default_batch_footer.php';
        $delfiles .= ',/views/booklist/tmpl/onecol.php,/views/booklist/tmpl/onecol.xml';
        $delfiles .= ',models/fields/otherrole.php,models/fields/rating.php';
        $delfiles .= ',controllers/group.php';
        $delfiles = explode(',',$delfiles);
    	$cnt = 0; $dcnt=0;
    	$ecnt = 0;
    	$message = 'Deleting Redundant Files in '.JPATH_ROOT.'/[administrator/]components/com_xbbooks/ <br />';
    	foreach ($delfiles as $f) {
    	    if (substr($f,0,1)=='/') {
    	        $name = JPATH_ROOT.'/components/com_xbbooks'.$f;
    	    } else {
    	        $name = JPATH_ADMINISTRATOR.'/components/com_xbbooks/'.$f;
    	    }
    	    
    	    if (file_exists($name)) {
    	        if (is_dir($name)) {
    	            if ($this->rrmdir($name)) { 
    	               $dcnt ++;
    	               $message .= 'RMDIR '.$f.'<br />';
    	            }
    	        } else {
        	        if (unlink($name)) {
        	            $message .= 'DEL '.$f.'<br />';
        	            $cnt ++;
        	        } else {
        	            $message .= 'DELETE FAILED: '.$f.'<br />';
        	            $ecnt ++;
        	        }
    	        }
        	} else {
//        	    $message .= 'FILE NOT FOUND: '.$f.'<br />';
        	}
    	}
	    if (($cnt+$ecnt+$dcnt)>0) {
	        $message .= $cnt.' files, '.$dcnt.' folders cleared';
	        $mtype = ($ecnt>0) ? 'Warning' : 'Message';
	        Factory::getApplication()->enqueueMessage($message, $mtype);
	    }
    }
    
    function postflight($type, $parent) {
    	if ($type=='install') {
    	    $app = Factory::getApplication();
    	    $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_xbbooks/xbbooks.xml'));
    		$message = '<b>xbBooks '.$componentXML['version'].' '.$componentXML['creationDate'].'</b><br />';
    		
    		//create xbbooks image folder
            if (!file_exists(JPATH_ROOT.'/images/xbbooks')) {
                mkdir(JPATH_ROOT.'/images/xbbooks',0775);
                $message .= 'Book images folder created (/images/xbbooks/).<br />';
            } else{
                $message .= '"/images/xbbooks/" already exists.<br />';
            }
            
            // Recover categories if they exist assigned to extension !com_xbbooks!
            $db = Factory::getDbo();
            $qry = $db->getQuery(true);
            $qry->update('#__categories')
            ->set('extension='.$db->q('com_xbbooks'))
            ->where('extension='.$db->q('!com_xbbooks!'));
            $db->setQuery($qry);
            try {
                $db->execute();
                $cnt = $db->getAffectedRows();
            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(),'Error');
            }
            $message .= $cnt.' existing xbBooks categories restored. ';
            // create default categories using category table
            $cats = array(
            		array("title"=>"Uncategorised","desc"=>"default fallback category for all xbBooks items"),
            		array("title"=>"Imported","desc"=>"default category for xbBooks imported data"),
                    array("title"=>"Books","desc"=>"default parent category for Books"),
                    array("title"=>"Book Reviews","desc"=>"default parent category for Book Reviews"));
            $message .= $this->createCategory($cats);
            $app->enqueueMessage($message,'Info');
            
            // we assume people default categories are already installed by xbpeople
            // we assume that indicies for xbpersons and xbcharacter tables have been handled by xbpeople install
            //set up indicies for books and bookreviews tables - can't be done in install.sql as they may already exists
            //mysql doesn't support create index if not exists.
            $message = 'Checking indicies... ';
            
            $prefix = $app->get('dbprefix');
            $querystr = 'ALTER TABLE '.$prefix.'xbbooks ADD INDEX bookaliasindex (alias)';
            $err=false;
            try {
                $db->setQuery($querystr);
                $db->execute();
            } catch (Exception $e) {
                if($e->getCode() == 1061) {
                    $message .= '- book alias index already exists. ';
                } else {
                    $message .= '[ERROR creating bookaliasindex: '.$e->getCode().' '.$e->getMessage().']';
                    $app->enqueueMessage($message, 'Error');
                    $message = 'Checking indicies... ';
                    $err = true;
                }
            }
            if (!$err) {
                $message .= '- book alias index created. ';
            }
            $querystr = 'ALTER TABLE '.$prefix.'xbbookreviews ADD INDEX reviewaliasindex (alias)';
            $err=false;
            try {
                $db->setQuery($querystr);
                $db->execute();
            } catch (Exception $e) {
                if($e->getCode() == 1061) {
                    $message .= '- bookreviews alias index already exists';
                } else {
                    $message .= '<br />[ERROR creating reviewaliasindex: '.$e->getCode().' '.$e->getMessage().']<br />';
                    $app->enqueueMessage($message, 'Error');
                    $message = '';
                    $err = true;
                }
            }
            if (!$err) {
                $message .= '- bookreviews alias index created.';
            }
            
            $app->enqueueMessage($message,'Info');
            
            //check if people available
            $xbpeople = true;
            $db = Factory::getDbo();
            $db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote('com_xbpeople'));
            if (!$db->loadObject()) {
                // we could check those indicies here
                $xbpeople = false;
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
                echo '<p><b>Important</b> Before starting review &amp; set the component options&nbsp;&nbsp;';
                echo '<a href="index.php?option=com_config&view=component&component=com_xbbooks" class="btn btn-small btn-info">xbBooks Options</a>';
                echo '<br /><i>After saving the options you will exit to the Dashboard for an overview</i>';
                echo '</p>';
                echo '<p><b>Dashboard</b> <i>The Dashboard view provides an overview of the component status</i>&nbsp;&nbsp;: ';
                echo '<a href="index.php?option=com_xbbooks&view=dashboard">xbBooks Dashboard</a> (<i>but save the options first!</i>)';
                echo '</p>';
                echo '<p><b>Sample Data</b> <i>You can install some sample data</i>&nbsp;&nbsp;: ';
                echo 'first check the option to show sample data button on the <a href="index.php?option=com_config&view=component&component=com_xbbooks#admin">Options Admin</a> tab, ';
                echo 'then an [Install/Remove Sample Data] button will appear in the xbbooks Dashboard toolbar.';
                echo '</p>';
                echo '<p><b>Import Data</b> <i>you can import data from CSV or SQL file</i>&nbsp;&nbsp;: ';
                echo 'visit the <a href="index.php?option=com_xbbooks&view=importexport#imp">Data Management Import</a> tab.';
                echo 'Be sure to read the <a href="https://crosborne.uk/xbbooks/doc#impcsv">documentation</a> first if importing from CSV';
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
    
    protected function uninstalldata() {
        $message = '';
        $db = Factory::getDBO();
        $db->setQuery('DROP TABLE IF EXISTS `#__xbbooks`, `#__xbbookreviews`, `#__xbbookperson`, `#__xbbookcharacter`');
        $res = $db->execute();
        if ($res === false) {
            $message = 'Error deleting xbBook tables, please check manually';
            Factory::getApplication()->enqueueMessage($message,'Error');
            return false;
        }
        return true;
    }

    function Xrrmdir($src) {
        return true;
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    $this->rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
        return true;
    }
    
    protected function rrmdir($dir) {       
        if (is_dir($dir)) {           
            $objects = scandir($dir);           
            foreach ($objects as $object) {                
                if ($object != "." && $object != "..") {                    
                    if (filetype($dir."/".$object) == "dir") {
                        $this->rrmdir($dir."/".$object); 
                    } else {
                        unlink($dir."/".$object);                    
                    }
                }               
            }            
            reset($objects);            
            rmdir($dir); 
            return true;
        }        
        return false;
    }

    protected static function checkXbPeople(string $minver = '') {
        $db = Factory::getDBO();
        $qry = $db->getQuery(true);
        $qry->select('enabled, manifest_cache')
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('element').' = '.$db->quote('com_xbpeople'));
        $db->setQuery($qry);
        $res = $db->loadAssoc();
        if (is_null($res)) {
            return false;
        } elseif($res['enabled']==0) {
            return 0;
        } else {
            if ($minver != '') {
                $manifest = json_decode($res['manifest_cache'],true);
                if (version_compare($minver, $manifest['version']) == 1) {
                    return $manifest;
                }
            }
        }
        return 1;
    }
    
    
}

