<?php
/*******
 * @package xbBooks
 * @filesource site/helpers/xbbooks.php
 * @version 0.9.9.3 13th July 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

class XbbooksHelper {
	
	public static function getCharacterBooksArray($personid) {
		$link = 'index.php?option=com_xbbooks&view=book&id=';
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select('b.title, b.subtitle, b.pubyear, b.id')
		->from('#__xbbookcharacter AS a')
		->join('LEFT','#__xbbooks AS b ON b.id=a.book_id')
		->where('a.char_id = "'.$personid.'"' )
		->order('b.pubyear, b.title', 'ASC');
		$db->setQuery($query);
		$list = $db->loadObjectList();
		foreach ($list as $i=>$item){
			$tlink = Route::_($link . $item->id);
			$item->link = '<a href="'.$tlink.'">'.$item->title.'</a>';
			$item->display = $item->title;
		}
		return $list;
	}
	
/* 		
	public static function getChildCats($pid, $ext, $incroot = true) {
 		$childarr = array();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__categories')->where('parent_id = '.$db->quote($pid));
		$db->setQuery($query);
		$children = $db->loadColumn();
		if ($children) {
			$childarr = array_merge($childarr,$children);
			foreach ($children as $child){
				$gch = self::getChildCats($child);
				if ($gch) { $childarr = array_merge($childarr, $gch);}
			}
			return $childarr;
		}
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__categories')->where('id='.$pid);
		$db->setQuery($query);
		$pcat=$db->loadObject();
		$start = $incroot ? '>=' : '>';
		$query->clear();
		$query->select('id')->from('#__categories')->where('extension = '.$db->quote($ext));
		$query->where(' lft'.$start.$pcat->lft.' AND rgt <='.$pcat->rgt);
		$db->setQuery($query);
		return $db->loadColumn();
	}
	
	public static function sitePageheader($displayData) {
		$header ='';
		if (!empty($displayData)) {
			$header = '	<div class="row-fluid"><div class="span12 xbpagehead">';
			if ($displayData['showheading']) {
				$header .= '<div class="page-header"><h1>'.$displayData['heading'].'</h1></div>';
			}
			if ($displayData['title'] != '') {
				$header .= '<h3>'.$displayData['title'].'</h3>';
				if ($displayData['subtitle']!='') {
					$header .= '<h4>'.$displayData['subtitle'].'</h4>';
				}
				if ($displayData['text'] != '') {
					$header .= '<p>'.$displayData['text'].'</p>';
				}
			}
		}
		return $header;
	}
 */		

	/**
	 * @name makeSummaryText
	 * @desc returns a plain text version of the source trunctated at the sentence before the specified length
	 * @param string $source
	 * @param int $len
	 * @return string
	 */
/* 	public static function makeSummaryText(string $source, int $len) {
	    //firs strip any html
	    $clean = strip_tags($source);
	    //if we have a length truncate at word break before it
	    if ($len>0) {
	        $summary = HTMLHelper::_('string.truncate', $clean, $len, true, false);
	        //strip off ellipsis if present
	        if (substr($summary,strlen($summary)-3) == '...') {
	            $summary = substr($summary,0,strlen($summary)-4);
	        }
	    }
	    //look for the last . ! ?
	    $breaks=array(strrpos($summary, "."), strrpos($summary, "!"), strrpos($summary, "?"));
	    $end = max($breaks);
	    if ($end>0) {
	        $summary = substr($summary,0, $end+1);
	    }
	    if (strlen($clean) > strlen($summary)) {
	        $summary .= ' ...';
	    }
	    return $summary;
	}
	
 */
}