<?php
/*******
 * @package xbBooks
 * @filesource admin/views/person/tmpl/qnew.php
 * @version 0.9.8.3 25th May 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => JText::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
HtmlHelper::_('formbehavior.chosen', 'select');

?>
<div class="xbml20 xbmr20">
<form action="<?php echo Route::_('index.php?option=com_xbbooks&layout=qnew&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
 <!--   <div class="xbbox xbboxred"><b>Did you save the book data?</b> - any unsaved changes will be lost when you save this person.</div>  --> 
    <div class="row-fluid">
    	<div class="span12">
    		<div class="row-fluid form-vertical">
        		<div class="pull-left" >
             		<?php echo $this->form->renderField('firstname'); ?>
        		</div>
        		<div class="pull-left xbml15">
            		<?php echo $this->form->renderField('lastname'); ?>
        		</div>
           	</div>
        </div>
    </div>
    <div class="row-fluid">
    	<div class="span4">
			<?php echo $this->form->renderField('state'); ?> 
		</div>
		<div class="span4">
			<?php echo $this->form->renderField('year_born'); ?>
			<?php echo $this->form->renderField('year_died'); ?>
		</div>
		<div class="span4">
			<?php echo $this->form->renderField('nationality'); ?>
		</div>
    </div>
    
    <input type="hidden" name="task" value="person.edit" />
    <?php echo HtmlHelper::_('form.token'); ?>
</form>
