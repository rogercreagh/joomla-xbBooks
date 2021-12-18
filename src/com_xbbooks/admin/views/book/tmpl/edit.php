<?php
/*******
 * @package xbBooks
 * @filesource admin/views/book/tmpl/edit.php
 * @version 0.9.6.a 18th December 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
HTMLHelper::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
HTMLHelper::_('formbehavior.chosen', 'select');

$document = JFactory::getDocument();
$style = '.controls .btn-group > .btn  {'
    . 'min-width: unset;'
    .'padding:3px 12px 4px;'
    . '}';
    $document->addStyleDeclaration($style);
?>
<form action="<?php echo JRoute::_('index.php?option=com_xbbooks&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
 	<div class="row-fluid">
		<div class="span10">
         	<div class="row-fluid">
        		<div class="span11">
        			<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
        		</div>
        		<div class="span1"><?php echo $this->form->renderField('id'); ?></div>
        	</div>
         	<div class="row-fluid">
        		<div class="span6">
        			<?php echo $this->form->renderField('subtitle'); ?>
                	<div class="row-fluid xbbox xbboxgrey">
						<div class="span6">
							<?php if (($this->item->id > 0) && (!empty($this->item->lastrat))) { 
                                echo Text::_('XBCULTURE_LAST_RATED').' ';
                                if ($this->item->lastrat['rate']>0) {
                                	echo str_repeat('<i class="'.$this->star_class.' "></i>',(int)($this->item->lastrat['rate']));
                                } else {
                                	echo '<i class="'.$this->zero_class.' "></i>';
                                }
                                echo ' on '.HtmlHelper::date($this->item->lastrat['read_date'] , 'd M Y');
                            } else { 
                                echo Text::_('XBCULTURE_NO_RATING');
                            } ?>
                        </div>
                        <div class="span6">
							<?php echo $this->form->renderField('quick_rating'); ?>
							<?php echo $this->form->renderField('qratnote'); ?>
						</div>
					</div>
        		</div>
        		<div class="span6">
        			<?php echo $this->form->renderField('summary'); ?>
        		</div>
        	</div> 
			<?php echo $this->form->renderField('authorlist'); ?>
        </div>    
        <div class="span2">
    		<?php if($this->form->getValue('cover_img')){?>
    			<div class="control-group">
    				<img class="img-polaroid hidden-phone" style="max-width:100%;" 
        				src="<?php echo JUri::root() . $this->form->getValue('cover_img');?>" />
    			</div>
    		<?php } ?>
        </div>
    </div>
	<div class="pull-right xbnote" >The <b>Quick Add Person</b> button appears below here on the people tab</div>   
    <div class="row-fluid form-horizontal">
      <div class="span12">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('XBCULTURE_DETAILS')); ?>
		<div class="row-fluid">
    		<div class="span6">
          		<h4>Content</h4>
          		<p>Synopsis</p>
    			<?php echo $this->form->getInput('synopsis'); ?>
    		</div>
    		<div class="span3 form-vertical">
          		<h4>Book Info</h4>
          		<?php echo $this->form->renderField('fiction'); ?>   					
        		<?php echo $this->form->renderField('cat_date'); ?>
        		<?php echo $this->form->renderField('format'); ?>
        		<?php echo $this->form->renderField('orig_lang'); ?>
        		<?php echo $this->form->renderField('publisher'); ?>
        		<?php echo $this->form->renderField('pubyear'); ?>
         		<?php echo $this->form->renderField('edition'); ?>
   			</div>
			<div class="span3">
 				<fieldset class="form-vertical">
           			<?php echo $this->form->renderField('cover_img'); ?>
 				</fieldset>
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
			<hr />
			<?php echo $this->form->renderField('ext_links'); ?>
   		</div>
 		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'people', Text::_('XBCULTURE_PEOPLE_U')); ?>
  		<div class="row-fluid">
			<div class="span9">
				<h4><?php echo Text::_('XBCULTURE_BOOK_U').' '.Text::_('XBCULTURE_PEOPLE_AND_CHARS');?></h4>
				<?php echo $this->form->renderField('editorlist'); ?>
				<p class="xbnote">Use this section for additional roles in producing the book such as translator, illustratior, etc.</p>
				<?php echo $this->form->renderField('otherlist'); ?>
				<?php echo $this->form->renderField('menlist'); ?>
				<p class="xbnote"><?php echo Text::_('COM_XBBOOKS_FIELD_CHARS_NOTE');?> </p>
				<?php echo $this->form->renderField('charlist'); ?>
			</div>
    		<div class="span3">
    			<h4>Quick Person Add</h4>
    			<p class="xbnote">NB Save this page before using quick-person or changes will be lost hen the page reloads. 
    			<br />The new person will appear at the top of the drop down list. Always check the list first 
    				to see if the person already exists. You cannot use this to create a second person with the same name -
    				for that you need to use the full New Person form.</p> 
				<a class="btn btn-small" data-toggle="modal" 
					href="index.php?option=com_xbfilms&view=film&layout=modal&tmpl=component" 
					data-target="#ajax-modal">
					<i class="icon-new"></i>Quick New Person</a>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('COM_XBBOOKS_FIELDSET_PUBLISHING')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    </div>
    </div>
    <input type="hidden" name="task" value="book.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="clearfix"></div>
<p><?php echo XbbooksGeneral::credit();?></p>
<script>
jQuery(document).ready(function(){
    jQuery('#ajax-modal').on('show', function () {
        // Load view vith AJAX
        jQuery(this).find('.modal-content').load(jQuery('a[data-target="#'+jQuery(this).attr('id')+'"]').attr('href'));
    })
    jQuery('#ajax-modal').on('hidden', function () {
     document.location.reload(true);
    })
});
</script>
<div class="modal fade" id="ajax-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>

