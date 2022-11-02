<?php
/*******
 * @package xbBooks
 * @filesource admin/models/importexport.php
 * @version 0.9.9.8 21st October 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

class XbbooksModelImportexport extends JModelAdmin {
	
	//protected $user;
	protected $xbfilmsStatus;
	
	public function __construct() {
		//$user = Factory::getUser();
		$this->xbfilmsStatus = Factory::getSession()->get('com_xbfilms',false);
		parent::__construct();
	}

	public function getForm($data = array(), $loadData = true) {
        $form = $this->loadForm('com_xbbooks.importexport', 'importexport', 
            array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
                return false;
        }
        return $form;
    }

    public function setExportInfo($export) {
        $session = Factory::getSession();
        $session->set('export', $export, 'Xbbooks');
    }
        
    public function getExportExt() {
        $session = Factory::getSession();
        $export=$session->get('export', array(), 'Xbbooks');
        switch ($export['exptype']){
    	    case 1:
    	    case 2:
    			$ext=".sql";
                break;
    		case 3:
    		case 4:
    		case 5:
    			$ext=".csv";
    		    break;
    	}
    	return $ext;
    }

    public function getExportTable() {
        $session = Factory::getSession();
        $export=$session->get('export', array(), 'Xbbooks');
        if ($export['exptype'] == 1) {
            return 'full';
        }
        $expcat = '';
        if (($export['exptables']=='xbpersons') || ($export['exptables']=='xbcharacters')) {
        	$expcat = ($export['exppcat'] >0 )? '-'.XbbooksHelper::getCat($export['exppcat'])->title: '';
        	
        } elseif (($export['exptables']=='xbbooks') || ($export['exptables']=='xbbookreviews')) {
        	$expcat = ($export['expcat'] >0 )? '-'.XbbooksHelper::getCat($export['expcat'])->title: '';
        }
        return $export['exptables'].$expcat;
    }
 
    
    
    public function getExport() {
		$session = Factory::getSession();
        $export=$session->get('export', array(), 'Xbbooks');
        $xmldata = Installer::parseXMLInstallFile(JPATH_COMPONENT_ADMINISTRATOR .'/xbbooks.xml');
        $headjson  = new stdClass();
        $headjson->compname = $xmldata['name'];
        $headjson->compversion = $xmldata['version'];
        $headjson->exporton = date('Y-m-d H:i:s');
        $config = Factory::getConfig();
        $headjson->sitename = $config->get( 'sitename' );;
        $headjson->siteurl = Uri::root();
        $version     = new Version;
        $versionText = $version->getShortVersion();
        $headjson->jversion = 'Joomla v'.$versionText;
        switch ($export['exptype']){
			case 1: //full sql export
			    $headjson->exptype = 'full';
			    echo '#'.json_encode($headjson);	
			    //order of table export is important here
				$tables= array('#__categories', '#__xbbooks', '#__xbpersons', '#__xbbookreviews', '#__xbbookperson'); 
				foreach ($tables as $table){
					$file=$this->datadumpSql($table);
				}
			    break;
			case 2: //single sql table export
			    $table = '#__'.$export['exptables'];
			    $headjson->exptype = 'table:'.$table;
			    echo '#'.json_encode($headjson);
			    $file=$this->datadumpSql($table, $export['expcat'],$export['exppcat']);
			    break;
			case 4: //csv table
			    $table = '#__'.$export['exptables'];
			    $headjson->exptype = $table;
			    //echo '#'.json_encode($headjson).'\n\n';
			    $file=$this->datadumpCsv($table, $export['expcat'],$export['exppcat'],$headjson);
			    break;
			default:
			    break;
		}
		return $file;
	}

	private function datadumpSql ($table, $expcat='0',$exppcat='0') {
        $result = "\n\n";
        $resrt = '';
        $reslt = '';
		$db = $this->getDbo();
        $query = $db->getQuery(true);
		if ($table === '#__categories') {
		    //only get our own categories
            $query->select('a.alias, a.extension, a.title, a.note, a.description,a.language,a.published,a.params,
                a.metadata,a.metakey,a.metadesc');
            if ($expcat>0) {
            	$query->where('a.id = '. $db->quote($expcat));
            } else {
            	$query->where('a.extension IN ('.$db->quote('com_xbbooks').','.$db->quote('com_xbpeople').')');
            }
		} else {
            $query->select('DISTINCT a.*'); 
		}
		$query->from($db->quoteName($table).' AS a');
		// add where clauses to filter by category
		// must use if === not switch for string comparisons
		if ($table === '#__xbbooks') {
		    // books are simple - just need to match the catid if supplied
			if ($expcat>0) { $query->where('a.catid = '. $db->quote($expcat));}		    
		} elseif ($table === '#__xbbookreviews') {
			if ($expcat>0) { 
				// only get reviews of books in the selected category
				$query->join('LEFT','#__xbbooks AS b ON b.id = a.book_id');
				$query->where('b.catid = '. $db->quote($expcat));					
			}		    
		} elseif ($table === '#__xbpersons') {
			//only get book people
			$query->join('RIGHT', '#__xbbookperson AS bp ON bp.person_id = a.id');
			if ($expcat>0) { 
				// only get people linked to books in selected category
				$query->join('LEFT', '#__xbbooks AS b ON b.id = bp.book_id');
				$query->where('b.catid = '. $db->quote($expcat));
			}
			//otherwise get all people attached to books
		} elseif ($table === '#__xbcharacters') {
			//only get book chars
			$query->join('RIGHT', '#__xbbookcharacter AS bc ON bc.char_id = a.id');
			if ($expcat>0) {
				// only get chars linked to books in selected category
				$query->join('LEFT', '#__xbbooks AS b ON b.id = bc.book_id');
				$query->where('b.catid = '. $db->quote($expcat));
			}
			//otherwise get all chars attached to books			
		} elseif ($table === '#__xbbookperson') {
			//we'll filter on book has catid
			if ($expcat>0) {
				$query->join('LEFT','#__xbbooks AS b ON b.id = a.book_id');
				$query->where('b.catid = '. $db->quote($expcat));
			}
		} elseif ($table === '#__xbbookcharacter') {
			//we'll filter on book has catid
			if ($expcat>0) {
				$query->join('LEFT','#__xbbooks AS b ON b.id = a.book_id');
				$query->where('b.catid = '. $db->quote($expcat));
			}
		}
	
		$db->setQuery($query);
		$rows = $db->loadAssocList();
        foreach($rows as $row){
        	//no need to strip newlines in fields for sql       	
        	//append an alias for linked category and book ids
        	$catalias = '';
        	if (key_exists('catid', $row)) {
        		$catalias = $this->getItemAlias('#__categories',(int)$row['catid']);
        	}
        	if (($table === '#__xbbooks') || ($table === '#__xbpersons') || ($table === '#__xbcharacters')) {
    			$row['catalias'] = $catalias;
        	} elseif ($table === '#__xbbookreviews') {
    			$row['catalias'] = $catalias;
    			$row['bookalias'] = $this->getItemAlias('#__xbbooks',$row['book_id']);
        	} elseif ($table === '#__xbbookperson') {
        		$row['bookalias'] = $this->getItemAlias('#__xbbooks',$row['book_id']);
        		$row['personalias'] = $this->getItemAlias('#__xbpersons',$row['person_id']);
        	}elseif ($table === '#__xbbookcharacter') {
        		$row['bookalias'] = $this->getItemAlias('#__xbbooks',$row['book_id']);
        		$row['characteralias'] = $this->getItemAlias('#__xbcharacters',$row['char_id']);
        	}
        	//TODO set created_by_alias to username if not set
        	//unset rows we don't want to export - in particular ids which will be instance specific
        	unset($row['id']);
        	unset ($row['asset_id']);
        	unset ($row['checked_out']);
        	unset ($row['checked_out_time']);
        	unset ($row['modified']);
        	unset ($row['modified_by']);
        	unset ($row['created_by']); //we are keeping created datetime and created_by_alias (if set)
        	unset ($row['catid']);
        	unset ($row['book_id']);
        	unset ($row['person_id']);
        	unset ($row['char_id']);
        	
        	$reslt .= "INSERT INTO ".$table." (";
            $resrt .= ") VALUES ('";
            foreach($row as $key=>$value) {
                $reslt .= $key.",";
                $resrt .= $this->_db->escape($value)."','";
            }
        	$reslt = substr($reslt,0,-1);
        	$resrt = substr($resrt,0,-2);
            $resrt .= ");\n";
	        $result .= ($reslt.$resrt);
        	$reslt ='';
            $resrt ='';
        }
        echo $result . "\n\n";
		return;
    }
        
    public function uninstallSample() {
    	//this will remove everything that is in the samples category including purging any bookperson links
    	//TODO language strings in messages
    	$deletecnts = array(
    			'#__xbbooks'=>0,'#__xbpersons'=>0,'#__xbbookreviews'=>0,'#__xbbookperson'=>0,
    			'#__xbcharacters'=>0, '#__xbbookcharacters'=>0,
    			'#__categories'=>0, 'ignored'=>0, 'donecnt'=>0, 'skipcnt'=>0, 'errs'=>'', 'mess'=> ''
    	);
    	$messtype = 'success';
    	$db = Factory::getDbo();
    	//get the id of the samples category
    	$scatid = XbcultureHelper::getIdFromAlias('#__categories','sample-books','com_xbbooks');
    	$pscatid = XbcultureHelper::getIdFromAlias('#__categories','sample-bookpeople','com_xbpeople');
    	if ($scatid==0) {
    		$deletecnts['mess']='No sample-films category found; nothing deleted';
    		Factory::getApplication()->enqueueMessage($deletecnts['mess'],'information');
     	}
    	if ($pscatid==0) {
    		$deletecnts['mess']='No sample-bookpeople category found; nothing deleted';
    		Factory::getApplication()->enqueueMessage($deletecnts['mess'],'information');
    	}
    	if (($scatid==0) && ($pscatid==0)) {
    		return false;
    	}
    	//start with the links where either side is in samples
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbbookperson'))
    		->where($db->quoteName('book_id').' IN (SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__xbbooks').' WHERE '.$db->quoteName('catid').' = '.$db->quote($scatid).')');
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbbookperson'] += $db->getAffectedRows();
    	$query->clear();
    	
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbbookperson'))
    		->where($db->quoteName('person_id').' IN (SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__xbpersons').' WHERE '.$db->quoteName('catid').' = '.$db->quote($pscatid).')');
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbbookperson'] += $db->getAffectedRows();
    	$query->clear();
    	
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbbookcharacter'))
    	->where($db->quoteName('book_id').' IN (SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__xbbooks').' WHERE '.$db->quoteName('catid').' = '.$db->quote($pscatid).')');
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbbookcharacter'] += $db->getAffectedRows();
    	$query->clear();
    	
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbbookcharacter'))
    	->where($db->quoteName('char_id').' IN (SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__xbcharacters').' WHERE '.$db->quoteName('catid').' = '.$db->quote($scatid).')');
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbbookcharacter'] += $db->getAffectedRows();
    	$query->clear();
    	
    	//now the main tables
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbbookreviews'))->where($db->quoteName('catid').' = '.$db->quote($scatid));
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbbookreviews'] += $db->getAffectedRows();
    	$query->clear();
    	
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbpersons'))->where($db->quoteName('catid').' = '.$db->quote($pscatid));
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbpersons'] += $db->getAffectedRows();
    	$query->clear();
    	
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbcharacters'))->where($db->quoteName('catid').' = '.$db->quote($pscatid));
    	$db->setQuery($query);
    	$db->execute();
    	$deletecnts['#__xbcharacters'] += $db->getAffectedRows();
    	$query->clear();
    	
    	$query = $db->getQuery(true);
    	$query->delete($db->quoteName('#__xbbooks'))->where($db->quoteName('catid').' = '.$db->quote($scatid));
    	$db->setQuery($query);
    	$db->execute(); 
    	$deletecnts['#__xbbooks'] += $db->getAffectedRows();
    	$query->clear();
    	
    	//now kill the samples and sample-bookpeople category
    	$query = $db->getQuery(true);
    	$query->delete('#__categories')
    	->where($db->quoteName('id').' = '.$db->quote($scatid).' OR '.$db->quoteName('id').' = '.$db->quote($pscatid));
    	$db->setQuery($query);
    	$db->execute();
    	if ($db->getAffectedRows()>0) {
    		$deletecnts['mess'] .= 'sample-books and sample-bookpeople cataegories and all items cleared. ';
    	} else {
    		$deletecnts['mess'] .= 'Error deleteing Samples categories. ';
    		$messtype = 'warning';
    	}
    	$deletecnts['mess'] .= $deletecnts['#__xbbooks'].' books, ';
    	$deletecnts['mess'] .= $deletecnts['#__xbbookreviews'].' reviews, ';
    	$deletecnts['mess'] .= $deletecnts['#__xbpersons'].' people, ';
    	$deletecnts['mess'] .= $deletecnts['#__xbbookperson'].' people links, ';
    	$deletecnts['mess'] .= $deletecnts['#__xbcharacters'].' character, ';
    	$deletecnts['mess'] .= $deletecnts['#__xbbookcharacter'].' character links, ';
    	
    	$dest = '/images/xbbooks/samples';
    	if (JFolder::exists(JPATH_ROOT.$dest)) {
    		if (JFolder::delete(JPATH_ROOT.$dest)){
    			$deletecnts['mess'] .= 'Sample images deleted ok';
    		} else {
    			$deletecnts['mess'] .= 'Problem deleting sample images';
    			$messtype = 'warning';
    		}
    	}
    	Factory::getApplication()->enqueueMessage($deletecnts['mess'], $messtype);    	
    	
    	return true;
    }
    
/**
     * @desc mergeSql() - runs insert queries testing each item alias to see if it already exists first
     * @param  $filename - name of file to parse already in uploads folder
     * @param  $catid - if set all items will be assigned to this category, 
     * 							otherwise (catid=0) any catalias found will be used to create a cat 
     * 							and if no catalias will be assigned to imported
     * @param  $setpub - >2  existing state info will be preserved defaulting to unpublished (0) if not found
     * 							otherwise will set all items state to $setpub, 
     * @return array of item counts and messages
     */
    public function mergeSql($filename, $post) { //$catid, $setpub) {
	    //this will merge in a file which may contain inserts to multiple tables.    	
    	$postcatid = $post['impcat'];
    	$postpcatid = $post['imppcat'];
    	$setpub = $post['setpub'];
	    $cover_path = $post['cover_path'];
	    $portrait_path = $post['portrait_path'];
	    $reviewer = ($post['reviewer']=='' ? Factory::getUser()->username : $post['reviewer']);
	    $postprependnote = $post['prependnote'];
	    if ($postprependnote > 1) {
	    	$prependnote = "Imported from ".$filename." on " .Factory::getDate()->format('Y H:i')." ";
	    } else {
	    	$prependnote = '';
	    }
	    
	    $importcnts = array(
	        '#__xbbooks'=>0,'#__xbbookreviews'=>0,'#__xbpersons'=>0,'#__xbbookperson'=>0,
	    		'#__xbcharacters'=>0,'#__xbbookcharacter'=>0,
	        '#__categories'=>0, 'ignored'=>0, 'donecnt'=>0, 'skipcnt'=>0, 'errs'=>'', 'mess'=> ''
	    );
		$db = Factory::getDbo();
		// read the import file
		$fileqrys = Jfile::read(JPATH_COMPONENT_ADMINISTRATOR."/uploads/".$filename);
	    //for each query we need get the table and break the insert into key/value pairs we can manipulate
		$queries = $db->splitSql($fileqrys);
		//check if we have anything to do
		if (count($queries) == 0) {
		    $importcnts['errs']='No valid sql found in file';
		    return $importcnts;
        }
       
		$query = $db->getQuery(true); 
		$qcnt = 0;
		//check that we have an imported category for fallback in case it has been deleted
		$impcatid = XbcultureHelper::getIdFromAlias('#__categories','imported','com_xbbooks');
		if (!$impcatid>0) {
			$wynik = $this->createCat('Imported');
			//TODO test result ok
			$impcatid = $wynik['id'];
		}
		$imppcatid = XbcultureHelper::getIdFromAlias('#__categories','imported','com_xbpeople');
		if (!$imppcatid>0) {
		    $wynik = $this->createCat('Import.People','imported','com_xbpeople');
			//TODO test result ok
			$imppcatid = $wynik['id'];
		}
		//parse each line in the query file 
		foreach ($queries as $qry){
		    $qry = trim($qry);
		    //we are only going to do INSERTs and ignore blank lines and hash comments
		    if ((strpos($qry,'INSERT')===false) || ($qry == '') || ($qry[0] == '#')) {	
		        $importcnts['ignored'] ++;
		    } else {
                $qcnt ++;
                //get the table name and check it
    			$hash = strpos($qry,'#');
    			$space = strpos($qry,' ',$hash);
    			$table = substr($qry,$hash,$space-$hash);
    			$validTables = array('#__categories', '#__xbbooks', '#__xbpersons', '#__xbbookreviews', '#__xbbookperson',
					'#__xbcharacters','#__xbbookcharacter'
    			);
    			if (in_array($table, $validTables)) {
    				//convert the query into an array of key/values
	    			$qryarr = $this->insertToArray($qry);
	    			//now depending on the table for this row...
	                if ($table === '#__categories') {
						//test if cat alias already exists
	                    $pcat = $qryarr['extension']=='com_xbpeople' ? 'com_xbpeople' : 'com_xbbooks';
	                    $catret = $this->createCat($qryarr['title'],$qryarr['alias'], $pcat);
	                    if ( $catret['id'] == 0) {
	                        $importcnts['errs'] .= 'Problem creating category '.$qryarr['title'].' '.$catret['mess'].' ';
	                    } elseif ($catret['existed'] === true) {
	                        $importcnts['mess'] .= 'Category '.$qryarr['title'].'already exists, ';
	                        $importcnts['skipcnt'] ++;
	                    } else  {
	                       $importcnts[$table]++;                       
	                       $importcnts['donecnt'] ++;
	                    }               
	                   //ok that's it for a category
	                } elseif ($table==='#__xbbookperson') {
						//bookpersons is a special case, will only work if books and persons have already been added
	                 	//for bookpersons get alias cols and replace ids with new aliases
	                 	//if both aliases not found then drop the link
	                 	$book_id = XbcultureHelper::getIdFromAlias('#__xbbooks',$qryarr['bookalias']);
	                 	$per_id = XbcultureHelper::getIdFromAlias('#__xbpersons',$qryarr['personalias']);
	                 	if (($per_id>0) && ($book_id>0) && (!$this->checkBookPerson($book_id, $per_id))) {
	                 		$qryarr['book_id'] = $book_id;
	                 		$qryarr['person_id'] = $per_id;
	                 		//unset the spurious keys
                            unset($qryarr['bookalias']);
                            unset($qryarr['personalias']);
                            unset($qryarr['id']);
	                 		$newqry = $this->arrayToInsert($qryarr, $table);
	                 		$query->clear();
	                 		$db->setQuery($newqry);
	                 		if (!$db->execute()) {
	                 			Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
	                 			$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
	                 		} else {
	                 			$importcnts[$table] ++;
	                 			$importcnts['donecnt'] ++;
	                 		}	                 		
	                 	} else {
	                 		$importcnts['skipcnt'] ++;
	                 	}
	                } elseif ($table==='#__xbbookcharacter') {
	                	//bookpersons is a special case, will only work if books and persons have already been added
	                	//for bookpersons get alias cols and replace ids with new aliases
	                	//if both aliases not found then drop the link
	                	$book_id = XbcultureHelper::getIdFromAlias('#__xbbooks',$qryarr['bookalias']);
	                	$char_id = XbcultureHelper::getIdFromAlias('#__xbcharacters',$qryarr['characteralias']);
	                	if (($char_id>0) && ($book_id>0) && (!$this->checkBookCharacter($book_id, $char_id))) {
	                		$qryarr['book_id'] = $book_id;
	                		$qryarr['char_id'] = $char_id;
	                		//unset the alias keys
	                		unset($qryarr['bookalias']);
	                		unset($qryarr['characteralias']);
	                		unset($qryarr['id']);
	                		$newqry = $this->arrayToInsert($qryarr, $table);
	                		$query->clear();
	                		try {
	                			$db->setQuery($newqry);
	                			$db->execute();
	                		} catch (Exception $e) {
	                			$dberr = $e->getMessage();
	                			Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$newqry, 'error');
	                			$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
	                			Jfile::delete(JPATH_COMPONENT_ADMINISTRATOR."/uploads/".$filename);
	                			return $importcnts;
	                		}
                			$importcnts[$table] ++;
                			$importcnts['donecnt'] ++;
	                	} else {
	                		$importcnts['skipcnt'] ++;
	                	}
	                } else { //books, persons characters and reviews	                    
						//create alias if one not specified
	                    if (!key_exists('alias', $qryarr)) {
	                     	if ($table === '#__xbpersons') {
	                     		$qryarr['alias'] = OutputFilter::stringURLSafe($qryarr['firstname'].' '.$qryarr['lastname']);
	                     	} elseif ($table === '#__xbcharacters') {
	                     		$qryarr['alias'] = OutputFilter::stringURLSafe($qryarr['name']);
	                         } else {
	                         	$qryarr['alias'] = OutputFilter::stringURLSafe($qryarr['title']);
	                         }
	                    }                     
                        //get alias col (creating if necessary) and check if item already exists
	                    if (XbcultureHelper::getIdFromAlias($table,trim($qryarr['alias'],'com_xbbooks'))>0) {
                     		$importcnts['mess'] .= ' '.$table.':'.$qryarr['alias'].' already existed. Item not imported';
                     		$importcnts['skipcnt'] ++;
                     	} else {  
                     		if (key_exists('cover_img', $qryarr)) {
                     			$cover = trim($qryarr['cover_img']," '");
                     			if (($cover!='') && ($cover_path != '')) {
                     				$qryarr['cover_img'] = "'".$cover_path.basename($cover)."'";
                     			}
                     		}
                     		if (key_exists('portrait', $qryarr)) {
                     			$portrait = trim($qryarr['portrait']," '");
                     			if (($portrait!='') && ($portrait_path != '')) {
                     				$qryarr['portrait'] = "'".$portrait_path.$portrait."'";
                     			}
                     		}
                     		if (key_exists('image', $qryarr)) {
                     			$portrait = trim($qryarr['image']," '");
                     			if (($portrait!='') && ($portrait_path != '')) {
                     				$qryarr['image'] = "'".$portrait_path.$portrait."'";
                     			}
                     		}
                     		
                     		$qryarr['created_by']= Factory::getUser()->id;
                     		
//                     		$qryarr['note'] = "'".trim($qryarr['note'],"'"). " Import ".$filename." on ". Factory::getDate()->format('Y-m-d H:i').". '";
                     		if (($postprependnote == 1) || ($postprependnote==2)) {
                     			$qryarr['note'] = "'".$prependnote." ".trim($qryarr['note'],"'")." '";
                     		} else {
                     			$qryarr['note'] = "''";
                     		}
                     		
                     		$newpub = 0;
                     		if ($setpub>2) {
                     			if (key_exists('state', $qryarr)) {
                     		    	$newpub = $qryarr['state'];  
                     			} elseif (key_exists('published', $qryarr)) {
                     				$newpub = $qryarr['published'];
                     			}
                     		} else {
                     		    $newpub = $setpub;
                     		}
                      		$qryarr['state'] = $newpub;
              
                      		if (($table === '#__xbpersons') || ($table==='#__xbcharacters')) {
	                      		if ($postpcatid>0) {
	                      			$catid = $postpcatid;
	                      		} else {
	                      			if (key_exists('catalias', $qryarr)) {
	                      				$catalias = $qryarr['catalias'];
	                      				$catid = XbcultureHelper::getIdFromAlias('#__categories',$catalias,'com_xbpeople'); //$this->$qryarr['catalias']);
	                      				if ($catid==0) { $catid = $imppcatid; }
	                      			} else {
	                      				$catid = $imppcatid;
	                      			}	                      			
	                      		}
                      		} else {
	                      		if ($postcatid>0) {
	                      			$catid = $postcatid;
	                      		} else {
	                      			if (key_exists('catalias', $qryarr)) {
	                      				$catalias = $qryarr['catalias'];
	                      				$catid = XbcultureHelper::getIdFromAlias('#__categories',$catalias,'com_xbbooks'); //$this->$qryarr['catalias']);
	                      				if ($catid==0) { $catid = $impcatid; }
	                      			} else {
	                      				$catid = $impcatid;
	                      			}
	                      		}                     			
                      		}
                      		$qryarr['catid']=(string)$catid;
                      		
                      		if ($table === '#__xbbookreviews') {
                     			if (key_exists('bookalias', $qryarr)){
                     			    $bkid = XbcultureHelper::getIdFromAlias('#__xbbooks', trim($qryarr['bookalias'],'com_xbbooks')," '");
                     				if ($bkid>0) {
                     				    $qryarr['book_id'] = $bkid;
                     				} else {
                     					//we'll import it as an orphan with no linked book
                     				    $qryarr['book_id'] = 0;
                     				}
                     			}
                     			if (!key_exists('reviewer',$qryarr)) {
                     				//set reviewer name to default if missing
                     				$qryarr['reviewer'] = "'".$reviewer."'";
                     			}                    			
                     			if (!key_exists('rev_date',$qryarr)) {
                     				//will default to current in mysql as column is not null
//                     				$qryarr['reviewer'] = Factory datetosql;
                     			}
                     		}
                     		//we need to strip out any invalid columns 
                     		//TODO modify this to use table specific allowed keys in case we have other garbage
                     		//for each table get array of allowed keys
                     		//flip allowed keys array to get keys as key
                     		//array_intersect_key($qryarr, array_flip($allowed))
                     		unset ($qryarr['catalias']);
                     		unset ($qryarr['bookalias']);
                     		unset ($qryarr['personalias']);
                     		unset ($qryarr['characteralias']);
                     		unset ($qryarr['id']);
                     		unset ($qryarr['asset_id']);
                     		unset ($qryarr['checked_out']);
                     		unset ($qryarr['checked_out_time']);
                     		unset ($qryarr['modified']);
                     		unset ($qryarr['modified_by']);
                     		unset ($qryarr['metakey']);
                     		unset ($qryarr['metadesc']);
                     		unset ($qryarr['bookcat']);
                     		unset ($qryarr['filmcat']);
                     		unset ($qryarr['published']); //????
                     		
                            $newqry = $this->arrayToInsert($qryarr, $table);	
		                    $query->clear();
		                    try {
		                    	$db->setQuery($newqry);	
		                    	$db->execute();
		                    } catch (Exception $e) {
		                    	$dberr = $e->getMessage();
		                    	Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$newqry, 'error');
		                        $importcnts['errs'] .= 'error in qry '.$qcnt.', '; 
		                        Jfile::delete(JPATH_COMPONENT_ADMINISTRATOR."/uploads/".$filename);
		                        return $importcnts;		                        
		                    } 
		                    $importcnts[$table] ++;
		                    $importcnts['donecnt'] ++;		                   
                     	} //end if check alias exists
	                } //endif categories elseif bookpersons else                
            	} //end if validtable 
		    } //endif insert test else
		} //end foreach query

		Jfile::delete(JPATH_COMPONENT_ADMINISTRATOR."/uploads/".$filename);

        return $importcnts;
    }
    
    private function checkBookPerson($bk, $per) {
    	$db = Factory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('id')->from($db->quoteName('#__xbbookperson'))->where('book_id='.$db->quote($bk).' AND person_id='.$db->quote($per));
    	$db->setQuery($query);
    	return (int) $db->loadResult();
    }
    
    private function checkBookCharacter($bk, $per) {
    	$db = Factory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('id')->from($db->quoteName('#__xbbookcharacter'))->where('book_id='.$db->quote($bk).' AND char_id='.$db->quote($per));
    	$db->setQuery($query);
    	return (int) $db->loadResult();
    }
    	
	private function insertToArray($qry) {
	    $open = strpos($qry,'(')+1; //assume the first '(' is the start of the field list
	    $close = strpos($qry,')',$open); //assume the next one is the end of the field list
	    $cols = explode(',', substr($qry,$open,$close-$open));
      
	    $itemsep = "','";
	    $seprep = 'xbs';
	    $sepcnt = 0;
	    while (strpos($qry, $seprep.$sepcnt)!==false) {
	        $sepcnt++;
	    }
	    $nosep = str_replace($itemsep, $seprep.$sepcnt, $qry);
	    $comrep = 'xbc';
	    $comcnt = 0;
	    while (strpos($nosep, $seprep.$comcnt)!==false) {
	        $comcnt++;
	    }
	    $nocom = str_replace(',', $comrep.$comcnt, $nosep);
	    $nocom = str_replace($seprep.$sepcnt, $itemsep, $nocom);
	    $open = strpos($nocom,'(',$close)+1;
	    $close = strrpos($nocom,')');
	    $vals = explode(',', substr($nocom,$open,$close-$open));
	    foreach ($vals as &$value) {
	        $value = str_replace($comrep.$comcnt,',',$value);
	    }
	    return array_combine($cols,$vals);	    	    
	}
	
	private function arrayToInsert($arr,$tbl) {
	    //takes an array of key/values and a table name and generate an insert query string
	    $qstr = 'INSERT INTO '.$tbl.' (';
	    $keys = implode(',',array_keys($arr));
	    $qstr .= $keys.') VALUES (';
	    $vals = implode(',',array_values($arr));
	    $qstr .= $vals.'); ';
	    return $qstr;
	}
	
	/**
	 * @name createCat
	 * @desc creates a category given a title and optionally alias 
	 * @param string $title
	 * @param string $alias
	 * @return array containing id of the category, whether it was creted or already existed, and any error message
	 */
	private function createCat($title, $alias = '', $ext = 'com_xbbooks' ) {
	    $title = trim($title," '");
	    $alias = trim($alias," '");
	    $report = array ('id'=> 0, 'mess'=>'', 'existed'=>false );
	    //check if alias already exists
	    if ($alias == '') { $alias = OutputFilter::stringURLSafe($title); }
	    $cid = XbcultureHelper::getIdFromAlias('#__categories', $alias, 'com_xbpeople');
	    if ($cid > 0) {
	    	$report['msg'] = $qarr['alias'].' already exists. ';
	    	$report['id'] = $cid;
	    	$report['existed'] = true;
	    	return $report;	    	
	    }
	    $qarr = array('title'=>$title, 'alias'=>$alias, 'id'=>0, 'parent_id'=>0,
	    		'extension'=>$ext, 'published'=>1, 'language'=>'*'
	    );
	    $qarr['params'] = array('category_layout' => '','image' => '');
	    $qarr['metadata'] = array('author' => '','robots' => '');
	    
	    $basePath = JPATH_ADMINISTRATOR.'/components/com_categories';
	    require_once $basePath.'/models/category.php';
	    $config  = array('table_path' => $basePath.'/tables');
	    $category_model = new CategoriesModelCategory($config);	    	    
	    if(!$category_model->save($qarr)){
	    	$report['msg'] = $category_model->getError();
	        return $report;
	    }
	    $report['id'] = $category_model->getItem()->id;  
	    return $report;
	}
	
	private function getItemAlias($table,$id) {
		//TODO need optional $ext parameter for categories table
		$table=trim($table,"' ");
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('alias')
			->from($db->quoteName($table))
			->where('id = '.$id);
		if ($table === '#__categories') {
			$query->where($db->quoteName('extension')." = ".$db->quote('com_xbbooks'));			
		}
		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * getItemId
	 * @param string $table - table to search in
	 * @param string $alias - alias of item to find
	 * @return int - id of item (0 if not found)
	 */
	private function getItemId($table, $alias) {
		//TODO need optional $ext parameter for categories table
		$alias = trim($alias,"' ");
      $table = trim($table,"' ");
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id')->from($db->quoteName($table))->where($db->quoteName('alias')." = ".$db->quote($alias));
		if ($table === '#__categories') {
			$query->where($db->quoteName('extension')." = ".$db->quote('com_xbbooks'));
		}
		$db->setQuery($query);
      $res =0;
		$res = $db->loadResult();
      return $res;
	}
	
	/**
	 * @name cleanData() implements options for cleaning unwanted data
	 * @desc orphans in the people and chars tables are those that are not linked to any book (or film if xbfilms is installed)
	 *     orphan links are entries in the link tables where either the book_id or person_id/char_id is invalid (has been deleted)
	 * @param array $post - the form values
	 * @return array - counts of items deleted in each table
	 */
	public function cleanData($post,$statelist) {
	    
		$msg = '';
		$cnt = 0;
		//delete anything in the core tables by requested state. This could create orphan links which we'll deal with at the end
		if ($statelist !='') {
			$msg .= $this->deletePeople('',$statelist);
			$msg .= $this->deleteCharacters('',$statelist);
			$msg .= $this->deleteBooks('',$statelist);
			$msg .= $this->deleteReviews('', $statelist);
		}
		
		//now we'll find any orphans as required				
		//$delorphpeep = $post['delorphpeep'];
		if ($post['delorphpeep'] == 1){
			$msg .= $this->deleteOrphanPeople();
		}
		
		//$delorphchars = $post['delorphchar'];
		if ($post['delorphchar'] == 1){
			$msg .= $this->deleteOrphanCharacters();
		}
		
		//$delorphrev = $post['delorphrev'];
		if ($post['delorphrev'] == 1){
			$cnt = $this->deleteRevOrphans();
			$msg .= $cnt.' orphan reviews deleted. ';
		}
		
		//now delete any orphans in the link tables 
		$msg .= $this->deletePlinkOrphans().' book-people and ';
		$msg .= $this->deleteClinkOrphans().' film-character orphan links deleted.';
		return $msg;
	}
	
	private function deleteRevOrphans() {
		$cnt =0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$qrystr ='DELETE a FROM '.$db->quoteName('#__xbbookreviews').' AS a';
		$qrystr .= ' LEFT JOIN '.$db->quoteName('#__xbbooks').' AS b ON b.id = a.book_id';
		$qrystr .= ' WHERE b.id IS NULL';
		try {
			$db->setQuery($qrystr);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deleteRevOrphans() Query: '.$query, 'error');
		}
		return $cnt;
	}
	
	private function deleteOrphanPeople() {
		//orphans here means people/chars without a book link (or film link if xbfilms is installed)
		// get list of all people/chars
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from($db->quoteName('#__xbpersons'));
		$db->setQuery($query);
		$allpeople = $db->loadColumn();
		
		//get lists of all the personids in bookperson (and filmperson if installed)
		$query->clear();
		$query->select('DISTINCT person_id')->from($db->quoteName('#__xbbookperson'));
		$db->setQuery($query);
		$bookpeople = $db->loadColumn();
		
		if ($this->xbfilmsStatus !== false) {
			$query->clear();
			$query->select('DISTINCT person_id')->from($db->quoteName('#__xbfilmperson'));
			$db->setQuery($query);
			$filmpeople = $db->loadColumn();
			$query->clear();
			//subtract the linked people
			$orphans = array_diff($allpeople, $bookpeople, $filmpeople);
		} else {
			$orphans = array_diff($allpeople, $bookpeople);
		}
		
		$query->clear();
		$query->delete($db->quoteName('#__xbpersons'));
		$query->where('id IN ('.implode(',',$orphans).')');
		try {
			$db->setQuery($query);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deleteOrphanPeople() Query: '.$query, 'error');
		}
		$msg = $cnt.' orphan people deleted. ';
		return $msg;
	}
	
	private function deleteOrphanCharacters() {
		//orphans here means people/chars without a book link (or film link if xbfilms is installed)
		// get list of all people/chars
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from($db->quoteName('#__xbcharacters'));
		$db->setQuery($query);
		$allpeople = $db->loadColumn();
		
		//get lists of all the personids in bookperson (and filmperson if installed)
		$query->clear();
		$query->select('DISTINCT char_id')->from($db->quoteName('#__xbbookcharacter'));
		$db->setQuery($query);
		$bookpeople = $db->loadColumn();
		
		if ($this->xbfilmsStatus !== false) {
		    $query->clear();
			$query->select('DISTINCT char_id')->from($db->quoteName('#__xbfilmcharacter'));
			$db->setQuery($query);
			$filmpeople = $db->loadColumn();
			$query->clear();
			//subtract the linked people
			$orphans = array_diff($allpeople, $bookpeople, $filmpeople);
		} else {
			$orphans = array_diff($allpeople, $bookpeople);
		}
		
		$query->clear();
		$query->delete($db->quoteName('#__xbcharacters'));
		$query->where('id IN ('.implode(',',$orphans).')');
		try {
			$db->setQuery($query);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
		}
		$msg = $cnt.' orphan characters deleted. ';
		return $msg;
	}
	
	private function deletePlinkOrphans() {
		$cnt = 0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		//can't use $query->delete(... as it screws up the first line
		$qrystr ='DELETE a FROM '.$db->quoteName('#__xbbookperson').' AS a';
		$qrystr .= ' LEFT JOIN '.$db->quoteName('#__xbbooks').' AS b ON b.id = a.book_id';
		$qrystr .= ' LEFT JOIN '.$db->quoteName('#__xbpersons').' AS p ON p.id = a.person_id';
		$qrystr .= ' WHERE b.id IS NULL OR p.id IS NULL';
		try {
			$db->setQuery($qrystr);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deletePlinkOrphans() Query: '.$query, 'error');
		}
		return $cnt;		
	}
		
	private function deleteClinkOrphans() {
		$cnt = 0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$qrystr ='DELETE a FROM '.$db->quoteName('#__xbbookcharacter').' AS a';
		$qrystr .= ' LEFT JOIN '.$db->quoteName('#__xbbooks').' AS b ON b.id = a.book_id';
		$qrystr .= ' LEFT JOIN '.$db->quoteName('#__xbcharacters').' AS c ON c.id = a.char_id';
		$qrystr .= ' WHERE b.id IS NULL OR c.id IS NULL';
		try {
			$db->setQuery($qrystr);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deleteClinkOrphans() Query: '.$query, 'error');
		}
		return $cnt;		
	}
	
	/**
	 * @name clearData
	 * @desc deletes all xbboks data without any filtering
	 *     if xbFilms is installed people and charactyers used in cxbFilms will not be deleted
	 * @return boolean true if done, false if error. Message written to enqueueMessage
	 */
	public function clearData(){
		$mess = 'Deleteing all xbBooks data: ';
		$mess .= $this->deleteBooks();
		$mess .= $this->deleteReviews();
		$mess .= $this->deletePeople();
		$mess .= $this->deleteCharacters();
		
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
        $query->delete('#__categories');
        $query->where($db->quoteName('extension').' = '.$db->quote('com_xbbooks'));
        try {
            $db->setQuery($query);
            $db->execute();
            $cnt += $db->getAffectedRows();
        } catch (Exception $e) {
            $emess = $e->getMessage().'<br />clearData() Query: '.$qry;
            if ($cnt>0) {
                $mess .= '</br><b>WARNING</b> '.$cnt.' items have been deleted. Check data carefully.';
            }
            Factory::getApplication()->enqueueMessage($emess, 'error');
	    }
	    $this->createCat('Uncategorised');
	    $this->createCat('Imported');

	    //clear the tag map of xbbooks items, but we are not deleting the tags as they may be used elsewhere
	    //we're not bothering with deleted item count below here, this is just meta data
	    $db = Factory::getDbo();	    
		$query = $db->getQuery(true);
		$types = "('com_xbbooks.book','com_xbbooks.review','com_xbbooks.category'";
	    //we're not deleting people or char tags unless xbfilms not installed
		if (!$this->xbfilmsStatus) { $types .= "'com_xbpeople.person','com_xbpeople.character'";}
		$types .= ")";
		$query->delete('#__contentitem_tag_map')->where('type_alias in '.$types);
		try {
		    $db->setQuery($query);
		    $db->execute();
		} catch (Exception $e) {
		    $emess = $e->getMessage().'<br />clearData() Query: '.$qry;
		    Factory::getApplication()->enqueueMessage($emess, 'error');
		}
		
		//now clean up the ucm rubbish
	    $query->clear();
		$subq = "(select type_id from '.$db->quoteName('#__content_types').' where type_alias in ".$types;
	    $query->delete('#__ucm_history')->where('ucm_type_id in '.$subq);
	    try {
	        $db->setQuery($query);
	        $db->execute();
	    } catch (Exception $e) {
	        $mess = $e->getMessage().'<br />clearData() Query: '.$qry;
	        Factory::getApplication()->enqueueMessage($mess, 'error');
	    }
	    
	    $query->clear();
	    $query->delete('#__ucm_base')->where('ucm_type_id in '.$subq);
	    try {
	        $db->setQuery($query);
	        $db->execute();
	    } catch (Exception $e) {
	        $mess = $e->getMessage().'<br />clearData() Query: '.$qry;
	        Factory::getApplication()->enqueueMessage($mess, 'error');
	    }
	    
	    $query->clear();
	    $query->delete('#__ucm_content')->where('core_type_alias in '.$types);
	    try {
	        $db->setQuery($query);
	        $db->execute();
	    } catch (Exception $e) {
	        $mess = $e->getMessage().'<br />clearData() Query: '.$qry;
	        Factory::getApplication()->enqueueMessage($mess, 'error');
	    }
	    
	    $query->clear();
	    	    
	    Factory::getApplication()->enqueueMessage('All xbBooks data cleared. '.$mess.' Tags have not themselves been deleted, but tag links to deleted items have been cleared. ','success');
	    
	    return true;
	}

	public function deleteBooks($catid = 0, $statelist='',$delrevs=0) {
		$mess = '';
		$cnt = 0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__xbbooks'));
		if ($catid > 0) {
			$query->where('catid = '.$db->quote($catid) );
		}
		if ($statelist != '') {
			$query->where('state IN ('.$statelist.')');
		}
		try {
			$db->setQuery($query);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deleteBooks() Query: '.$query, 'error');
			return 'probably nothing deleted owing to error - please check. ';
		}
		$mess = $cnt.' books deleted, ';
		if ($delrevs == 1) {
			$revscnt = $this->deleteRevOrphans();
			$mess .= $revscnt.' associated reviews also deleted. ';
		}
		$plinkcnt = $this->deletePlinkOrphans();
		$clinkcnt = $this->deleteClinkOrphans();
		$mess .= $plinkcnt.' people and '.$clinkcnt.' character redundant book links.';		
		return $mess;
	}
		
	public function deletePeople($catid = 0, $statelist = '') {
	    $cnt = 0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$qstr = 'DELETE a FROM '.$db->quoteName('#__xbpersons').' AS a';
		if ($this->xbfilmsStatus!==false) {
			//check not used in films
			$qstr .= ' LEFT JOIN '.$db->quoteName('#__xbfilmperson').' AS fp ON fp.person_id =  a.id';
			$qstr .= ' WHERE fp.id IS NULL';
		}
		if ($statelist != '') {
			if (strpos($qstr,'WHERE' === false)) {
				$qstr .= ' WHERE';
			} else {
				$qstr .= ' AND';
			}
			$qstr .= ' a.state IN ('.$statelist.')';
		}		
		if ($catid>0) {
			if (strpos($qstr,'WHERE' === false)) {
				$qstr .= ' WHERE';
			} else {
				$qstr .= ' AND';
			}
			$qstr = ' a.catid = '.$catid;
		}
		try {
			$db->setQuery($qstr);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deletePeople() Query: '.$qstr, 'error');			
		}		
		//now need to delete any orphan links
		$lcnt = $this->deletePlinkOrphans();
		$mess = $cnt.' people deleted, '.$lcnt.' related redundant book links deleted.';
		return $mess;
	}
		
	public function deleteCharacters($catid = 0, $statelist = '') {
	    $cnt = 0;
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$qstr = 'DELETE a FROM '.$db->quoteName('#__xbcharacters').' AS a';
		if ($this->xbfilmsStatus!==false) {
		    //check not used in films
		    $qstr .= ' LEFT JOIN '.$db->quoteName('#__xbfilmcharacter').' AS fp ON fp.char_id =  a.id';
		    $qstr .= ' WHERE fp.id IS NULL';
		}
		if ($statelist != '') {
		    if (strpos($qstr,'WHERE' === false)) {
		        $qstr .= ' WHERE';
		    } else {
		        $qstr .= ' AND';
		    }
		    $qstr .= ' a.state IN ('.$statelist.')';
		}
		if ($catid>0) {
		    if (strpos($qstr,'WHERE' === false)) {
		        $qstr .= ' WHERE';
		    } else {
		        $qstr .= ' AND';
		    }
		    $qstr = ' a.catid = '.$catid;
		}
		try {
			$db->setQuery($qstr);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deleteCharacters() Query: '.$query, 'error');
		}
		//now need to delete any orphan links
		$lcnt = $this->deleteClinkOrphans();
		$mess = $cnt.' characters deleted, '.$lcnt.' related redundant links deleted.';
		return $mess;
	}
	
	public function deleteReviews($catid=0, $statelist='') {
		
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__xbbookreviews'));
		if ($catid > 0) {
			$query->where('catid = '.$db->quote($catid));
		}
		if ($statelist != '') {
			$query->where('state IN ('.$statelist.')');
		}

		try {
			$db->setQuery($query);
			$db->execute();
			$cnt = $db->getAffectedRows();
		} catch (Exception $e) {
			$dberr = $e->getMessage();
			Factory::getApplication()->enqueueMessage($dberr.'<br />deleteReviews() Query: '.$query, 'error');
		}
	 	$mess =  $cnt. ' reviews deleted.';
		return $mess;
	}
		
	private function datadumpCsv ($table, $expcat='0',$exppcat='0', $header='') {
		$imgcnt = 0;
	    $imglist = '';
	    $result = '';
	    $resrt = '';
	    $db = $this->getDbo();
	    $query = $db->getQuery(true);
	    $select = '';
	    switch ($table){
	    	case '#__xbbooks' :
	    		$select = 'title AS book_title, subtitle, alias AS book_alias, summary AS book_summary, synopsis, 
					cover_img,pubyear, publisher, orig_lang,edition,format, 
					 fiction, first_read, last_read, note AS book_note';
	    		break;
	    	case '#__xbbookreviews' :
	    		$select = 'a.title AS review_title, a.alias AS review_alias, b.alias AS book_alias, a.summary AS review_summary, review, 
					rev_date, reviewer, rating, a.note AS review_note';
	    		$query->join('LEFT', '#__xbbooks AS b ON b.id = a.book_id');		
	    		break;
	    	case '#__xbpersons' :
	    		$select = 'firstname, lastname, alias AS person_alias, summary AS person_summary, biography, portrait, nationality,
					year_born, year_died, note AS person_note';
	    		break;
	    	case '#__xbbookperson' :
	    		$select = 'a.role, a.role_note, b.alias AS book_alias, p.alias AS person_alias';
	    		$query->join('LEFT', '#__xbbooks AS b ON b.id = a.book_id');
	    		$query->join('LEFT', '#__xbpersons AS p ON p.id = a.person_id');
	    		break;
	    	case '#__xbcharacters' :
	    	    $select = 'name, alias AS character_alias, summary AS character_summary, description, image, note AS character_note';
	    	    break;
	    	case '#__xbbookcharacter' :
	    		//for characters we export with virtual role column set to "character"
	    	    $select = 'a.char_note, b.alias AS book_alias, p.alias AS character_alias'
					.$db->quote('character').' AS role';
	    	    $query->join('LEFT', '#__xbbooks AS b ON b.id = a.book_id');
	    	    $query->join('LEFT', '#__xbcharacters AS p ON p.id = a.char_id');
	    	    break;
	    	case '#__categories' :
	    		$select = 'a.title, a.alias AS category_alias, a.description, a.note AS category_note';
	    		$query->where('a.extension IN ('.$db->quote('com_xbbooks').','.$db->quote('com_xbpeople').')');
	    	default:
	    		Factory::getApplication()->enqueueMessage('Sorry, csv export type "'.$table.'" not supported', 'warning');
	    		return;
	    }
	    $query->select($select);
	    $query->from($table.' AS a');
	    if (($expcat!=0)) {
	    	if (($table == '#__xbbooks') || ($table == '#__xbbookreviews')) {
	    		$query->where('catid='.$expcat);
	    	} elseif (($table == '#__xbpersons') || ($table == '#__xbcharacters')) {
	    		$query->where('catid='.$exppcat);
	    	}
	    }
	    try {
	    	$db->setQuery($query);
	    	$rows = $db->loadAssocList();
	    	
	    } catch (Exception $e) {
	    	$dberr = $e->getMessage();
	    	//set message in session variable and pickup in default.php
	    	$session = Factory::getSession();
	    	$session->set('exprep', $dberr, 'Xbbooks');
	    	//Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
	    	return;
	    }
	    //output list of keys as first line
	    $keys = array_keys($rows[0]);
	    foreach ($keys as $kv) {
	    	$result .= '"'.$kv.'",';
	    }
	    $result = trim($result,',');
	    $result .= "\r\n";
	    $result .= '"#","';
	    foreach ((array)$header as $key=>$value) {
	    	$result .= ' '.$key.': '.addslashes($value).'.';
	    }
	    $result .= '"'.str_repeat(',', count($keys)-2);
	    $result .= "\r\n";
	    //output data for each row in double quotes comma separated (replace any double quotes in string with &quot;)
	    foreach($rows as $row){
	        $resrt = '';
	        
	        foreach($row as $key=>$value) {
	        	//strip out newlines - they will screw up csv format when read
	        	$value = str_replace(array("\n", "\r"), ' ', $value);
	        	//strip off the folder from image filenames - a different site may have completely different images folder structure
	        	if (($key == 'portrait') || ($key == 'cover_img') || ($key == 'image')) {
	        		if (!empty($value)) {
		        		$imglist .= $value.'<br />';
		        		$imgcnt ++;
	        			$value = basename($value);
	        		}
	        	}
	        	//escape any quotes or double qoutes - will need to be unescaped when reading
	            $resrt .= '"'.addslashes($value).'",';	            
	        }	            
	        $resrt = trim($resrt,',');
	        $resrt .= "\r\n";
	        $result .= $resrt;
	    }
	    $message = count($rows).' items exported to file.';
	    if ($imgcnt>0) {
	    	$message .= '<br />'. $imgcnt. ' images, which need to be exported manually:<br />'.$imglist;
	    }
	    $session = Factory::getSession();
	    $session->set('exprep', $message, 'Xbbooks');	    
	    echo $result;	    
	    return;
	}
		
/**
 * @desc mergeCsv() imports data from csv file
 * @param string $csvfile - file pathname to import
 * @param array $post - the form data from view
 * @return array $importcnts - array of counts and error messages
 */
	public function mergeCsv($csvfile, $post) {
		$filename = JPATH_COMPONENT_ADMINISTRATOR.'/uploads/'.$csvfile;
		$cover_path = $post['cover_path'];
		$portrait_path = $post['portrait_path'];
		$postcatid = $post['impcatcsv'];  //for csv we are not importing category names
		$postpcatid = $post['imppcatcsv'];		
		$poststate = $post['setpub']; //for csv we are not keeping any existing state info
		$postreviewer = ($post['reviewer']=='' ? Factory::getUser()->username : $post['reviewer']);
		$postprependnote = $post['prependnote'];		
		if ($postprependnote > 1) {
			$prependnote = "Imported from ".$csvfile." on " .Factory::getDate()->format('d-m-Y H:i')." ";
		} else {
			$prependnote = '';
		}
		//setup return array
		$importcnts = array(
		    '#__xbbooks'=>0,'#__xbbookreviews'=>0,'#__xbpersons'=>0,'#__xbbookperson'=>0,
		    '#__xbcharacters'=>0,'#__xbbookcharacter'=>0,
			'#__categories'=>0, 'ignored'=>0, 'donecnt'=>0, 'skipcnt'=>0, 'errs'=>'', 'mess'=> ''
		);
		$qcq = "','";
				
		//read file strip out comment lines (starting with #) and parse to array		
		//NB there must be no newlines in the data fields
		//NB2 headers to be used must be correct format for column names - lower case with underscores, no spaces
		$csvrows = array_map('str_getcsv', file($filename)); //$lines);
		array_walk($csvrows, function(&$a) use ($csvrows) {
			$a = array_combine($csvrows[0], $a);
		});	
		$hd = implode(',', csvrows[0]);
		array_shift($csvrows); // remove column header row
		//now check the header row to see if we have required fields for books, persons and reviews
		//book must have title
		$bookcheck = (key_exists('book_title',$csvrows[0])) ? true : false;
		//person must have lastname
		$personcheck = (key_exists('lastname',$csvrows[0])) ? true : false;
		//character must have name
		$charcheck = (key_exists('name',$csvrows[0])) ? true : false;
		//review must have rev_date, rating (review_title will be created with default if empty/missing)
		$reviewcheck = ((key_exists('rev_date', $csvrows[0])) && (key_exists('rating',$csvrows[0]))) ? true : false;
		//bookperson must have role and (booktitle or bookalias) and (lastname or name or personalias)
		//bookchar will have role==character
		$bookpersoncheck = ((key_exists('role',$csvrows[0])) && 
			((key_exists('book_title',$csvrows[0])) || (key_exists('book_alias',$csvrows[0]))) && 
			((key_exists('lastname',$csvrows[0])) || (key_exists('person_alias',$csvrows[0])))) ? true : false;
		$bookcharactercheck = ((key_exists('role',$csvrows[0])) && 
		    ((key_exists('book_title',$csvrows[0])) || (key_exists('book_alias',$csvrows[0]))) && 
			((key_exists('name',$csvrows[0])) || (key_exists('character_alias',$csvrows[0])))) ? true : false;
				
		if (!(($bookcheck==true) || ($personcheck==true) || ($charcheck==true) || ($reviewcheck==true) 
				|| ($bookpersoncheck==true) || ($bookcharactercheck==true) )) {
			$importcnts['errs'] = 'No valid set of columns to import found in header';
			return $importcnts;
		} else {
			//ok we've got some valid columns in the header now parse each row
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			foreach ($csvrows as $row) {
				//comments flagged by '#' as a field (usually the first but could be any - beware)
			  if (array_search('#',$row)===false) {
			  	foreach ($row as $key=>$value) {
			  		str_replace(array("\n", "\r"), ' ', $value);
			  		$row[$key] = $value;
			  	}
				$bookid=0;
				$personid=0;
				$characterid=0;
				if ($bookcheck && ($row['book_title']!='')) {
					//get book data
					$bookalias = key_exists('book_alias',$row)? $row['book_alias'] : '';
					if ((trim($bookalias)=='') && (key_exists('book_title', $row))) {
						$bookalias = preg_replace('/[^a-z0-9 ]/', '', strtolower($row['book_title']));
						$bookalias = preg_replace('/\s+/', '-', $bookalias);
					}
					//check if book already exists
					if ($bookalias != '') {
						$bookid = XbcultureHelper::getIdFromAlias('#__xbbooks', $bookalias);
					}
					if ($bookid>0) {
						$importcnts['skipcnt'] ++;
						// $importcnts['mess'] .= $bookalias.', ';
					} else {
						$sqlbook = "INSERT INTO #__xbbooks (title,subtitle,alias,summary,synopsis,
							cover_img,pubyear,publisher,orig_lang,edition,format,first_read,last_read,
							note,fiction,catid,state) VALUES ('";
						$sqlbook .= $db->escape($row['book_title']).$qcq;
						$sqlbook .= (key_exists('subtitle',$row) ? $db->escape($row['subtitle']) : '').$qcq;
						$sqlbook .= $bookalias.$qcq;
						$sqlbook .= (key_exists('book_summary',$row) ? $db->escape($row['book_summary']) : '').$qcq;
												
						$sqlbook .= (key_exists('synopsis',$row) ? $db->escape($row['synopsis']) : '').$qcq;
						if (key_exists('cover_img',$row)) {
							if ($row['cover_img']!='') {
								if ($cover_path!='') {
									$sqlbook .= $cover_path.basename($row['cover_img']); 
								} else {
									$sqlbook .= $row['cover_img'];
								}
							}
						}
						$sqlbook .= $qcq;
						$sqlbook .= (key_exists('pubyear',$row) ? $row['pubyear'] : '').$qcq;
						$sqlbook .= (key_exists('publisher',$row) ? $db->escape($row['publisher']) : '').$qcq;
						$sqlbook .= (key_exists('orig_lang',$row) ? $db->escape($row['orig_lang']) : '').$qcq;
						$sqlbook .= (key_exists('edition',$row) ? $db->escape($row['edition']) : '').$qcq;
						$sqlbook .= (key_exists('format',$row) ? $db->escape($row['format']) : '').$qcq;
						$sqlbook .= (key_exists('first_read',$row) ? date('Y-m-d',strtotime($row['first_read'])) : '').$qcq;
						$sqlbook .= (key_exists('last_read',$row) ? date('Y-m-d',strtotime($row['last_read'])) : '').$qcq;
											
						
						$sqlbook .= $prependnote;
						if (key_exists('book_note',$row)) {
							if (($postprependnote==1) || ($postprependnote==2)) {
								$sqlbook .= $db->escape($row['book_note']);
							}
						}
						$sqlbook .= $qcq;
						//need to test for fiction - how??? currently defaulting to non-fiction if row not present
						$sqlbook .= (key_exists('fiction',$row) ? (int)$row['fiction'] : '0').$qcq;
						$sqlbook .= $postcatid.$qcq;
						$sqlbook .= $poststate."')";						
						$query->clear();						
						try {
							$db->setQuery($sqlbook);
							$db->execute();
						} catch (Exception $e) {
							Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
							$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
							return $importcnts;
						}
						$importcnts['#__xbbooks'] ++;
						$importcnts['donecnt'] ++;
						$bookid = $db->insertid();
					} //endif bookid 
				} //endif bookcheck title check
				if ($personcheck && (trim($row['lastname'])!='')) {
					$lastname = $db->escape(trim($row['lastname']));
					$firstname = $db->escape($row['firstname']);
					$personalias = (key_exists('person_alias',$row)) ? $row['person_alias'] : '';
					if (trim($personalias == '')) {
						$personalias = preg_replace('/[^a-z0-9 ]/', '', strtolower($firstname.' '.$lastname));
						$personalias = preg_replace('/\s+/', '-', $personalias);						
					}
					//check if person already exists
					$personid = XbcultureHelper::getIdFromAlias('#__xbpersons', $personalias);
					if ($personid>0) {
						$importcnts['skipcnt'] ++;
						$importcnts['mess'] .= $personalias.', ';
					} else {
						$sqlperson = "INSERT INTO #__xbpersons (firstname,lastname,alias,summary,biography,portrait,nationality,year_born,year_died,note,catid,state) VALUES ('";
						$sqlperson .= $firstname.$qcq.$lastname.$qcq.$personalias.$qcq;
						$sqlperson .= (key_exists('person_summary',$row) ? $db->escape($row['person_summary']) : '').$qcq;
						$sqlperson .= (key_exists('biography',$row) ? $db->escape($row['biography']) : '').$qcq;
						if (key_exists('portrait',$row)) {
							if ($row['portrait']!='') {
								if ($portrait_path!='') {
									$sqlperson .= $portrait_path.basename($row['portrait']);
								} else {
									$sqlperson .= $row['portrait'];
								}
							}
						}
						$sqlperson .= $qcq;
						$sqlperson .= (key_exists('nationality',$row) ? $db->escape($row['nationality']) : '').$qcq;
						$sqlperson .= (key_exists('year_born',$row) ? $row['year_born'] : '').$qcq;
						$sqlperson .= (key_exists('year_died',$row) ? $row['year_died'] : '').$qcq;
						$sqlperson .= $prependnote;
						if (key_exists('person_note',$row)) {
							if (($postprependnote == 1) || ($postprependnote==2)) {
								$sqlperson .= $db->escape($row['person_note']);
							}
						}
						$sqlperson .= $qcq;
						$sqlperson .= $postpcatid.$qcq;
						$sqlperson .= $poststate."')";
						
						$query->clear();
						
						try {
							$db->setQuery($sqlperson);
							$db->execute();
						} catch (Exception $e) {
							Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
							$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
							return $importcnts;
						}
						$personid = $db->insertid();
						$importcnts['#__xbpersons'] ++;
						$importcnts['donecnt'] ++;
					}
				} //endif personcheck
				if ($charcheck && (trim($row['name'])!='')) {
					$name = $db->escape(trim($row['name']));
					$characteralias = (key_exists('character_alias',$row)) ? $row['character_alias'] : '';
					if (trim($characteralias == '')) {
						$characteralias = preg_replace('/[^a-z0-9 ]/', '', strtolower($name));
						$characteralias = preg_replace('/\s+/', '-', $characteralias);
					}
					//check if character already exists
					$characterid = XbcultureHelper::getIdFromAlias('#__xbcharacters', $characteralias);
					if ($characterid>0) {
						$importcnts['skipcnt'] ++;
						$importcnts['mess'] .= $characteralias.', ';
					} else {
						$sqlperson = "INSERT INTO #__xbcharacters (name,alias,summary,description,image,note,catid,state) VALUES ('";
						$sqlperson .= $name.$qcq.$characteralias.$qcq;
						$sqlperson .= (key_exists('character_summary',$row) ? $db->escape($row['character_summary']) : '').$qcq;
						$sqlperson .= (key_exists('description',$row) ? $db->escape($row['description']) : '').$qcq;
						if (key_exists('image',$row)) {
							if ($row['image']!='') {
								if ($portrait_path!='') {
									$sqlperson .= $portrait_path.basename($row['image']);
								} else {
									$sqlperson .= $row['image'];
								}
							}
						}
						$sqlperson .= $qcq;
						$sqlperson .= $prependnote;
						if (key_exists('character_note',$row)) {
							if (($postprependnote == 1) || ($postprependnote==2)) {
								$sqlperson .= $db->escape($row['character_note']);
							}
						}
						$sqlperson .= $qcq;
						$sqlperson .= $postpcatid.$qcq;
						$sqlperson .= $poststate."')";
						
						$query->clear();
						
						try {
							$db->setQuery($sqlperson);
							$db->execute();
						} catch (Exception $e) {
							Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
							$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
							return $importcnts;
						}
						$characterid = $db->insertid();
						$importcnts['#__xbcharacters'] ++;
						$importcnts['donecnt'] ++;
					}
				} //endif charactercheck
				if ($bookpersoncheck && ($row['role'] != 'character')) {
					//check we have a book and person id
					//book/person id will have been set already if row contains a valid book or person columns
					$bookid = ($bookid == 0) ? XbcultureHelper::getIdFromAlias('#__xbbooks', $bookalias) : $bookid;
					$role = $row['role'];
					if ($role =='') { $role = 'author'; }
					$personid = ($personid == 0) ? XbcultureHelper::getIdFromAlias('#__xbpersons', $row['person_alias']) : $personid;
					
										
					if (($bookid>0) && ($personid>0)) {
						$query->clear();
						$query->select('id')->from('#__xbbookperson');
						$query->where('book_id = '.$db->quote($bookid));
						$query->where('person_id = '.$db->quote($personid));
						$query->where('role = '.$db->quote($role));
						$db->setQuery($query);
						$linkid = $db->loadResult();
						if ($linkid>0) {
							$importcnts['skipcnt'] ++;
							$importcnts['mess'] .= 'plink-'.$linkid.', ';
						} else {
							$role_note = (key_exists('role_note',$row)) ? $row['role_note'] : '';
    						$sqllink = "INSERT INTO #__xbbookperson (book_id,person_id,role,role_note) VALUES ('";
    						$sqllink .= $bookid.$qcq.$personid.$qcq.$role.$qcq.$role_note."')";
							$query->clear();							
							try {
								$db->setQuery($sqllink);
								$db->execute();
							} catch (Exception $e) {
								Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
								$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
								return $importcnts;
							}
							$importcnts[$table] ++;
							$importcnts['donecnt'] ++;
						}
					}
				} //endif bookcharperson
				if ($bookcharactercheck && ($row['role'] == 'character')) {
					//check we have a book and person id
					//book/person id will have been set already if row contains a valid book or person columns
					$bookid = ($bookid == 0) ? XbcultureHelper::getIdFromAlias('#__xbbooks', $bookalias) : $bookid;
					$characterid = ($characterid == 0) ? XbcultureHelper::getIdFromAlias('#__xbcharacters', $row['character_alias']) : $characterid;
										
					if (($bookid>0) && ($characterid>0)) {
						$query->clear();
						$query->select('id')->from('#__xbbookcharacter');
						$query->where('book_id = '.$db->quote($bookid));
						$query->where('char_id = '.$db->quote($characterid));
						$db->setQuery($query);
						$linkid = $db->loadResult();
						if ($linkid>0) {
							$importcnts['skipcnt'] ++;
							$importcnts['mess'] .= 'clink-'.$linkid.', ';
						} else {
							$char_note = (key_exists('char_note',$row)) ? $row['char_note'] : '';
							$sqllink = "INSERT INTO #__xbbookcharacter (book_id,char_id,char_note) VALUES ('";
							$sqllink .= $bookid.$qcq.$personid.$qcq.$char_note."')";
							$query->clear();
							try {
								$db->setQuery($sqllink);
								$db->execute();
							} catch (Exception $e) {
								Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
								$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
								return $importcnts;
							}
							$importcnts[$table] ++;
							$importcnts['donecnt'] ++;
						}
					}
				} //endif $bookcharactercheck
				
				if ($reviewcheck) { //can't import review without book
					//check whether we have rating and date for this review (required)
					if (($row['rev_date'] != '') && ($row['rating'] != '')) {
						$rev_title = '';
						If (key_exists('review_title',$row)) {
							$rev_title = $db->escape($row['review_title']);
						}
						if (trim($rev_title == '')) {
							$rev_title = 'Review of "'.$db->escape($row['book_title']).'"';
						}
						$revalias = key_exists('review_alias',$row)? $row['review_alias'] : '';
						if (trim($revalias)=='') {
							$revalias = preg_replace('/[^a-z0-9 ]/', '', strtolower($rev_title));
							$revalias = preg_replace('/\s+/', '-', $revalias);
						}
						//check if review already exists
						$revid = XbcultureHelper::getIdFromAlias('#__xbbookreviews', $revalias);
						if ($revid>0) {
							$importcnts['skipcnt'] ++;
							$importcnts['mess'] .= $revalias.', ';
						} else {						
							$bookid = $bookid>0 ? $bookid : XbcultureHelper::getIdFromAlias('#__xbbooks', $row['book_alias']);
							$sqlrev = "INSERT INTO #__xbbookreviews (title,alias,book_id,summary,review,rev_date,reviewer,rating,note,catid,state) VALUES ('";
							$sqlrev .= $rev_title.$qcq;
							$sqlrev .= $revalias.$qcq;
							$sqlrev .= $bookid.$qcq;
							$sqlrev .= (key_exists('review_summary',$row) ? $db->escape($row['review_summary']) : '').$qcq;
							$sqlrev .= (key_exists('review',$row) ? $db->escape($row['review']) : '').$qcq;
							$sqlrev .= date('Y-m-d',strtotime($row['rev_date'])).$qcq;
							if ((key_exists('reviewer',$row)) && ($row['reviewer'] != '')) {
								$sqlrev .= $db->escape($row['reviewer']).$qcq;								
							} else {
								$sqlrev .= $postreviewer.$qcq;
							}
							$sqlrev .= (int)$row['rating'].$qcq;
							$sqlrev .= $prependnote;
							if (key_exists('review_note',$row)) {
								if (($postprependnote == 1) || ($postprependnote==2)) {
									$sqlrev .= $db->escape($row['review_note']);
								}
							}
							$sqlrev .= $qcq;
							$sqlrev .= $postcatid.$qcq;
							$sqlrev .= $poststate."')";
							
							$query->clear();
							try {
								$db->setQuery($sqlrev);
								$db->execute();
							} catch (Exception $e) {
								Factory::getApplication()->enqueueMessage('JDatabase::query: '.Text::_('XBCULTURE_SQL_ERROR')." ".$db->stderr(true), 'warning');
								$importcnts['errs'] .= 'error in qry '.$qcnt.', ';
								return $importcnts;
							}
							//$personid = $db->insertid();
							$importcnts['#__xbbookreviews'] ++;
							$importcnts['donecnt'] ++;
							
							
						} //endif revid!=0
					} //endif rev_date && rating				
				} //endif reviewcheck
				} //endif # check
			} //endforeach row
		} //endif valid columns
		Jfile::delete(JPATH_COMPONENT_ADMINISTRATOR."/uploads/".$filename);
				
		return $importcnts;
	}
	
}

