<?php
/*******
 * @package xbBooks
 * @filesource admin/views/person/tmpl/qnew.php
 * @version 0.8.2 16th March 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => JText::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
JHtml::_('formbehavior.chosen', 'select');

?>
<div class="xbml20 xbmr20">
<form action="<?php echo JRoute::_('index.php?option=com_xbbooks&layout=qnew&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
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
			<?php echo $this->form->renderField('catid'); ?> 
		</div>
		<div class="span4">
			<?php echo $this->form->renderField('state'); ?>
		</div>
		<div class="span4">
			<?php echo $this->form->renderField('tags'); ?>
		</div>
    </div>
    
    <input type="hidden" name="task" value="person.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>
