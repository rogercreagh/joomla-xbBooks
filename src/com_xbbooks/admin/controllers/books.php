<?php
/*******
 * @package xbBooks
 * @filesource admin/controllers/books.php
 * @version 0.2.9 2nd June 2020
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

class XbbooksControllerBooks extends JControllerAdmin {

    public function getModel($name = 'Books', $prefix = 'XbbooksModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config );
        return $model;
    }

}
