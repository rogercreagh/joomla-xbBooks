<?php
/*******
 * @package xbBooks
 * @filesource admin/helpers/xbbooks.php
 * @version 0.9.6.a 16th December 2021
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
	            Text::_('XBCULTURE_ICONMENU_CPANEL'),
	            'index.php?option=com_xbbooks&view=cpanel',
	            $vName == 'cpanel'
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
			JHtmlSidebar::addEntry(
				Text::_('XBCULTURE_ICONMENU_OPTIONS'),
				'index.php?option=com_config&view=component&component=com_xbbooks',
				$vName == 'options'
				);
		} else {
			JHtmlSidebar::addEntry(
					Text::_('xbBooks Dashboard'),
					'index.php?option=com_xbbooks&view=cpanel',
					$vName == 'cpanel'
					);
			
			JHtmlSidebar::addEntry(
					Text::_('Books'),
					'index.php?option=com_xbbooks&view=books',
					$vName == 'films'
					);
			JHtmlSidebar::addEntry(
					Text::_('Reviews'),
					'index.php?option=com_xbbooks&view=reviews',
					$vName == 'reviews'
					);
			JHtmlSidebar::addEntry(
					Text::_('Book Cat.Counts'),
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
    
    public static function getIdFromAlias($table,$alias, $ext = 'com_xbbooks') {
        $alias = trim($alias,"' ");
        $table = trim($table,"' ");
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id')->from($db->quoteName($table))->where($db->quoteName('alias')." = ".$db->quote($alias));
        if ($table === '#__categories') {
            $query->where($db->quoteName('extension')." = ".$db->quote($ext));
        }
        $db->setQuery($query);
        $res =0;
        $res = $db->loadResult();
        return $res;
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
    
    /**
     * @name createCategory
     * @param string $name - name of category to create
     * @param string $alias - alias if not to be derived from name
     * @param string $ext - component to own category
     * @param string $desc - optional description
     * @return false or existing category id (if it exists) on new id if created
     */
    public static function createCategory($name, $alias='', $ext='com_xbbooks', $desc='') {
    	if ($alias=='') {
    		//create alias from name
    		$alias = OutputFilter::stringURLSafe(strtolower($name));    		
    	}
    	//check category doesn't already exist
    	$db = Factory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('id')->from($db->quoteName('#__categories'))->where($db->quoteName('alias')." = ".$db->quote($alias));
    	$query->where($db->quoteName('extension')." = ".$db->quote($ext));
    	$db->setQuery($query);
    	$id =0;
    	$res = $db->loadResult();
    	if ($res>0) {
    		return $res;
    	}
    	//get category model
    	$basePath = JPATH_ADMINISTRATOR.'/components/com_categories';
    	require_once $basePath.'/models/category.php';
    	$config  = array('table_path' => $basePath.'/tables');
    	//setup data for new category
    	$category_model = new CategoriesModelCategory($config);
    	$category_data['id'] = 0;
    	$category_data['parent_id'] = 0;
    	$category_data['published'] = 1;
    	$category_data['language'] = '*';
    	$category_data['params'] = array('category_layout' => '','image' => '');
    	$category_data['metadata'] = array('author' => '','robots' => '');
    	$category_data['extension'] = $ext;
    	$category_data['title'] = $name;
    	$category_data['alias'] = $alias;
		$category_data['description'] = $desc;
    	if(!$category_model->save($category_data)){
    		Factory::getApplication()->enqueueMessage('Error creating category: '.$category_model->getError(), 'error');
    		return false;
    	}
    	$id = $category_model->getItem()->id;    	
    	return $id;
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
