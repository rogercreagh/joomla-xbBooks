<?php 
/*******
 * @package xbBooks
 * @filesource site/views/book/tmpl/default.php
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
$hide_empty=$this->hide_empty;

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbbooks&view=category'.$itemid.'&id=';

$blink = XbbooksHelperRoute::getBooksLink();

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
<div class="xbbox bkbox">
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
					<?php if ($item->revcnt>0) : ?>
						<?php $stars = (round(($item->averat)*2)/2); ?>
						<?php if (($this->zero_rating) && ($stars==0)) : ?>
				 		    <span class="<?php echo $this->zero_class; ?>"></span>
						<?php else : ?>
	                         <?php echo str_repeat('<i class="'.$this->star_class.'"></i>',intval($item->averat)); ?>
	                         <?php if (($item->averat - floor($item->averat))>0) : ?>
	                              <i class="<?php echo $this->halfstar_class; ?>"></i>
	                          <?php  endif; ?> 
	                    <?php endif; ?> 
	                    <br /><span class="xb09" style="color:darkgray;">
	                    <?php if ($item->revcnt >1) : ?>
	                    	<?php echo round($item->averat,1); ?> average from <?php echo $item->revcnt; ?> ratings<br />	
	                    <?php else : ?>                                               
    	                    One review on <?php echo HtmlHelper::date($item->reviews[0]->rev_date ,'d M Y');?>
	                    <?php endif; ?>
	                    </span> 
	                <?php else : ?>
	                   	<p class="xbnote">no reviews available</p>                   
					<?php endif; ?>						
                </div>
				<p class="xb11"><?php if ($item->pubyear>0) { 
					echo Text::_('XBBOOKS_FIRSTPUB').': <b>'.$item->pubyear.'</b>'; 
				} ?></p>
				<?php if($this->show_fict) : ?>
				<p>
					<?php if ($item->fiction==1) : ?>
						<span class="label"><?php echo Text::_('XBCULTURE_FICTION'); ?></span>
					<?php else : ?>
						<span class="label label-inverse"><?php echo Text::_('XBCULTURE_NONFICTION'); ?></span>
					<?php endif; ?>	
				</p>
				<?php endif; ?>
			</div>
			<h2><?php echo $item->title; ?></h2>
			<?php if ($item->subtitle != '') : ?>
				<h3><?php  echo $item->subtitle; ?></h3>
			<?php endif; ?>
    	    <?php if ($item->editcnt>0) : ?>
					<h4><span class="xbnit xbmr10"><?php echo Text::_('XBCULTURE_EDITOR').': '; ?></span>
		                <?php  echo $item->elist['commalist']; ?> 
                     </h4> 
		    <?php endif; ?>
             <?php if ($item->authcnt>0) : ?>
                  <h4>
					<span class="xbnit xbmr10">
						<?php echo Text::_(($item->authcnt==1) ? 'XBCULTURE_AUTHOR' : 'XBCULTURE_AUTHORS').': '; ?>
					</span> 
					<?php echo $item->alist['commalist']; ?> 
				</h4>
			<?php else : ?>
				<p class="xbnit"><?php echo Text::_('XBBOOKS_NOAUTHOR'); ?></p>                         
             <?php endif; ?>
             <div class="clearfix"></div>
             <?php if (trim($item->summary)!='') {
                 $sum = '<i>'.Text::_('XBCULTURE_SUMMARY').'</i>: '.$item->summary;
             } elseif (trim($item->synopsis)!='') {
                 $sum = '<i>'.Text::_('XBBOOKS_SYNOPSIS_EXTRACT').'</i>: '.XbcultureHelper::makeSummaryText($item->synopsis,200);                
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
    <div class="row-fluid">
    	<div class="span5">
        	<?php if ((!$item->publisher=='') || (!$hide_empty)) : ?>
     			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_PUBLISHER').': '; ?></div>
       			<div class="pull-left" style="margin:2px 0 0 0;">
    				<?php echo (!$item->publisher=='') ? $item->publisher : '<span class="xbnit">'.Text::_('XBBOOKS_UNKNOWN').'</span>'; ?>
    			</div>
          		<div class="clearfix"></div>
         	<?php endif; ?>
           	<?php if ((!$item->orig_lang=='') || (!$hide_empty)) : ?>
    			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBBOOKS_ORIG_LANG').': '; ?></div>
    			<div class="pull-left" style="margin:2px 0 0 0;">
    				<?php echo (!$item->orig_lang=='') ? $item->orig_lang : '<span class="xbnit">'.Text::_('XBBOOKS_UNKNOWN').'</span>'; ?>
                </div>
    			<div class="clearfix"></div> 
       		<?php endif; ?>
        </div>               
    	<div class="span1"></div>
    	<div class= "span6">
           	<?php if ((!$item->edition=='') || (!$hide_empty)) : ?>
    			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_EDITION').': '; ?></div>
    			<div class="pull-left" style="margin:2px 0 0 0;">
    				<?php echo (!$item->edition=='') ? $item->edition : '<span class="xbnit">'.Text::_('XBBOOKS_UNKNOWN').'</span>'; ?>
                </div>
    			<div class="clearfix"></div> 
       		<?php endif; ?>
           	<?php if ((!$item->format=='') || (!$hide_empty)) : ?>
    			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_FORMAT').': '; ?></div>
    			<div class="pull-left" style="margin:2px 0 0 0;">
    				<?php echo (!$item->format=='') ? $item->format : '<span class="xbnit">'.Text::_('XBBOOKS_UNKNOWN').'</span>'; ?>
                </div>
    			<div class="clearfix"></div> 
       		<?php endif; ?>
       		<!-- insert reading notes here -->
    	</div>
    </div>
    <?php if ((($item->mencnt + $item->othcnt + $item->ccnt + $item->gcnt) > 0) || (!$hide_empty)) : ?>
        <hr />
        <div class="row-fluid">
        	<?php if ((($item->mencnt + $item->ccnt) > 0) || (!$hide_empty)) : ?>
        		<div class="span6">
                	<p><b><i>People mentioned & Characters</i></b></p>
                  	<?php if (($item->mencnt > 0) || (!$hide_empty)) : ?>
              			<div class="pull-left xbnit xbmr10">
                			<?php echo Text::_('XBBOOKS_APPEARING_BOOK'); ?>: 
                		</div>
                		<?php if ($item->mencnt == 0) : ?>
                			<div class="xbnit xbmt2"><?php echo Text::_('XBBOOKS_NONELISTED'); ?><br /></div>
                		<?php else : ?>
                			<div class="clearfix"></div>
                			<div class="xbmt2 xbml20"><?php echo $item->mlist['ullist'] ; ?></div>
                		<?php  endif; ?>
                		<div class="clearfix"></div>
                	<?php endif; ?>
                	<?php if (($item->ccnt > 0) || (!$hide_empty)) : ?>
                		<div class="pull-left xbnit xbmr10">
                			<?php echo Text::_('XBBOOKS_FICTIONAL_CHARS'); ?>: 
                		</div>
                		<?php if ($item->ccnt == 0) : ?>
                			<div class="xbnit xbmt2"><?php echo Text::_('XBBOOKS_NONELISTED'); ?><br /></div>
                		<?php else : ?>
                			<div class="clearfix"></div>
                			<div class="xbmt2 xbml20"><?php echo $item->clist['ullist'] ; ?></div>
                		<?php  endif; ?>
                	<?php endif; ?>
        		</div>
        	<?php endif; ?>
       		<?php if ((($item->othcnt + $item->gcnt) > 0) || (!$hide_empty)) : ?>
        		<div class="span6">
            		<?php if (($item->othcnt > 0) || (!$hide_empty)) : ?>
                    	<p><b><i>Other Production Roles</i></b></p>
            			<?php if ($item->othcnt == 0) : ?>
            				<div class="xbnit xbmt2"><?php echo Text::_('XBBOOKS_NOOTHERS'); ?><br /></div>
            			<?php else : ?>
            				<div class="xbmt2"><?php echo $item->olist['ullist'] ; ?></div>
            			<?php  endif; ?>
            		<?php endif; ?>
                	<?php if (($item->gcnt > 0) || (!$hide_empty)) : ?>
                    	<p><b><i>Groups</i></b></p>
            			<?php if ($item->gcnt == 0) : ?>
            				<div class="xbnit xbmt2"><?php echo Text::_('XBBOOKS_NONELISTED'); ?><br /></div>
            			<?php else : ?>
            				<div class="xbmt2"><?php echo $item->grouplist['ullist'] ; ?></div>
            			<?php  endif; ?>
                	<?php endif; ?>
        		</div>
        	<?php endif; ?>
        </div>
    <?php endif; ?>
	<?php if ($item->ext_links_cnt > 0) : ?>
        <hr />
    	<div class="row-fluid">
        	<div class="span<?php echo (($item->mencnt > 0) || ($item->ccnt > 0) || ($item->othcnt > 0))? '6' : '12'; ?>">
        		<p><b><i><?php echo Text::_('XBBOOKS_EXT_LINKS'); ?></i></b></p>   					
        		<?php echo $item->ext_links_list; ?>		
        	</div>
        </div>
	<?php endif; ?>
	<div class="row-fluid xbmt16">
		<?php if ($this->show_bcat >0) : ?>       
        	<div class="span4">
				<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBBOOKS_BOOK_CAT'); ?></div>
				<div class="pull-left">
					<?php if($this->show_bcat==2) : ?>
						<a class="label label-success" href="<?php echo Route::_($clink.$item->catid); ?>">
							<?php echo $item->category_title; ?></a>
					<?php else: ?>
						<span class="label label-success">
						<?php echo $item->category_title; ?></span>
					<?php endif; ?>		
				</div>
	        </div>
        <?php endif; ?>
    	<?php if (($this->show_btags>0) && (!empty($item->tags))) : ?>
    	<div class="span<?php echo ($this->show_bcat>0) ? '8' : '12'; ?>">
			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_TAGS_U'); ?>
			</div>
			<div class="pull-left">
				<?php  $tagLayout = new FileLayout('joomla.content.tags');
    				echo $tagLayout->render($item->tags); ?>
			</div>
    	</div>
		<?php endif; ?>
    </div>
    <?php if ($this->show_bdates) : ?>
    	<div class="row-fluid">
    		<div class="span4">
    			<span class="xbnit"><?php echo  Text::_('XBBOOKS_FIRST_READ').': '; ?>
    			</span>
    			<?php if($item->first_read) : ?>
    				<?php $datefmt = xbCultureHelper::getDateFmt($item->first_read, 'D jS M Y');
    				echo HtmlHelper::date($item->first_read , $datefmt) ; ?>
    			<?php else: 
    			     echo Text::_('unknown');
    			endif; ?>
    		</div>
    		<div class="span4">
    	    	<?php if (($item->last_read) && ($item->last_read <> $item->first_read)) : ?>
    	    		<span class="xbnit"><?php echo  Text::_('XBBOOKS_LAST_READ').': '; ?>
    	    		</span>
    	    		<?php $datefmt = xbCultureHelper::getDateFmt($item->last_read, 'D jS M Y');
    	    		echo HtmlHelper::date($item->last_read , $datefmt) ; ?>
        		<?php endif; ?>
    		</div>
    		<div class="span4">
    			   <span class="xbnit xbgrey"><?php echo  Text::_('XBCULTURE_CATALOGUED').': '.HtmlHelper::date($item->created ,'jS M Y'); ?>
    	    		</span>
    		</div>
    	</div>
        <hr />
    <?php endif; ?>
    <div class="row-fluid">
    	<div class="span<?php echo ($this->show_brevs ==0)? 12 : 6; ?>">
    		<h4><?php echo Text::_('XBCULTURE_SYNOPSIS'); ?></h4>
    		<div class="xbbox xbboxcyan">
    			<?php if (empty($item->synopsis)) { 
    				echo '<p class="xbnit">'.Text::_('XBBOOKS_NO_SYNOPSIS').'</p>';				    
                } else { 
    				echo $item->synopsis; 
                } ?> 
    		</div>
    	</div>
    	<?php if ($this->show_brevs>0) : ?>
    		<div class="span6 xbmb12">
    			<h4><?php echo Text::_('XBCULTURE_REVIEWS_U'); ?></h4>
    			<?php if(empty($item->reviews)) : ?>
    				<p><i><?php echo Text::_( 'XBBOOKS_NOREVIEW' ); ?></i></p>
    			<?php else : ?>
    				<?php foreach ($item->reviews as $rev) : ?>
    					<div class="xbrevlist ">
    						<div class="xbbox revbox">
    							<?php if ($this->show_brevs>0) : ?>
    								<div style="padding-bottom:5px;">
    									<?php if (($this->zero_rating) && ($rev->rating==0)) : ?>
    										<span class="<?php echo $this->zero_class; ?>"></span>
    									<?php else: ?>
    										<?php echo str_repeat('<i class="'.$this->star_class.'"></i>',$rev->rating); ?>
    									<?php endif; ?>
    								</div>
    							<?php endif; ?>
    							<?php if ($this->show_brevs==2) : ?>
    								<?php if (!empty($rev->title) && ((!empty($rev->summary)) || (!empty($rev->review)) )) : ?>
    									<p><span class="xbtitle"><?php echo $rev->title; ?></span></p>				
    								<?php endif; ?>
    							<?php endif; ?>
    							<?php if ($this->show_brevs>0) : ?>
    								<p><?php echo ' by '.$rev->reviewer;
    									echo ' on '.HtmlHelper::date($rev->rev_date ,'D jS M Y').'. '; ?>
    								</p>
    							<?php endif; ?>
    							<?php if ($this->show_brevs==2) : ?>
    								<?php if (empty($rev->review)) {
    									if (empty($rev->summary)) {
    										echo '<span class="xbnit">'.Text::_('XBBOOKS_NO_REV_TEXT').'</span>';
    									} else {
    										echo $rev->summary;
    									}
    								} else { //summary doesn't get shown here if there is a review - OK????
    									echo $rev->review;
    								}  ?>
    								<?php //TODO extlinks?>
    	                    		<div class="row-fluid">
    	            	        		<?php if($this->show_rcat) : ?>
    	                        			<div class="span4">
    	                						<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBBOOKS_REV_CAT'); ?></div>
    	                						<div class="pull-left">
    	               								<?php if($this->show_rcat==2) : ?>
    													<a class="label label-success" href="<?php echo Route::_($clink.$rev->catid); ?>">
    													<?php echo $rev->category_title; ?></a>
    												<?php else: ?>
    													<span class="label label-success"><?php echo $rev->category_title; ?></a></span>
    												<?php endif; ?>
    	                						</div>
    	                            		</div>
    	            					<?php endif; ?>
    	             	       		 	<?php if(($this->show_rtags>0) && ($rev->tagcnt>0)) : ?>
    	                            		<div class="span<?php echo ($this->show_rtags>0) ? '8' : '12'; ?>">
    	                						<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_TAGS_U'); ?></div>
    	                						<div class="pull-left">	                	
    	                                			<?php $tagLayout = new FileLayout('joomla.content.tags');
    	                            				echo $tagLayout->render($rev->tags); ?>              
    	                                		</div>
    	                            		</div>
    									<?php endif; ?>
    	            				</div>
    	            			<?php  endif;?>
    	            		</div>
    					</div>
    	        	<?php endforeach; ?>
    	    	<?php endif; ?>
    		</div>
    	<?php endif; ?>
    </div> 
</div>
<?php if($this->tmpl != 'component') : ?>
    <div class="xbbox xbboxgrey">
    <div class="row-fluid ">
    	<div class="span2">
    		<?php if (($item->prev>0) || ($item->next>0)) : ?>
    				<span class="xbpop xbcultpop xbinfo fas fa-info-circle" data-trigger="hover" title 
    					data-original-title="Prev-Next Info" data-content="<?php echo JText::_('XBBOOKS_INFO_PREVNEXT'); ?>" >
    				</span>&nbsp;
    		<?php endif; ?>
    		<?php if($item->prev > 0) : ?>
    			<a href="<?php echo Route::_(XbbooksHelperRoute::getBookLink($item->prev)); ?>" class="btn btn-small">
    				<?php echo Text::_('XBCULTURE_PREV'); ?></a>
    	    <?php endif; ?>
    	</div>
    	<div class="span8"><center>
    		<a href="<?php echo Route::_($blink); ?>" class="btn btn-small">
    			<?php echo Text::_('XBBOOKS_BOOKLIST'); ?></a></center>
    	</div>
    	<div class="span2">
    		<?php if($item->next > 0) : ?>
    			<a href="<?php echo Route::_(XbbooksHelperRoute::getBookLink($item->next)); ?>" class="btn btn-small pull-right">
    				<?php echo Text::_('XBCULTURE_NEXT'); ?></a>
    	    <?php endif; ?>
    	</div>
    </div>
</div>
<div class="clearfix"></div>
</div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
<?php endif; ?>
<script>
jQuery(document).ready(function(){
//for preview modals
    jQuery('#ajax-ppvmodal').on('show', function () {
        // Load view vith AJAX
      jQuery(this).find('.modal-content').load('/index.php?option=com_xbpeople&view=person&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-ppvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
    jQuery('#ajax-gpvmodal').on('show', function () {
        // Load view vith AJAX
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbpeople&view=group&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-gpvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
    jQuery('#ajax-cpvmodal').on('show', function () {
        // Load view vith AJAX
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbpeople&view=character&layout=default&tmpl=component&id='+window.pvid);
    })
    jQuery('#ajax-cpvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
});
</script>
<!-- preview modal windows -->
<div class="modal fade xbpvmodal" id="ajax-ppvmodal" style="max-width:1000px">
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
<div class="modal fade xbpvmodal" id="ajax-gpvmodal" style="max-width:800px">
    <div class="modal-dialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Group</h4>
        </div>
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
<div class="modal fade xbpvmodal" id="ajax-cpvmodal" style="max-width:800px">
    <div class="modal-dialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
            	style="opacity:unset;line-height:unset;border:none;">&times;</button>
             <h4 class="modal-title" style="margin:5px;">Preview Character</h4>
        </div>
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>


