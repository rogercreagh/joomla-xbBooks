<?php 
/*******
 * @package xbBooks
 * @version 0.9.6.a 18th December 2021
 * @version 0.9.5 10th May 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

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

<div class="row-fluid">
	<?php if ($imgok && ($this->show_image == 1 )) : ?>
		<div class="span2">
			<img class="hasTooltip" title="" data-original-title="<?php echo $tip; ?>"
				 data-placement="right" src="<?php echo $src; ?>" border="0" alt="" style="max-width:100%;" />
		</div>
	<?php endif; ?>
	<div class="<?php echo $imgok==true ? 'span10' : 'span12'; ?>">
		<div class="row-fluid">
			<div class="span12">
				<div class="xbbox xbboxcyan">
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
	                                    <span style="color:darkgray;"> (<?php echo round($item->averat,1); ?>)</span>                                   
	                                <?php  endif; ?> 
	                             <?php endif; ?> 
	                    <?php else : ?>
	                    	<p> </p>                   
						<?php endif; ?>						
                        </div>
						<h4 ><?php if ($item->pubyear>0) { 
						      echo Text::_('COM_XBBOOKS_FIRSTPUB').': '.$item->pubyear; 
						} ?></h4>
						<p>
                    		<?php echo ($item->fiction==1) ? '<span class="label">'.Text::_('XBCULTURE_FICTION').'</span>' : 
                                '<span class="label label-inverse">'.Text::_('XBCULTURE_NONFICTION').'</span>'; ?>
						</p>
					</div>
					<h2><?php echo $item->title; ?></h2>
			       	<?php if ($item->subtitle != '') : ?>
						<h3><?php  echo $item->subtitle; ?></h3>
			       	<?php endif; ?>
					<div class="row-fluid">
          			     <?php if ($item->editcnt>0) : ?>
							<div class="span5">
								<h4><span class="xbnit xbmr10"><?php echo Text::_('XBCULTURE_EDITOR').': '; ?></span>: 
					                <?php  echo $item->elist; ?> 
                	             </h4> 
							</div>
				        <?php endif; ?>
						<div class="span<?php echo ($item->editcnt>0)? '7' : '12'; ?>"><p class="xbmt10">
	                        <?php if ($item->authcnt>0) : ?>
    	                        <h4>
    								<span class="xbnit xbmr10">
    									<?php echo Text::_(($item->authcnt==1) ? 'XBCULTURE_AUTHOR' : 'XBCULTURE_CAPAUTHORS').': '; ?>
    								</span> 
    								<?php echo $item->alist; ?> 
								</h4>
							<?php else : ?>
								<p class="xbnit"><?php echo Text::_('COM_XBBOOKS_NOAUTHOR'); ?></p>                         
	                        <?php endif; ?>						
						</div>
					</div>   
 				</div>
       		</div>        		
		</div>
	    <div class="row-fluid">
			<div class="span5">
            	<?php if ((!$item->publisher=='') || (!$hide_empty)) : ?>
		 			<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_CAPPUBLISHER').': '; ?></div>
           			<div class="pull-left" style="margin:2px 0 0 0;">
						<?php echo (!$item->publisher=='') ? $item->publisher : '<span class="xbnit">'.Text::_('COM_XBBOOKS_UNKNOWN').'</span>'; ?>
					</div>
              		<div class="clearfix"></div>
             	<?php endif; ?>
	           	<?php if ((!$item->orig_lang=='') || (!$hide_empty)) : ?>
	 				<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_ORIG_LANG').': '; ?></div>
       				<div class="pull-left" style="margin:2px 0 0 0;">
       					<?php echo (!$item->orig_lang=='') ? $item->orig_lang : '<span class="xbnit">'.Text::_('COM_XBBOOKS_UNKNOWN').'</span>'; ?>
                    </div>
					<div class="clearfix"></div> 
           		<?php endif; ?>
        	</div>               
			<div class="span1"></div>
			<div class= "span6">
	           	<?php if ((!$item->edition=='') || (!$hide_empty)) : ?>
	 				<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_EDITION').': '; ?></div>
       				<div class="pull-left" style="margin:2px 0 0 0;">
       					<?php echo (!$item->edition=='') ? $item->edition : '<span class="xbnit">'.Text::_('COM_XBBOOKS_UNKNOWN').'</span>'; ?>
                    </div>
					<div class="clearfix"></div> 
           		<?php endif; ?>
	           	<?php if ((!$item->format=='') || (!$hide_empty)) : ?>
	 				<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_FORMAT').': '; ?></div>
       				<div class="pull-left" style="margin:2px 0 0 0;">
       					<?php echo (!$item->format=='') ? $item->format : '<span class="xbnit">'.Text::_('COM_XBBOOKS_UNKNOWN').'</span>'; ?>
                    </div>
					<div class="clearfix"></div> 
           		<?php endif; ?>
           		<!-- insert reading notes here -->
				<?php if ((trim($item->summary) != '') && (!empty($item->synopsis))) : ?>
					<div class="xbbox xbboxwht">
						<div class="pull-left"><span class="xbnit"><?php echo Text::_('XBCULTURE_SUMMARY'); ?> 
						: </span></div>
					 	<div><?php echo $item->summary; ?></div> 
					</div>
				<?php  endif;?>
				<?php if (($item->othcnt > 0) || (!$hide_empty)) : ?>
					<?php if ($item->othcnt == 0) : ?>
						<div class="xbnit xbmt2"><?php echo Text::_('COM_XBBOOKS_NOOTHERS'); ?></div>
					<?php else : ?>
    					<div class="xbmt2"><?php echo $item->olist ; ?></div>
					<?php  endif; ?>
				<?php endif; ?>
			</div>
		</div>
        <div class="row-fluid">
        <?php if ($item->ext_links_cnt > 0) : ?>
    		<div class="span<?php echo (($item->mencnt > 0) || ($item->charcnt > 0))? '6' : '12'; ?>">
    			<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_EXT_LINKS'); ?></div>
    			<div class="pull-left" style="margin:2px 0 0 0;">			
    				<?php echo $item->ext_links_list; ?>
    			</div><div class="clearfix"></div>		
    		</div>
        <?php endif; ?>
        <?php if (($item->mencnt > 0) || ($item->charcnt > 0)) : ?>
            <div class="span<?php echo ($item->ext_links_cnt > 0)? '6' : '12'; ?>">
            	<?php if (($item->mencnt > 0) || (!$hide_empty)) : ?>
            		<div class="pull-left xbnit xbmr10">
            			<?php echo Text::_('COM_XBBOOKS_APPEARING_BOOK'); ?>: 
            		</div>
            		<?php if ($item->mencnt == 0) : ?>
            			<div class="xbnit xbmt2"><?php echo Text::_('COM_XBBOOKS_NONELISTED'); ?></div>
            		<?php else : ?>
            			<div class="clearfix"></div>
            			<div class="xbmt2 xbml20"><?php echo $item->mlist ; ?></div>
            		<?php  endif; ?>
            		<div class="clearfix"></div>
            	<?php endif; ?>
            	<?php if (($item->charcnt > 0) || (!$hide_empty)) : ?>
            		<div class="pull-left xbnit xbmr10">
            			<?php echo Text::_('COM_XBBOOKS_FICTIONAL_CHARS'); ?>: 
            		</div>
            		<?php if ($item->charcnt == 0) : ?>
            			<div class="xbnit xbmt2"><?php echo Text::_('COM_XBBOOKS_NONELISTED'); ?></div>
            		<?php else : ?>
            			<div class="clearfix"></div>
            			<div class="xbmt2 xbml20"><?php echo $item->clist ; ?></div>
            		<?php  endif; ?>
            	<?php endif; ?>
            
            </div>
		<?php endif; ?>
        </div>
    </div>
	<?php if ($imgok && ($this->show_image == 2 )) : ?>
		<div class="span2">
			<img class="hasTooltip" title="" data-original-title="<?php echo $tip; ?>"
				 data-placement="left" src="<?php echo $src; ?>" border="0" alt="" style="max-width:100%;" />
		</div>
	<?php endif; ?>
    
</div>	
<?php if ($item->lastread>0) : ?>
	<div class="pull-left xbnit"><?php echo  Text::_('COM_XBBOOKS_DATE_READ').': '; ?></div>
	<div class="pull-left">
		<?php echo HtmlHelper::date($item->lastread , 'D jS M Y') ; ?>
    </div>
	<div class="clearfix"></div> 
	<hr />
<?php endif; ?>
	
<hr />
<div class="row-fluid">
	<div class="span<?php echo ($this->show_brevs ==0)? 12 : 6; ?>">
		<h4><?php echo Text::_('COM_XBBOOKS_CAPSYNOPSIS'); ?></h4>
		<div class="xbbox xbboxcyan">
			<?php if (empty($item->synopsis)) { 
				echo '<p class="xbnit">'.Text::_('COM_XBBOOKS_NO_SYNOPSIS').'</p>';				    
            } else { 
				echo $item->synopsis; 
            } ?> 
		</div>
        <div class="row-fluid xbmt16">
			<?php if ($this->show_bcat >0) : ?>       
	        	<div class="span4">
					<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_BOOK_CAT'); ?></div>
					<div class="pull-left">
    					<?php if($this->show_bcat==2) : ?>
    						<a class="label label-success" href="<?php echo JRoute::_($clink.$item->catid); ?>">
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
				<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_CAPTAGS'); ?>
				</div>
				<div class="pull-left">
					<?php  $tagLayout = new JLayoutFile('joomla.content.tags');
	    				echo $tagLayout->render($item->tags); ?>
				</div>
        	</div>
			<?php endif; ?>
        </div>
	</div>
	<?php if ($this->show_brevs>0) : ?>
		<div class="span6 xbmb12">
			<h4><?php echo Text::_('XBCULTURE_REVIEWS_U'); ?></h4>
			<?php if(empty($item->reviews)) : ?>
				<p><i><?php echo Text::_( 'COM_XBBOOKS_NOREVIEW' ); ?></i></p>
			<?php else : ?>
				<?php foreach ($item->reviews as $rev) : ?>
					<div class="xbrevlist ">
						<div class="xbbox xbboxmag">
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
										echo '<span class="xbnit">'.Text::_('COM_XBBOOKS_NO_REV_TEXT').'</span>';
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
	                						<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_REV_CAT'); ?></div>
	                						<div class="pull-left">
	               								<?php if($this->show_rcat==2) : ?>
													<a class="label label-success" href="<?php echo JRoute::_($clink.$rev->catid); ?>">
													<?php echo $rev->category_title; ?></a>
												<?php else: ?>
													<span class="label label-success"><?php echo $rev->category_title; ?></a></span>
												<?php endif; ?>
	                						</div>
	                            		</div>
	            					<?php endif; ?>
	             	       		 	<?php if(($this->show_rtags>0) && ($rev->tagcnt>0)) : ?>
	                            		<div class="span<?php echo ($this->show_rtags>0) ? '8' : '12'; ?>">
	                						<div class="pull-left xbnit xbmr10"><?php echo Text::_('COM_XBBOOKS_CAPTAGS'); ?></div>
	                						<div class="pull-left">	                	
	                                			<?php $tagLayout = new JLayoutFile('joomla.content.tags');
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
<div class="row-fluid xbbox xbboxgrey">
	<div class="span2">
		<?php if (($item->prev>0) || ($item->next>0)) : ?>
		<span class="hasTooltip xbinfo" title 
			data-original-title="<?php echo Text::_('COM_XBBOOKS_INFO_PREVNEXT'); ?>" >
		</span>&nbsp;
		<?php endif; ?>
		<?php if($item->prev > 0) : ?>
			<a href="<?php echo JRoute::_(XbbooksHelperRoute::getBookLink($item->prev)); ?>" class="btn btn-small">
				<?php echo Text::_('COM_XBBOOKS_CAPPREV'); ?></a>
	    <?php endif; ?>
	</div>
	<div class="span8"><center>
		<a href="<?php echo JRoute::_($blink); ?>" class="btn btn-small">
			<?php echo Text::_('COM_XBBOOKS_BOOKLIST'); ?></a></center>
	</div>
	<div class="span2">
		<?php if($item->next > 0) : ?>
			<a href="<?php echo JRoute::_(XbbooksHelperRoute::getBookLink($item->next)); ?>" class="btn btn-small pull-right">
				<?php echo Text::_('COM_XBBOOKS_CAPNEXT'); ?></a>
	    <?php endif; ?>
	</div>
</div>
<div class="clearfix"></div>
<p><?php echo XbbooksGeneral::credit();?></p>

