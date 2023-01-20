<?php
/*******
 * @package xbBooks
 * @filesource site/helpers/xbbooks.php
 * @version 1.0.3.5 19th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

class XbbooksHelperRoute
{
    public static function &getItems() {
        static $items;
        
        // Get the menu items for this component.
        if (!isset($items)) {
            $component = ComponentHelper::getComponent('com_xbbooks');
            $items     = Factory::getApplication()->getMenu()->getItems('component_id', $component->id);
            // If no items found, set to empty array.
            if (!$items) {
                $items = array();
            }
        }       
        return $items;
    }
    
    /**
     * @name getBooksRoute
     * @desc Get menu itemid booklist view in default layout
     * @param boolean $retstr if false return integer id, if true return return string with "&Itemid="
     * @return string|int|NULL
     */
    public static function getBooksRoute($retstr=false) {
        $items  = self::getItems();
        foreach ($items as $item) {
            if ((isset($item->query['view']) && $item->query['view'] === 'booklist')
                && ((empty($item->query['layout']) || $item->query['layout'] === 'default')) ) {
                    return ($retstr)? '&Itemid='.$item->id : $item->id;
                }
        }
        return null;
    }
    
    /**
     * @name getBooksLink
     * @desc Get link to books view
     * @return string
     */
    public static function getBooksLink() {
        $blink = 'index.php?option=com_xbbooks';
        $items  = self::getItems();
        foreach ($items as $item) {
            if ((isset($item->query['view']) && $item->query['view'] === 'booklist')
                && ((empty($item->query['layout']) || $item->query['layout'] === 'default')) ) {
                    return $blink.'&Itemid='.$item->id;
                }
        }
        return $flink.'&view=booklist';
    }
    
    /**
     * @name getBooksCompactRoute
     * @desc Get menu itemid booklist view in default layout
     * @param boolean $retstr if false return integer id, if true return return string with "&Itemid="
     * @return string|int|NULL
     */
    public static function getBooksCompactRoute($retstr=false) {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'booklist' && $item->query['layout'] === 'compact') {
                return ($retstr)? '&Itemid='.$item->id : $item->id;
            }
        }
        return null;
    }
    
    /**
     * @name getBooksCompactLink
     * @desc Get link to compact books view
     * @return string
     */
    public static function getBooksCompactLink() {
    	$blink = 'index.php?option=com_xbbooks';
    	$items  = self::getItems();
    	foreach ($items as $item) {
    		if (isset($item->query['view']) && $item->query['view'] === 'booklist'
    				&& $item->query['layout'] === 'compact' ) {
    					return $blink.'&Itemid='.$item->id;
    				}
    	}
    	return $blink.'&view=booklist&layout=compact';
    }
    
    /**
     * @name getBookRoute
     * @desc returns the itemid for a menu item for book view with id  $fd, if not found returns menu id for a booklist, if not found null
     * @param int $fid
     * @return int|string|NULL
     */
    public static function getBookRoute($fid) {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'book' && isset($item->query['id']) && $item->query['id'] == $fid ) {
                return $item->id;
            }
        }
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'booklist' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id.'&view=book&id='.$fid;
                }
        }
        return null;
    }
    
    /**
     * @name getBookLink
     * @desc gets a comlete link for a book menu item either dedicated, or booklist menu or generic
     * @param int $fid
     * @return string
     */
    public static function getBookLink($fid) {
        $flink = 'index.php?option=com_xbbooks';
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'book' && isset($item->query['id']) && $item->query['id'] == $fid ) {
                return $flink.'&Itemid='.$item->id;
            }
        }
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'booklist' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $flink.'&Itemid='.$item->id.'&view=book&id='.$fid;
                }
        }
        return $flink.'&view=book&id='.$fid;
    }
    
    public static function getBlogRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'blog' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
    public static function getReviewsRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'books' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
    public static function getPeopleRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'people' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
    public static function getGroupsRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'groups' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
    public static function getCharsRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'characters' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
    public static function getCategoriesRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'categories' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
    public static function getTagsRoute() {
        $items  = self::getItems();
        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] === 'tags' &&
                (empty($item->query['layout']) || $item->query['layout'] === 'default')) {
                    return $item->id;
                }
        }
        return null;
    }
    
}
