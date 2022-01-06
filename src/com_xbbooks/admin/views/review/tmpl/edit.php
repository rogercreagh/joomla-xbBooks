<?php
/*******
 * @package xbBooks
 * @filesource admin/views//tmpl/edit.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
HtmlHelper::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => JText::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
HtmlHelper::_('formbehavior.chosen', 'select');

?>
<form action="<?php echo JRoute::_('index.php?option=com_xbbooks&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
	<?php if(empty($this->item->id)) : ?>
		<p><i>If you leave title, summary and synopsis blank this will be treated as a quick rating only</i></p>
	<?php endif; ?>
	<div class="row-fluid">
		<div class="span11">
			<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
		</div>
		<div class="span1"><?php echo $this->form->renderField('id'); ?></div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<?php echo $this->form->renderField('book_id'); ?>
	 		<?php echo $this->form->renderField('rating'); ?>
		</div>
		<div class="span6">
			<?php echo $this->form->renderField('summary'); ?>
		</div>
	</div>     
    <div class="row-fluid">
      <div class="span12">
		<?php echo HtmlHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_XBBOOKS_FIELDSET_GENERAL')); ?>
			<div class="span9">
				<fieldset class="adminform">
					<div class="row-fluid">				
     					<div class="span12">
    						<fieldset class="form-vertical">
                				<?php echo $this->form->renderField('review'); ?>
                			</fieldset>
    					</div>
					</div>
				</fieldset>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                 				<?php echo $this->form->renderField('reviewer'); ?>
                 				<?php echo $this->form->renderField('rev_date'); ?>
			</div>
		<?php echo HtmlHelper::_('bootstrap.endTab'); ?>
		<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_XBBOOKS_FIELDSET_PUBLISHING')); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
		<?php echo HtmlHelper::_('bootstrap.endTab'); ?>
	</div>
  </div>
    <input type="hidden" name="task" value="review.edit" />
    <?php echo HtmlHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbbooksGeneral::credit();?></p>
