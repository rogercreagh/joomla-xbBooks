<?php
/*******
 * @package xbBooks
 * @filesource admin/helpers/xbbooks.php
 * @version 0.9.9.9 2nd November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
//use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Filter\OutputFilter;


class XbbooksHelper extends ContentHelper
{
    //static $extension = 'com_xbbooks';

	public static function getActions($component = 'com_xbbooks', $section = 'component', $categoryid = 0) {
	    
	    $user 	= Factory::getUser();
	    $result = new JObject;
	    if (empty($categoryid)) {
	        $assetName = $component;
	        $level = $section;
	    } else {
	        $assetName = $component.'.category.'.(int) $categoryid;
	        $level = 'category';
	    }
	    $actions = Access::getActions('com_xbbooks', $level);
	    foreach ($actions as $action) {
	        $result->set($action->name, $user->authorise($action->name, $assetName));
	    }
	    return $result;
	}
	
	public static function addSubmenu($vName = 'books') {
		if ($vName != 'categories') {
			JHtmlSidebar::addEntry(
	            Text::_('XBCULTURE_ICONMENU_DASHBOARD'),
	            'index.php?option=com_xbbooks&view=dashboard',
	            $vName == 'dashboard'
	        );
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_ICONMENU_BOOKS'),
			    'index.php?option=com_xbbooks&view=books',
			    $vName == 'books'
			    );
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_ICONMENU_NEWBOOK'),
			    'index.php?option=com_xbbooks&view=book&layout=edit',
			    $vName == 'book'
			    );
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_ICONMENU_REVIEWS'),
			    'index.php?option=com_xbbooks&view=reviews',
			    $vName == 'reviews'
			    );
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_ICONMENU_NEWREVIEW'),
			    'index.php?option=com_xbbooks&view=review&layout=edit',
			    $vName == 'review'
			    );
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_PEOPLE'),
				'index.php?option=com_xbbooks&view=persons',
				$vName == 'persons'
				);
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_NEWPERSON'),
				'index.php?option=com_xbbooks&view=person&layout=edit',
				$vName == 'person'
				);
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_CHARS'),
				'index.php?option=com_xbbooks&view=characters',
				$vName == 'characters'
				);
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_NEWCHAR'),
				'index.php?option=com_xbbooks&view=character&layout=edit',
				$vName == 'character'
				);
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_BOOKCATS'),
				'index.php?option=com_xbbooks&view=bcategories',
				$vName == 'bcategories'
				);
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_NEWCAT'),
				'index.php?option=com_categories&view=category&task=category.edit&extension=com_xbbooks',
				$vName == 'category'
				);
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_ICONMENU_EDITCATS'),
			    'index.php?option=com_categories&view=categories&extension=com_xbbooks',
			    $vName == 'categories'
			    );
			if (Factory::getSession()->get('xbpeople_ok')==true) {
			    JHtmlSidebar::addEntry(
			        Text::_('XBCULTURE_ICONMENU_SUBPEOPLECATS'),
			        'index.php?option=com_xbpeople&view=pcategories',
			        $vName == 'pcategories'
			        );
			}
			JHtmlSidebar::addEntry(
	            Text::_('XBCULTURE_ICONMENU_TAGS'),
	            'index.php?option=com_xbbooks&view=tags',
	            $vName == 'tags'
	        	);
			JHtmlSidebar::addEntry(
					Text::_('XBCULTURE_ICONMENU_NEWTAG'),
					'index.php?option=com_tags&view=tag&layout=edit',
					$vName == 'tag'
					);
			JHtmlSidebar::addEntry(
	            Text::_('XBCULTURE_ICONMENU_IMPORTEXPORT'),
	            'index.php?option=com_xbbooks&view=importexport',
	            $vName == 'importexport'
	        );
		} else {
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_BOOKS_DASHBOARD'),
					'index.php?option=com_xbbooks&view=dashboard',
					$vName == 'dashboard'
					);
			
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_BOOKS'),
					'index.php?option=com_xbbooks&view=books',
					$vName == 'films'
					);
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_BOOK_REVIEWS'),
					'index.php?option=com_xbbooks&view=reviews',
					$vName == 'reviews'
					);
			JHtmlSidebar::addEntry(
			    Text::_('XBCULTURE_BOOKS_CAT_CNTS'),	
					'index.php?option=com_xbbooks&view=bcategories',
					$vName == 'bcategories'
					);
		}
	}
    
    public static function getBookTitleById($id) {
        $db = Factory::getDBO();
        $query = $db->getQuery(true);      
        $query->select('title')
            ->from('#__xbbooks')
            ->where('id = '. (int) $id);
        $db->setQuery($query);
        return $db->loadResult();       
    }
       
    public static function getCat($catid) {
    	$db = Factory::getDBO();
    	$query = $db->getQuery(true);
    	$query->select('a.title, a.description')
    	->from('#__categories AS a ')
    	->where('a.id = '.$catid);
    	$db->setQuery($query);
    	return $db->loadObjectList()[0];
    }
    
    public static function getColCounts($srcarr,$col) {
    	return array_count_values(array_column($srcarr, $col));
    }
    
    /**
     * @name getItemCnt
     * @desc returns the number of items in a table
     * @param string $table
     * @return integer
     */
    public static function getItemCnt($table) {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')->from($db->quoteName($table));
        $db->setQuery($query);
        $cnt=-1;
        try {
            $cnt = $db->loadResult();
        } catch (Exception $e) {
            $dberr = $e->getMessage();
            Factory::getApplication()->enqueueMessage($dberr.'<br />Query: '.$query, 'error');
        }
        return $cnt;
    }
       
    public static function checkPersonExists($firstname, $lastname) {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')->from('#__xbpersons')
        ->where('LOWER('.$db->quoteName('firstname').')='.$db->quote(strtolower($firstname)).' AND LOWER('.$db->quoteName('lastname').')='.$db->quote(strtolower($lastname)));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res > 0) {
            return true;
        }
        return false;
    }
    
    public static function checkTitleExists($title, $table) {
        $col = ($table == '#__xbcharacters') ? 'name' : 'title';
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id')->from($db->quoteName($table))
        ->where('LOWER('.$db->quoteName($col).')='.$db->quote(strtolower($title)));
        $db->setQuery($query);
        $res = $db->loadResult();
        if ($res > 0) {
            return true;
        }
        return false;
    }
    
}
