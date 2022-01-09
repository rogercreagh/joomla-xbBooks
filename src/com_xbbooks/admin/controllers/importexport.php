<?php
/*******
 * @package xbBooks
 * @filesource admin/controllers/importexport.php
 * @version 0.9.6.f 9th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

class XbbooksControllerImportexport extends FormController
{
	function export() {
		Session::checkToken() or die( 'Invalid Token' );
		$jinput = Factory::getApplication()->input;
		$post   = $jinput->get('jform', array(), 'ARRAY');
		$link = 'index.php?option=com_xbbooks&view=importexport&format=raw';
		$model = $this->getModel('importexport');
		$model->setExportInfo($post);
		$this->setRedirect($link);
	}
	
	function import() {
		$msg = '';
		$msgtype = 'information';
		$jinput = Factory::getApplication()->input;
		$post   = $jinput->get('jform', 'array()', 'ARRAY');
		$model = $this->getModel('importexport');
		$link = 'index.php?option=com_xbbooks&view=importexport';
		
		//get the filename and copy it in to tmp folder
		$importfile = $jinput->files->get('jform', null, 'files', 'array' );
		if ($post['imptype'] == 3 ) {
			$filename = JFile::makeSafe($importfile['import_file']['name']);
			$src = $importfile['import_file']['tmp_name'];
		} else {
			$filename = JFile::makeSafe($importfile['import_filecsv']['name']);
			$src = $importfile['import_filecsv']['tmp_name'];
		}
		$dest = JPATH_COMPONENT_ADMINISTRATOR ."/uploads/". $filename;
		JFile::upload($src, $dest);
		switch ($post['imptype']) {
			case 3: //merge data with cross checks
				$wynik = $model->mergeSql($filename, $post); //['impcat'],$post['setpub'] );
				break;
			case 4: //Import csv single table
					$wynik = $model->mergeCsv($filename, $post); //['impcat'],$post['setpub'] );
				break;
			default:
				$wynik['errs'] = Text::_( 'XBCULTURE_INVALID_OPTION' );
				break;
		}
		//get back counts for each table imported plus error details
		if ($wynik['errs'] != '') {
			Factory::getApplication()->enqueueMessage('Errors during import, please check results.<br />'.$wynik['errs'],'error');
		}				
		if ($wynik['donecnt'] > 0 ) {
			$msg='Data imported. ';
			if ($wynik['#__xbbooks']>0) { $msg .= $wynik['#__xbbooks'].' books, ';}
			if ($wynik['#__xbbookreviews']>0) { $msg .= $wynik['#__xbbookreviews'].' reviews, ';}
			if ($wynik['#__xbpersons']>0) { $msg .= $wynik['#__xbpersons'].' people. ';}
			$msg .= ' All assigned to   category.';
			if ($wynik['#__xbbookperson']>0) { $msg .= $wynik['#__xbbookperson'].' people-book links created, ';}
			if ($wynik['skipcnt']>0) { $msg .= $wynik['skipcnt'].' duplicate inserts ignored.'; }
			$msgtype = 'success';
		} else {
			$msg = 'Nothing to import. '.$wynik['skipcnt'].' duplicate inserts found:';
			$msgtype = 'info';
		}
		$msg .= $wynik['mess'];
		$this->setRedirect($link, $msg, $msgtype);
	}
	
	function delete() {
		$msg = '';
		$msgtype = 'success';
		$jinput = Factory::getApplication()->input;
		$post   = $jinput->get('jform', 'array()', 'ARRAY');
		$model = $this->getModel('importexport');
		$link = 'index.php?option=com_xbbooks&view=importexport';

		$statelist = '';
		if ($post['deltype']>2) {
		    if ($post['delallitems']==1){
		        $statelist = '-2,0,1,2';
            } elseif (key_exists('delstate',$post)) {
                $statelist = implode(',',$post['delstate']);
            }
		} 
		
		switch ($post['deltype']) {
			case 1:
			    if (key_exists('delstate',$post)) {
			        $statelist = implode(',',$post['delstate']);
			    }
				$msg = $model->cleanData($post, $statelist); 
				break;
			case 2:
				$msg = $model->clearData();
				break;
			case 3:
				$msg = $model->deleteBooks($post['delcat'], $statelist, $post['delrevs']);
				break;
			case 4:
				$msg = $model->deletePeople($post['delpcat'], $statelist);
				break;
			case 5:
				$msg = $model->deleteReviews($post['delcat'], $statelist);
				break;
			case 6:
				$msg = $model->deleteCharacters($post['delpcat'], $statelist);
				break;
			default:
				$msg = Text::_( 'XBCULTURE_SELECT_DELETE_TYPE' );
				$msgtype = 'error';
				break;
		}
		$this->setRedirect($link,$msg,$msgtype);
	}
	
}
