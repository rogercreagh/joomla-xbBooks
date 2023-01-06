<?php
/*******
 * @package xbBooks
 * @filesource admin/models/dashboard.php
 * @version 1.0.1.1 31st December 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
//use Joomla\CMS\Table\Observer\Tags;

class XbbooksModelDashboard extends JModelList {

    public function __construct() {
        parent::__construct();
    }  
    
	public function getBookStates() {
		return $this->stateCnts('#__xbbooks');
	}
	
	public function getCatStates() {
		return $this->stateCnts('#__categories','published','com_xbbooks');
	}
	
	public function getRevStates() {
		return $this->stateCnts('#__xbbookreviews');
	}
	
	public function getPerStates() {
	    return $this->stateCnts('#__xbpersons','state','com_xbpeople');
	}
	
	public function getGroupStates() {
	    return $this->stateCnts('#__xbgroups','state','com_xbpeople');
	}
	
	public function getCharStates() {
	    return $this->stateCnts('#__xbcharacters','state','com_xbpeople');
	}
	
	public function getBookCnts() {
        $books = array();
        $db = $this->getDbo();
        
        $query = $db->getQuery(true);
        $query->select('fiction')->from('#__xbbooks');
        $db->setQuery($query);
        $col = $db->loadColumn();
        $vals = array_count_values($col);       
        $books['nonfiction'] = key_exists('0',$vals) ? $vals['0'] : 0;
        $books['fiction'] = key_exists('1',$vals) ? $vals['1'] : 0 ;
        
        $query =$db->getQuery(true);
        //nedd to exclude orphans
        $query->select('COUNT(DISTINCT book_id)')->from('#__xbbookreviews AS r');
        $query->join('LEFT', '#__xbbooks AS b ON b.id = r.book_id');
        $query->where('b.id IS NOT NULL');
        $db->setQuery($query);
        $books['reviewed'] = $db->loadResult();
        
        return $books;
    }
        
    public function getCats() {
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);
    	$query->select('a.*')
    	->select('(SELECT COUNT(*) FROM #__xbbooks AS b WHERE b.catid=a.id) AS bookcnt')
    	->select('(SELECT COUNT(*) FROM #__xbbookreviews AS r WHERE r.catid=a.id) AS revcnt')
    	->from('#__categories AS a')
    	->where('a.extension = '.$db->quote("com_xbbooks"))
    	->order($db->quoteName('path') . ' ASC');
    	$db->setQuery($query);
    	return $db->loadAssocList('alias');    	
    }
    
    public function getRoleCnts() {
        $result = array();
        $wynik = $this->roleCnts('author');
        $result['authpub'] = $wynik['pub'];
        $result['authunpub'] = $wynik['unpub'];
        
        $wynik = $this->roleCnts('editor');
        $result['editpub'] = $wynik['pub'];
        $result['editunpub'] = $wynik['unpub'];
        
        $wynik = $this->roleCnts('mention');
        $result['menpub'] = $wynik['pub'];
        $result['menunpub'] = $wynik['unpub'];
        
        $wynik = $this->roleCnts('other_roles');
        $result['otherpub'] = $wynik['pub'];
        $result['otherunpub'] = $wynik['unpub'];
        
        
        return $result;
    }
    
    public function getRatCnts() {
    	$db = $this->getDbo();
    	$query =$db->getQuery(true);
    	$query->select('rating');
    	$query->from('#__xbbookreviews');
    	$query->where('state = 1');
    	$db->setQuery($query);
    	return array_count_values($db->loadColumn());
    }
    
    public function getClient() {
    	$result = array();
    	$client = Factory::getApplication()->client;
    	$class = new ReflectionClass('Joomla\Application\Web\WebClient');
    	$constants = array_flip($class->getConstants());
    	
    	$result['browser'] = $constants[$client->browser].' '.$client->browserVersion;
    	$result['platform'] = $constants[$client->platform].($client->mobile ? ' (mobile)' : '');
    	$result['mobile'] = $client->mobile;
    	return $result;   	
    }
    
    public function getTagcnts() {
        //we need number of books tagged, number of reviews tagged, number of tags used for films, number of tags used for reviews
        // people tagged, chars tagged, people tags, char tags
        $result = array('bookscnt' => 0, 'revscnt' =>0, 'booktags' => 0, 'revtags' => 0,
            'bookper' => 0, 'bookchar' => 0, 'bookpertags' => 0, 'bookchartags' => 0 );
        
        $result['bookscnt'] = XbcultureHelper::getTagtypeItemCnt('com_xbbooks.book','');
        $result['revscnt'] = XbcultureHelper::getTagtypeItemCnt('com_xbbooks.review','');
        $result['booktags']= XbcultureHelper::getTagtypeTagCnt('com_xbbooks.book','');
        $result['revtags']= XbcultureHelper::getTagtypeTagCnt('com_xbbooks.review','');
        $result['bookper'] = XbcultureHelper::getTagtypeItemCnt('com_xbpeople.person','book');
        $result['bookchar'] = XbcultureHelper::getTagtypeItemCnt('com_xbpeople.character','book');
        $result['bookpertags']= XbcultureHelper::getTagtypeTagCnt('com_xbpeople.person','book');
        $result['bookchartags']= XbcultureHelper::getTagtypeTagCnt('com_xbpeople.character','book');
        return $result;
    }
    
    /**
     * @name getOtherRoles()
     * @desc get an array of other roles in bookperson table
     * @return array
     */
    public function getOtherRoles() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('DISTINCT role_note')->from($db->quoteName('#__xbbookperson'))->where('role = '.$db->quote('other'));
        $db->setQuery($query);
        $res=array();
        try {
            $res = $db->loadColumn();
        } catch (Exception $e) {
            $dberr = $e->getMessage();
            Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
        }
        return $res;       
    }
    
	private function stateCnts(string $table, string $colname = 'state', string $ext='com_xbbooks') {
	    $db = $this->getDbo();
	    $query = $db->getQuery(true);
	    $query->select('DISTINCT a.'.$colname.', a.alias')
	    ->from($db->quoteName($table).' AS a');
	    if ($table == '#__categories') {
	        $query->where('extension = '.$db->quote($ext));
	    }
	    if ($table == '#__xbpersons') {
	        $query->join('LEFT','#__xbbookperson AS bp ON bp.person_id = a.id')->where('bp.id IS NOT NULL');
	    }
	    if ($table == '#__xbcharacters') {
	        $query->join('LEFT','#__xbbookcharacter AS bc ON bc.char_id = a.id')->where('bc.id IS NOT NULL');
	    }
	    $db->setQuery($query);
	    $col = $db->loadColumn();
	    $vals = array_count_values($col);
	    $result['total'] = count($col);
	    $result['published'] = key_exists('1',$vals) ? $vals['1'] : 0;
	    $result['unpublished'] = key_exists('0',$vals) ? $vals['0'] : 0;
	    $result['archived'] = key_exists('2',$vals) ? $vals['2'] : 0;
	    $result['trashed'] = key_exists('-2',$vals) ? $vals['-2'] : 0;
	    return $result;
	}

	private function roleCnts($role='') {
		$wynik = array();
		$db = $this->getDbo();
		$exclude = "('author','editor','mention')";
		
		$query = $db->getQuery(true);
		$query->select('COUNT(DISTINCT a.id) AS pcnt')
		->from($db->quoteName('#__xbpersons','a'));
		if (!empty($role)) {
			if ($role == 'other_roles') {
				$query->join('LEFT',$db->quoteName('#__xbbookperson','b').' ON '.$db->quoteName('b.person_id').' = '.$db->quoteName('a.id'))
				->where('b.role NOT IN '.$exclude);
			} else {
				$query->join('LEFT',$db->quoteName('#__xbbookperson','b').' ON '.$db->quoteName('b.person_id').' = '.$db->quoteName('a.id'))
				->where('b.role='.$db->quote($role));
			}
		}
		$query->where('a.state=1');
		$db->setQuery($query);
		$db->execute();
		$wynik['pub'] = $db->loadResult();
		
		$query = $db->getQuery(true);
		$query->select('COUNT(DISTINCT a.id) AS pcnt')
		->from('#__xbpersons AS a');
		if (!empty($role)) {
			if ($role == 'other_roles') {
				$query->join('LEFT',$db->quoteName('#__xbbookperson','b').' ON '.$db->quoteName('b.person_id').' = '.$db->quoteName('a.id'))
				->where('b.role NOT IN '.$exclude);
			} else {
				$query->leftJoin('#__xbbookperson AS b ON a.id = b.person_id')
				->where('b.role='.$db->quote($role));
			}
		}
		$query->where('a.state!=1');
		$db->setQuery($query);
		$db->execute();
		$wynik['unpub'] = $db->loadResult();
		
		return $wynik;
	}
			
}	
