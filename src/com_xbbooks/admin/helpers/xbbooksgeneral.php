<?php
/*******
 * @package xbBooks
 * @filesource admin/helpers/xbbooksgeneral.php
 * @version 0.9.0 5th April 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;

//class for methods used by both site and admin

class XbbooksGeneral {
        
    /***
     * checkComponent()
     * test whether a component is installed, and if installed whether enabled
     * @param  $name - component name as stored in the extensions table (eg com_xbfilms)
     * @return boolean|number - true= installed and enabled, 0= installed not enabled, false = not installed
    public static function checkComponent($name) {
        $sname=substr($name,4).'_ok';
        $sess= Factory::getSession();
        $db = Factory::getDBO();
        $db->setQuery('SELECT enabled FROM #__extensions WHERE element = '.$db->quote($name));
        $res = $db->loadResult();
        $sess->set($sname,$res);
        return $res;
    }
     */
    
    /**
     * @name makeSummaryText
     * @desc returns a plain text version of the source trunctated at the sentence before the specified length
     * @param string $source
     * @param int $len
     * @return string
    public static function makeSummaryText(string $source, int $len=200, bool $first = false) {
    	if ($len == 0 ) {$len = 100; $first = true; }
    	//first strip any html and truncate to max length
    	$summary = HTMLHelper::_('string.truncate', $source, $len, true, false);
    	//strip off ellipsis if present (we'll put it back at end)
    	$hadellip = false;
    	if (substr($summary,strlen($summary)-3) == '...') {
    		$summary = substr($summary,0,strlen($summary)-3);
    		$hadellip = true;
    	}
    	// get a version with '? ' and '! ' replaced by '. '
    	$dotsonly = str_replace(array('! ','? '),'. ',$summary.' ');
    	if ($first) {
    		// look for first ". " as end of sentence
    		$dot = strpos($dotsonly,'. ');
    	} else {
    		// look for last ". " as end of sentence
    		$dot = strrpos($dotsonly,'. ');
    	}
    	// are we going to cut some more off?)
    	if (($dot!==false) && ($dot < strlen($summary)-3)) {
    		$hadellip = true;
    	}
    	if ($dot>3) {
    		$summary = substr($summary,0, $dot+1);
    	}
    	if ($hadellip) {
    		// put back ellipsis with a space
    		$summary .= ' ...';
    	}
    	return $summary;
    }
     */
    
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
            $tlink = JRoute::_($link . $item->id);
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
//        if (Factory::getSession()->get('xbpeople_ok')) { //(self::checkComponent('com_xbpeople') ) {
//        	require_once JPATH_ADMINISTRATOR.'/components/com_xbpeople/helpers/xbpeople.php';
//        }
        	if (XbcultureHelper::penPont()) {
        		return '';
        	}
		$credit='<div class="xbcredit">';
		if (Factory::getApplication()->isClient('administrator')==true) {
			$xmldata = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR.'/components/com_xbbooks/xbbooks.xml');
			$credit .= '<a href="http://crosborne.uk/xbbooks" target="_blank">
                xbFilms Component '.$xmldata['version'].' '.$xmldata['creationDate'].'</a>';
			$credit .= '<br />'.Text::_('COM_XBCULTURE_BEER_TAG');
			$credit .= Text::_('COM_XBCULTURE_BEER_FORM');
		} else {
			$credit .= 'xbBooks by <a href="http://crosborne.uk/xbbooks" target="_blank">CrOsborne</a>';
		}
		$credit .= '</div>';
		return $credit;   	
    }
    
/*     
    public static function credit() {
        if (self::penPont()) {
            return '';
        } else {
            $xmldata = ApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR.'/components/com_xbbooks/xbbooks.xml');
            $credit='<div class="xbcredit"><a href="http://crosborne.uk/xbbooks" target="_blank">
                xbBooks Component '.$xmldata['version'].' '.$xmldata['creationDate'].'</a>';
            if (Factory::getApplication()->isClient('administrator')==true) {
                $credit .= '<br />Buy Roger a beer to hide this message and support a local brewery! (&pound;4)';
                
                $credit .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="69BAH2Z3TRKYW">
<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!" style="width:120px;">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>';
            }
            $credit .= '</div>';
            return $credit;
        }
    }
    
    private static function penPont() {
        $params = ComponentHelper::getParams('com_xbbooks');
        $beer = trim($params->get('roger_beer'));
//        Factory::getApplication()->enqueueMessage(password_hash($beer.'PASSWORD_DEFAULT'));
        $hashbeer = $params->get('penpont');
        if (password_verify($beer,$hashbeer)) { return true; }
        return false;
    }
 */    
}