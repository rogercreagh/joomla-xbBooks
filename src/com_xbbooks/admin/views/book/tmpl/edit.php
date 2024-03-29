<?php
/*******
 * @package xbBooks
 * @filesource admin/views/book/tmpl/edit.php
 * @version 1.0.4.0d 12th February 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HtmlHelper::_('behavior.tabState');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
HTMLHelper::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
HTMLHelper::_('formbehavior.chosen', 'select');

$document = Factory::getDocument();
$style = '.controls .btn-group > .btn  {min-width: unset;padding:3px 12px 4px;}';
$document->addStyleDeclaration($style);
?>
<style type="text/css" media="screen">
	.xbpvmodal .modal-content {padding:15px;max-height:calc(100vh - 190px); overflow:scroll; }
    .xbqpmodal .modal-body {height:370px;} 
    .xbqpmodal .modal-body iframe { height:340px;}
</style>
<form action="<?php echo Route::_('index.php?option=com_xbbooks&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
 	<div class="row-fluid">
		<div class="span10">
         	<div class="row-fluid">
        		<div class="span11">
        			<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
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
                                echo ' on '.HtmlHelper::date($this->item->lastrat['rev_date'] , 'd M Y');
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
        	<div class="row-fluid">
        		<div class="span9">
        			<?php echo $this->form->renderField('authorlist'); ?>
        		</div>
        		<div class="span3 xbbox xbboxwht">
        			<h4><?php echo Text::_('XBCULTURE_QUICK_P_ADD');?></h4>
        			<p class="xbnote"><?php echo Text::_('XBCULTURE_QUICK_P_NOTE');?></p> 
    				<a class="btn btn-small" data-toggle="modal" 
    					href="index.php?option=com_xbbooks&view=book&layout=modalnewp&tmpl=component" 
    					data-target="#ajax-qpmodal">
    					<i class="icon-new"></i><?php echo Text::_('XBCULTURE_ADD_NEW_P');?></a>
        		</div>
        	</div>
        </div>    
        <div class="span2">
     		<?php if($this->form->getValue('cover_img')) : ?>
    			<div class="control-group">
    				<img class="img-polaroid hidden-phone" style="max-width:100%;" 
        				src="<?php echo Uri::root() . $this->form->getValue('cover_img');?>" />
    			</div>
    		<?php else : ?>
    			<div class="xbbox xbboxwht xbnit xbtc"><?php echo Text::_('XBCULTURE_NO_PICTURE'); ?></div>
    		<?php endif; ?>
        </div>
    </div>
    <div class="row-fluid form-vertical">
      <div class="span12">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('XBCULTURE_DETAILS')); ?>
		<div class="rowfluid">
			<div class="span9">
				<div class="xbmw1200">
					<?php echo $this->form->renderField('ext_links'); ?>
				</div>			
        		<div class="row-fluid">
            		<div class="span9">
              			<h4>Content</h4>
              			<p>Synopsis</p>
        				<?php echo $this->form->getInput('synopsis'); ?>
        			</div>
            		<div class="span3 form-vertical">
                  		<h4>Book Info</h4>
                        <?php echo $this->form->renderField('first_read'); ?>
                		<?php echo $this->form->renderField('last_read'); ?>
                  		<?php echo $this->form->renderField('fiction'); ?>   					
                		<?php echo $this->form->renderField('format'); ?>
                		<?php echo $this->form->renderField('orig_lang'); ?>
                		<?php echo $this->form->renderField('publisher'); ?>
                		<?php echo $this->form->renderField('pubyear'); ?>
                 		<?php echo $this->form->renderField('edition'); ?>
           			</div>
           		</div>
			</div>
			<div class="span3">
 				<fieldset class="form-vertical">
           			<?php echo $this->form->renderField('cover_img'); ?>
           			<?php if ($this->taggroups) : ?>
 						<?php  $this->form->setFieldAttribute('tags','label',Text::_('XBCULTURE_ALLTAGS'));
 						    $this->form->setFieldAttribute('tags','description',Text::_('XBCULTURE_ALLTAGS_DESC'));	?>	    
           				<h4><?php echo Text::_('XBCULTURE_TAG_GROUPS'); ?></h4>
 						<?php if ($this->taggroup1_parent) {
 						    $this->form->setFieldAttribute('taggroup1','label',$this->taggroupinfo[$this->taggroup1_parent]['title']);
 						    $this->form->setFieldAttribute('taggroup1','description',$this->taggroupinfo[$this->taggroup1_parent]['description']);
      						echo $this->form->renderField('taggroup1'); 
						} ?>
 						<?php if ($this->taggroup2_parent) {
 						    $this->form->setFieldAttribute('taggroup2','label',$this->taggroupinfo[$this->taggroup2_parent]['title']);
 						    $this->form->setFieldAttribute('taggroup2','description',$this->taggroupinfo[$this->taggroup2_parent]['description']);
      						echo $this->form->renderField('taggroup2'); 
						} ?>
 						<?php if ($this->taggroup3_parent) {
 						    $this->form->setFieldAttribute('taggroup3','label',$this->taggroupinfo[$this->taggroup3_parent]['title']);
 						    $this->form->setFieldAttribute('taggroup3','description',$this->taggroupinfo[$this->taggroup3_parent]['description']);
      						echo $this->form->renderField('taggroup3'); 
						} ?>
 						<?php if ($this->taggroup4_parent) {
 						    $this->form->setFieldAttribute('taggroup4','label',$this->taggroupinfo[$this->taggroup4_parent]['title']);
 						    $this->form->setFieldAttribute('taggroup4','description',$this->taggroupinfo[$this->taggroup4_parent]['description']);
      						echo $this->form->renderField('taggroup4'); 
						} ?>
 					<?php endif; ?>
 				</fieldset>
 				<h4><?php echo Text::_('XBCULTURE_STATUS_CATS_TAGS'); ?></h4> 				
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
 		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'people', Text::_('XBCULTURE_PEOPLE_U')); ?>
  		<div class="row-fluid">
			<div class="span6 form-vertical">
			<h4><?php echo Text::_('XBCULTURE_BOOK_U').' '.Text::_('XBCULTURE_PEOPLE_U');?></h4>
				<p class="xbnote"><?php echo Text::_('XBCULTURE_ADD_PEEP_NOTE');?> </p>
				<?php echo $this->form->renderField('editorlist'); ?>
				<?php echo $this->form->renderField('menlist'); ?>
			</div>
    		<div class="span6 form-vertical">
				<h4><?php echo Text::_('XBCULTURE_BOOK_U').' '.Text::_('XBCULTURE_CHARACTERS_U');?></h4>
				<p class="xbnote"><?php echo Text::_('XBCULTURE_ADD_CHAR_NOTE');?> </p>
				<?php echo $this->form->renderField('charlist'); ?>
				<h4><?php echo Text::_('XBCULTURE_BOOK_U').' '.Text::_('XBCULTURE_GROUPS');?></h4>
				<p class="xbnote"><?php echo Text::_('XBCULTURE_ADD_CHAR_NOTE');?> </p>
				<?php echo $this->form->renderField('grouplist'); ?>
			</div>
		</div>
		<div class="xbmw1200 xbcentre">
			<?php echo $this->form->renderField('bookotherlist'); ?>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('XBBOOKS_FIELDSET_PUBLISHING')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    </div>
    </div>
    <input type="hidden" name="task" value="book.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
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
     //Joomla.submitbutton('book.apply');
    })
//for quickperson modal
     jQuery('#ajax-qpmodal').on('show', function () {
        // Load view vith AJAX
        jQuery(this).find('.modal-content').load(jQuery('a[data-target="#'+jQuery(this).attr('id')+'"]').attr('href'));
    })
    jQuery('#ajax-qpmodal').on('hidden', function () {
     //document.location.reload(true);
     Joomla.submitbutton('book.apply');
    })    
});
// fix multiple backdrops
jQuery(document).bind('DOMNodeInserted', function(e) {
    var element = e.target;
    if (jQuery(element).hasClass('modal-backdrop')) {
         if (jQuery(".modal-backdrop").length > 1) {
           jQuery(".modal-backdrop").not(':last').remove();
       }
	}    
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
<!-- quickperson modal window -->
<div class="modal fade xbqpmodal" id="ajax-qpmodal" style="max-width:1000px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
