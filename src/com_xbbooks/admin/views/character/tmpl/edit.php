<?php
/*******
 * @package xbBooks
 * @filesource admin/views/character/tmpl/edit.php
 * @version 0.9.10.2 14th November 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
HtmlHelper::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
HtmlHelper::_('formbehavior.chosen', 'select');

?>
<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=character&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
    	<div class="span9">
      		<div class="row-fluid form-vertical">
        		<div class="pull-left" >
             		<?php echo $this->form->renderField('name'); ?>
        		</div>
			</div>
			<div class="row-fluid form-vertical">
               <div class="span8">
                   <?php echo $this->form->renderField('alias'); ?> 
                   <?php echo $this->form->renderField('bookcharlist'); ?>
              </div>
              <div class="span4">
                   <?php echo $this->form->renderField('id'); ?>
                   <?php echo $this->form->renderField('summary'); ?>
              </div>
          </div>
      </div>
           <div class="pull-right span3">
        		<?php if($this->form->getValue('image')){?>
        			<div class="control-group">
        				<img class="img-polaroid hidden-phone" style="max-height:200px;min-width:24px;" 
            				src="<?php echo Uri::root() . $this->form->getValue('image');?>" />
        			</div>
        		<?php } ?>
            </div>  
    </div>
    <div class="row-fluid">
      <div class="span12">
		<?php echo HtmlHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('XBBOOKS_FIELDSET_GENERAL')); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform form-vertical">
					<div class="row-fluid">
    					<div class="span12">
                 			<?php echo $this->form->renderField('description'); ?>
    					</div>
					</div>
				</fieldset>
			</div>
			<div class="span3">
 				<fieldset class="form-vertical">
           			<?php echo $this->form->renderField('image'); ?>
 				</fieldset>
					<?php if ($this->chartaggroup_parent) : ?>
				        <h4>Character Tags</h4>
 						<?php  $this->form->setFieldAttribute('tags','label',Text::_('XBCULTURE_ALLTAGS'));
 						    $this->form->setFieldAttribute('tags','description',Text::_('XBCULTURE_ALLTAGS_DESC'));						    
 						    $this->form->setFieldAttribute('chartaggroup','label',$this->taggroupinfo[$this->chartaggroup_parent]['title']);
 						    $this->form->setFieldAttribute('chartaggroup','description',$this->taggroupinfo[$this->chartaggroup_parent]['description']);
 						    echo $this->form->renderField('chartaggroup'); 
						endif; ?>
 				<h4><?php echo Text::_('XBCULTURE_STATUS_CATS_TAGS'); ?></h4> 				
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo HtmlHelper::_('bootstrap.endTab'); ?>
		<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('XBBOOKS_FIELDSET_PUBLISHING')); ?>
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

    <input type="hidden" name="task" value="character.edit" />
    <?php echo HtmlHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
