<?php
/*******
 * @package xbBooks
 * @filesource admin/views/review/tmpl/edit.php
 * @version 1.0.2.2 6th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
HtmlHelper::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => JText::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
HtmlHelper::_('formbehavior.chosen', 'select');

?>
<style type="text/css" media="screen">
    .xbpvmodal .modal-body iframe { max-height:calc(100vh - 190px);}
</style>
<form action="<?php echo Route::_('index.php?option=com_xbbooks&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
	<?php if(empty($this->item->id)) : ?>
		<p><i>If you leave title, summary and synopsis blank this will be treated as a quick rating only</i></p>
	<?php endif; ?>
	<div class="row-fluid">
		<div class="span11">
			<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
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

		<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'general', JText::_('XBBOOKS_FIELDSET_GENERAL')); ?>
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
                 				<?php echo $this->form->renderField('rev_date'); ?>
					<?php if ($this->revtaggroup_parent) : ?>
						<h4>Review Tags</h4>
 						<?php  $this->form->setFieldAttribute('tags','label',Text::_('XBCULTURE_ALLTAGS'));
 						    $this->form->setFieldAttribute('tags','description',Text::_('XBCULTURE_ALLTAGS_DESC'));						    
 						    $this->form->setFieldAttribute('revtaggroup','label',$this->taggroupinfo[$this->revtaggroup_parent]['title']);
 						    $this->form->setFieldAttribute('revtaggroup','description',$this->taggroupinfo[$this->revtaggroup_parent]['description']);
 						    echo $this->form->renderField('revtaggroup'); 
						endif; ?>
 				<h4><?php echo Text::_('XBCULTURE_STATUS_CATS_TAGS'); ?></h4> 				
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                 				<?php echo $this->form->renderField('reviewer'); ?>
                 				<?php // echo $this->form->renderField('rev2read'); ?>
			</div>
		<?php echo HtmlHelper::_('bootstrap.endTab'); ?>
		<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('XBBOOKS_FIELDSET_PUBLISHING')); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span6">
					<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="span6">
					<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
		<?php echo HtmlHelper::_('bootstrap.endTab'); ?>
	</div>
  </div>
    <input type="hidden" name="task" value="review.edit" />
    <?php echo HtmlHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
<script>
//for preview modal
jQuery(document).ready(function(){
    jQuery('#ajax-pvmodal').on('show', function () {
        // Load view vith AJAX
        jQuery(this).find('.modal-content').load(jQuery('a[data-target="#'+jQuery(this).attr('id')+'"]').attr('href'));
    })
    jQuery('#ajax-pvmodal').on('hidden', function () {
     //document.location.reload(true);
     //Joomla.submitbutton('film.apply');
    })
});
</script>
<!-- preview modal window -->
<div class="modal fade xbpvmodal" id="ajax-pvmodal" style="max-width:1200px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>


