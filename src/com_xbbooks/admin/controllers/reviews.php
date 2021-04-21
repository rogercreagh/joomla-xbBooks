<?php
/*******
 * @package xbBooks
 * @filesource admin/controlers/reviews.php
 * @version 0.2.5 23rd May 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

class XbbooksControllerReviews extends JControllerAdmin {
    
    public function getModel($name = 'Reviews', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}