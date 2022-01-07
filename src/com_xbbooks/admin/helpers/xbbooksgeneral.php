<?php
/*******
 * @package xbBooks
 * @filesource admin/helpers/xbbooksgeneral.php
 * @version 0.9.6.d 7th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
// use Joomla\CMS\HTML\HTMLHelper;
// use Joomla\CMS\Application\ApplicationHelper;
// use Joomla\CMS\Component\ComponentHelper;

//class for methods used by both site and admin

class XbbooksGeneral {
            
    /**
     * @name getBookReviews()
     * @desc Gets an object list of reviews given a film id
     * @param int $film required
     * @return object list
     */
    public static function getBookReviews(int $book) {
        $ord = 'rev_date'; $dir='ASC'; //oldest first
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('r.*,c.title AS category_title')
        ->from('#__xbbookreviews AS r')
        ->join('LEFT','#__categories AS c ON c.id = r.catid')
        ->where('r.book_id = "'.$book.'"' )
        ->where('r.state = '.$db->quote('1'));
        $query->order($db->escape($ord.' '.$dir));
        $db->setQuery($query);
        $revs = $db->loadObjectList();
        $tagsHelper = new TagsHelper;
        foreach ($revs as $r) {
            $r->tags = $tagsHelper->getItemTags('com_xbbooks.review' , $r->id);
            $r->tagcnt = count($r->tags);
        }
        return $revs;
    }
    
    /**
     * @name makeLinkedNameList
     * @param array $arr required - array of details to turn into list
     * @param string $role default'' - filter by role type
     * @param string $sep default ',' - separtor between list elements (eg <br />)
     * @param boolean $linked default true - if true use linked names to detail view (set false for use in tooltips)
     * @param boolean $amp default true - if true and list is only two people used ampersand as separator
     * @param boolean $note default 0 - 1 = prepend role_note as itallics in span minwidth 60px, 2 = append the role_note to the name in brackets
     * @return string
     */
    public static function makeLinkedNameList($arr, $role='', $sep=', ', $linked=true, $amp = true, $note = 0) {
        $wynik = '';
        $cnt = 0;
        foreach ($arr as $item) {
            if (($role=='') || ($role == $item->role)) {
                if($note==1) {
                    $wynik .= '<span class="xbnit xbsp60">'.$item->role_note.':</span> ';
                 }
                $wynik .= ($linked) ? $item->link : $item->display;
                if (($note==2) && ($item->role_note !='')) {
                    $wynik .= ' ('.$item->role_note.')';
                }                   
                $wynik .= $sep;
                $cnt++;
            }
        }
        //strip off final separator which could be a string so can't use trim
        if (substr($wynik,-strlen($sep))===$sep) $wynik = substr($wynik, 0, strlen($wynik)-strlen($sep));
        //if it is a comma list with only two items then we might use & rather than ,
        if (($cnt==2) && (trim($sep)==',') && $amp) {
            $wynik = str_replace($sep,' &amp; ',$wynik);
        }
        return trim($wynik);
    }
    
    /**
     * @name getBookRolesArray
     * @desc for given book returns and array of person names and roles
     * @param int $bookid
     * @param string $role - if not blank only get the specified role
     * @param boolean $edit - link to site/admin page
     * @return array
     */
    public static function getBookRolesArray(int $bookid, $role='',$edit=false) {
    	$link = 'index.php?option=com_xbbooks';
		$link .= $edit ? '&task=person.edit&id=' : '&view=person&id=';
    	$db = Factory::getDBO();
    	$query = $db->getQuery(true);
    	
    	$query->select('a.role, a.role_note, p.firstname, p.lastname, p.id, p.state AS pstate')
    	->from('#__xbbookperson AS a')
    	->join('LEFT','#__xbpersons AS p ON p.id=a.person_id')
    	->join('LEFT','#__xbbooks AS b ON b.id=a.book_id')
    	->where('a.book_id = "'.$bookid.'"' )
    	->order('a.book_id, a.listorder', 'ASC');
    	$query->order(array('a.role','a.listorder ASC','p.lastname'));
    	if (!empty($role)) {
    		$query->where('a.role = "'.$role.'"');
    	}
    	
    	$db->setQuery($query);
    	$list = $db->loadObjectList();
    	foreach ($list as $i=>$item){
    		$ilink = Route::_($link . $item->id);
    		//handle no firstname
    		$name = ($item->firstname !='') ? $item->firstname.' ' : '';
    		$name .= $item->lastname;
    		$item->display = '';
    		if ($role == 'other') {
    			$item->display = '<i>'.ucfirst($item->role_note).'</i>: ';
    		}
    		//if not published highlight in yellow if editable or grey if view
    		if ($item->pstate != 1) {
    			$flag = $edit ? 'xbhlt' : 'xbdim';
    			$item->display .= '<span class="'.$flag.'">'.$name.'</span>';
    		} else {
    			$item->display .= $name;
    		}
    		//if item not published only link if to edit page
    		if (($edit) || ($item->pstate == 1)) {
    			$item->link = '<a href="'.$ilink.'">'.$item->display.'</a>';
    		} else {
    			$item->link = $item->display;
    		}
    	}
    	return $list;
    }
    
    public static function getBookCharsArray($bookid) {
        $admin = Factory::getApplication()->isClient('administrator');
        $link = 'index.php?option=com_xbbooks'. ($admin) ? '&task=character.edit&id=' : '&view=character&id=';
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        
        $query->select('c.name, c.id, c.state AS chstate, a.char_note')
        ->from('#__xbbookcharacter AS a')
        ->join('LEFT','#__xbcharacters AS c ON c.id=a.char_id')
        ->where('a.book_id = "'.$bookid.'"' );
        if (!$admin) {
            $query->where('c.state = 1');
        }
        $query->order('a.book_id, a.listorder', 'ASC'); //need to join books to order by title
        try {
            $db->setQuery($query);
            $list = $db->loadObjectList();
        } catch (Exception $e) {
            return '';
        }
        foreach ($list as $i=>$item){
            $ilink = Route::_($link . $item->id);
            if ($item->chstate != 1) {
                $item->display = '<span style="background:yellow;">'.$item->name.'</span>';
            } else {
                $item->display = $item->name;
            }
            $item->link = '<a href="'.$ilink.'">'.$item->display.'</a>';
        }
        return $list;
    }
    
    /**
     * @name getPersonRoleArray
     * @desc for given person returns and array of books and roles
     * @param int $personid
     * @param string $role - if not blank only get the specified role
     * @param boolean $edit - link to site/admin page
     * @return array
     */
    public static function getPersonRoleArray(int $personid, $role='',$edit=false) {
        $link = 'index.php?option=com_xbbooks';
        $link .= $edit ? '&task=book.edit&id=' : '&view=book&id=';
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        
        $query->select('a.role, a.role_note, b.title, b.subtitle, b.pubyear, b.id, b.state AS bstate')
        ->from('#__xbbookperson AS a')
        ->join('LEFT','#__xbbooks AS b ON b.id=a.book_id')
        ->where('a.person_id = "'.$personid.'"' )
        ->order('b.pubyear DESC, b.title', 'ASC');
        if (!empty($role)) {
            $query->where('a.role = "'.$role.'"');
        }
        $db->setQuery($query);
        $list = $db->loadObjectList();
        foreach ($list as $i=>$item){
            $tlink = Route::_($link . $item->id);
            //if not published highlight in yellow if editable or grey if view
            if ($item->bstate != 1) {
            	$flag = $edit ? 'xbhlt' : 'xbdim';
                $item->display = '<span class="'.$flag.'">'.$item->title.'</span>';
            } else {
                $item->display = $item->title;
            }
            //if item not published only link if to edit page
            if (($edit) || ($item->bstate == 1)) {
            	$item->link = '<a href="'.$tlink.'">'.$item->display.'</a>';
            } else {
            	$item->link = $item->display;
            }
        }
        return $list;
    }        
    
    public static function credit() {
        	if (XbcultureHelper::penPont()) {
        		return '';
        	}
		$credit='<div class="xbcredit">';
		if (Factory::getApplication()->isClient('administrator')==true) {
			$xmldata = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR.'/components/com_xbbooks/xbbooks.xml');
			$credit .= '<a href="http://crosborne.uk/xbbooks" target="_blank">
                xbBooks Component '.$xmldata['version'].' '.$xmldata['creationDate'].'</a>';
			$credit .= '<br />'.Text::_('XBCULTURE_BEER_TAG');
			$credit .= Text::_('XBCULTURE_BEER_FORM');
		} else {
			$credit .= 'xbBooks by <a href="http://crosborne.uk/xbbooks" target="_blank">CrOsborne</a>';
		}
		$credit .= '</div>';
		return $credit;   	
    }
    
}