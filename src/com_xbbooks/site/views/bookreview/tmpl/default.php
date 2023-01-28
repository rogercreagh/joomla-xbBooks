<?php
/*******
 * @package xbBooks
 * @filesource site/views/bookreview/tmpl/default.php
 * @version 1.0.3.9 28th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;

$item = $this->item;

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbbooks&view=category'.$itemid.'&id=';

$blink = XbbooksHelperRoute::getBookLink($item->book_id);

$itemid = XbbooksHelperRoute::getReviewsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$brlink = 'index.php?option=com_xbbooks&view=bookreview'.$itemid.'&id=';

$imgok = (($this->show_image >0) && (JFile::exists(JPATH_ROOT.'/'.$item->cover_img)));
if ($imgok) {
    $src = Uri::root().$item->cover_img;
    $tip = '<img src=\''.$src.'\' style=\'width:400px;\' />';
}

?>
<style type="text/css" media="screen">
	.xbpvmodal .modal-content {padding:15px;max-height:calc(100vh - 190px); overflow:scroll; }
    <?php if($this->tmpl == 'component') : ?>
        .fa-eye {visibility:hidden;}
    <?php endif; ?>
</style>
<div class="xbculture ">
	<div class="xbbox revbox">
		<div class="row-fluid">
    		<?php if ($imgok && ($this->show_image ==1 )) : ?>
    			<div class="span2">
    				<?php if($imgok) : ?>
        				<img class="hasTooltip" title="" data-original-title="<?php echo $tip; ?>"
        					data-placement="right" src="<?php echo $src; ?>" border="0" alt="" style="max-width:100%;" />
        			<?php endif; ?>
    			</div>
    		<?php endif; ?>
    		<div class="span<?php echo ($imgok && ($this->show_image > 0 )) ? '10' : '12'; ?>">
				<div class="pull-right xbmr20" style="text-align:right;">
	    	    	<div class="xb12">
            	        <?php if (($this->zero_rating) && ($item->rating==0)) : ?>
            	            <span class="<?php echo $this->zero_class; ?>" ></span>
            	        <?php else: ?>
            	          	<span class="xb15">
            	          	<?php echo str_repeat('<i class="'.$this->star_class.'"></i>',$item->rating); ?>
            	          	</span>
            	        <?php endif; ?>	
	                    <br /><span class="xb09" style="color:darkgray;">
    	                    reviewed on <?php echo HtmlHelper::date($item->rev_date ,'d M Y');?>
    	                </span>
	    	    	</div>
				</div>
				<h3><?php echo $item->title; ?></h3>
				<h4><span class="xbnit"><?php echo Text::_('XBBOOKS_REVIEWOF'); ?></span>"
					 <a href="<?php echo XbbooksHelperRoute::getBookLink($item->book_id); ?>"
    					 <?php if (!empty($item->edauths)) : ?>
    					 	class="xbpop xbcultpop xbhover" data-trigger="hover" tabindex="<?php echo $item->id; ?>" 
    					 	title data-original-title="Author/Editor" data-content="<?php echo strip_tags($item->edauths); ?>"
    					 <?php endif; ?>
					 ><?php echo $item->book_title; ?></a>"&nbsp;
					 <a href="" class="xbpv" data-toggle="modal" data-target="#ajax-bpvmodal" 
					 	onclick="window.pvid=<?php echo $item->book_id; ?>;"><i class="far fa-eye"></i></a>&nbsp;
					 <span class="xbnit"><?php echo Text::_('XBCULTURE_BY'); ?></span>
					 <?php echo $item->reviewer; ?>
    			</h4>
                 <div class="clearfix"></div>
                 <?php if (trim($item->summary)!='') {
                     $sum = '<i>'.Text::_('XBCULTURE_SUMMARY').'</i>: '.$item->summary;
                 } elseif (trim($item->review)!='') {
                     $sum = '<i>'.Text::_('XBBOOKS_SYNOPSIS_EXTRACT').'</i>: '.XbcultureHelper::makeSummaryText($item->review,200);                
                 } else {
                     $sum = '<i>'.Text::_('XBBOOKS_NO_SUMMARY_SYNOPSIS').'</i>';
                 } ?>						
    			<div class="xbbox xbboxwht" style="max-width:700px; margin:auto;">
    				<div><?php echo $sum; ?></div> 
    			</div>
    			<br />
    		</div>
    		<?php if ($imgok && ($this->show_image == 2 )) : ?>
    			<div class="span2">
    				<img class="hasTooltip" title="" data-original-title="<?php echo $tip; ?>"
    				 data-placement="left" src="<?php echo $src; ?>" border="0" alt="" style="max-width:100%;" />
    			</div>
    		<?php endif; ?>
		</div>
        <div class="row-fluid xbmt16">
        	<?php if ($this->show_cat >0) : ?>       
                	<div class="span4">
        				<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_CATEGORY'); ?></div>
        					<div class="pull-left">
            					<?php if($this->show_cat==2) : ?>
            						<a class="label label-success" href="<?php echo Route::_($clink.$item->catid); ?>">
            							<?php echo $item->category_title; ?></a>
            					<?php else: ?>
            						<span class="label label-success">
            							<?php echo $item->category_title; ?></span>
            					<?php endif; ?>		
        				</div>
        	        </div>
                <?php endif; ?>
                	<?php if (($this->show_tags) && (!empty($item->tags))) : ?>
                	<div class="span<?php echo ($this->show_fcat>0) ? '8' : '12'; ?>">
        		<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_TAGS_U'); ?>
        		</div>
        		<div class="pull-left">
        			<?php  $tagLayout = new FileLayout('joomla.content.tags');
            				echo $tagLayout->render($item->tags); ?>
        				</div>
                	</div>
        			<?php endif; ?>
        </div>
        <p class="xbnit xbmb8"><?php echo Text::_('XBCULTURE_REVIEW_U');?></p>
   		<div class="xbbox xbboxcyan" style="max-width:800px; margin:auto;">
    		<?php if (empty($item->review)) : ?>
        		<p class="xbnit">No review text provided</p>
    		<?php else : ?>
				<?php echo $item->review; ?>
            <?php endif; ?>
   		</div>	
	</div>
    <?php if (count($item->allbookreviews) > 1) : ?>
    	<div class="row-fluid">
    		<div class="span12">
    			<div class="xbbox xbboxwht">
    				<span class="xbnit"><?php echo Text::_('XBCULTURE_OTHER_REVIEWS_OF').' '.$item->book_title; ?>: </span>
    				<p>
    				<?php foreach ($item->allbookreviews as $rev) : ?>
    					<?php if ($rev->id != $item->id) : ?>
    						<div class="pull-left" style="min-width:110px; margin:0 10px 0 30px">
    	                     	<?php if (($this->zero_rating) && ($rev->rating==0)) : ?>
                    	            <span class="<?php echo $this->zero_class; ?> "></span>
                    	        <?php else: ?>
                    	          	<?php echo str_repeat('<i class="'.$this->star_class.'"></i>',$item->rating); ?>
    		        			<?php endif; ?> 	
    	                    </div>
    	                    <a href="<?php echo $brlink.$rev->id; ?>"><b><?php echo $rev->title; ?></b></a>
    	                     by <?php echo $rev->created_by_alias.', '.HtmlHelper::date($rev->created ,'d M Y'); ?>
    	                     <br />
    	                <?php endif; ?>
    				<?php endforeach; ?>
    				</p>
    	        </div>
    	    </div>
    	</div>
    <?php endif; ?>
	<?php if($this->tmpl != 'component') : ?>
		<div class="xbbox xbboxgrey">
        	<div class="row-fluid xbmt16"><!-- prev/next -->
        		<div class="row-fluid">
        			<div class="span2">
        		<?php if (($item->prev>0) || ($item->next>0)) : ?>
        				<span class="xbpop xbcultpop xbinfo fas fa-info-circle" data-trigger="hover" title 
        					data-original-title="Prev-Next Info" data-content="<?php echo JText::_('XBBOOKS_INFO_PREVNEXT'); ?>" >
        				</span>&nbsp;
        		<?php endif; ?>
        				<?php if($item->prev > 0) : ?>
        					<a href="index.php?option=com_xbbooks&view=bookreview&id=<?php echo $item->prev ?>" class="btn btn-small">
        						<?php echo Text::_('XBCULTURE_PREV'); ?></a>
        			    <?php endif; ?>
        			</div>
        			<div class="span8"><center>
        				<a href="index.php?option=com_xbbooks&view=bookreviews" class="btn btn-small">
        					<?php echo Text::_('XBBOOKS_REVIEWLIST'); ?></a></center>
        			</div>
        			<div class="span2">
        			<?php if($item->next > 0) : ?>
        				<a href="index.php?option=com_xbbooks&view=bookreview&id=<?php echo $item->next ?>" class="btn btn-small pull-right">
        					<?php echo Text::_('XBCULTURE_NEXT'); ?></a>
        		    <?php endif; ?>
        			</div>
        	      </div>
              </div>
		</div>
	<?php endif; ?>
	<div class="clearfix"></div>
</div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
<script>
jQuery(document).ready(function(){
//for preview modals
    jQuery('#ajax-ppvmodal').on('show', function () {
        // Load view vith AJAX
       //jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=booklist&layout=modalppv&tmpl=component');
      jQuery(this).find('.modal-content').load('/index.php?option=com_xbpeople&view=person&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-ppvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
    jQuery('#ajax-bpvmodal').on('show', function () {
        // Load view vith AJAX
       //jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=booklist&layout=modalbpv&tmpl=component');
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=book&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-bpvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
});
</script>
<!-- preview modal windows -->
<div class="modal fade xbpvmodal" id="ajax-ppvmodal" style="max-width:800px">
    <div class="modal-dialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Person</h4>
        </div>
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
<div class="modal fade xbpvmodal" id="ajax-bpvmodal" style="max-width:1000px">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Book</h4>
        </div>
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>

