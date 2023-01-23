<?php 
/*******
 * @package xbBooks
 * @filesource site/views/booklist/tmpl/default.php
 * @version 1.0.3.6 23rd January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_TAGS')));
HTMLHelper::_('formbehavior.chosen', '.multipleCats', null, array('placeholder_text_multiple' => Text::_('XBCULTURE_SELECT_CATS')));
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='last_read';
    $orderDrn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'),'pubyear'=>Text::_('XBBOOKS_YEARPUB'), 
    'averat'=>Text::_('XBCULTURE_AVERAGE_RATING'), 'first_read'=>Text::_('XBBOOKS_FIRST_READ'),
    'last_read'=>Text::_('XBBOOKS_LAST_READ'), 'category_title'=>Text::_('XBCULTURE_CATEGORY'));

require_once JPATH_COMPONENT.'/helpers/route.php';

$cmitemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $cmitemid !== null ? '&Itemid=' . $cmitemid : '';
$clink = 'index.php?option=com_xbbooks&view=category'.$itemid.'&id=';

$bmitemid = XbbooksHelperRoute::getBooksRoute();
$itemid = $bmitemid !== null ? '&Itemid=' . $bmitemid : '';
$blink = 'index.php?option=com_xbbooks&view=book'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getReviewsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$rlink = 'index.php?option=com_xbbooks&view=bookreview'.$itemid.'&id=';

?>
<style type="text/css" media="screen">
	.xbpvmodal .modal-body {max-height: calc(100vh - 190px); }
    .xbpvmodal .modal-body iframe { max-height:calc(100vh - 270px);}
</style>
<div class="xbculture ">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
	    echo XbcultureHelper::sitePageheader($this->header);
	} ?>
	
	<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=booklist'); ?>" method="post" name="adminForm" id="adminForm">       
		<?php  // Search tools bar
			if ($this->search_bar) {
				$hide = '';
				if ($this->hide_fict) { $hide .= 'filter_fictionfilt,';}
				if ($this->hide_peep) { $hide .= 'filter_perfilt,filter_prole,';}
				if ($this->hide_char) { $hide .= 'filter_charfilt,';}
				if ($this->hide_cat) { $hide .= 'filter_category_id,filter_subcats,';}
				if ($this->hide_tag) { $hide .= 'filter_tagfilt,filter_taglogic,';}
				echo '<div class="row-fluid"><div class="span12">';
	            echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this,'hide'=>$hide));       
	         echo '</div></div>';
			} 
		?>
		<div class="row-fluid pagination" style="margin-bottom:10px 0;">
			<div class="pull-right">
				<p class="counter" style="text-align:right;margin-left:10px;">
					<?php echo $this->pagination->getResultsCounter().'.&nbsp;&nbsp;'; 
					   echo $this->pagination->getPagesCounter().'&nbsp;&nbsp;'.$this->pagination->getLimitBox().' per page'; ?>
				</p>
			</div>
			<div>
				<?php  echo $this->pagination->getPagesLinks(); ?>
                <?php echo 'sorted by '.$orderNames[$listOrder].' '.$listDirn ; ?>
			</div>
		</div>
		<div class="row-fluid">
        	<div class="span12">
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-no-items">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>

	<table class="table table-striped table-hover" style="table-layout:fixed;" id="xbbooklist">	
		<thead>
			<tr>
				<?php if($this->show_pic) : ?>
					<th class="center" style="width:80px">
						<?php echo Text::_( 'XBBOOKS_COVER' ); ?>
					</th>	
                <?php endif; ?>
				<th>
					<?php echo HtmlHelper::_('searchtools.sort','XBCULTURE_TITLE','title',$listDirn,$listOrder).				
    						', '.Text::_('XBCULTURE_AUTHOR').', '.
    						HtmlHelper::_('searchtools.sort','XBBOOKS_PUBYEARCOL','pubyear',$listDirn,$listOrder );				
					?>
				</th>					
                <?php if($this->show_sum) : ?>
				<th class="hidden-phone">
					<?php echo Text::_('XBCULTURE_SUMMARY');?>
				</th>
                <?php endif; ?>
                <?php if ($this->showrevs != 0 ) : ?>
					<th class="xbtc">
						<?php echo HtmlHelper::_('searchtools.sort','Rating','averat',$listDirn,$listOrder); ?>
					</th>
                <?php endif; ?>
                <?php if ($this->show_bdates) : ?>
    				<th>
    					<?php echo HTMLHelper::_('searchtools.sort','First','first_read',$listDirn,$listOrder ).'/'; ?>
    					<?php echo HTMLHelper::_('searchtools.sort','Last','last_read',$listDirn,$listOrder ).' read'; ?>
    				</th>
				<?php endif; ?>
                <?php if($this->show_ctcol) : ?>
     				<th class="hidden-phone">
    					<?php if ($this->showcat) {
    					    echo HtmlHelper::_('searchtools.sort','XBCULTURE_CATEGORY','category_title',$listDirn,$listOrder );
    					}
    					if (($this->showcat) && ($this->showtags)) {
    						echo ' &amp; ';
    					}
    					if ($this->showtags) {
    						echo JText::_( 'XBCULTURE_TAGS_U' );
    					} ?>
    				</th>
               <?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">	
              		<?php if($this->show_pic) : ?>
						<td>
						<?php  $src = trim($item->cover_img);
							if ((!$src=='') && (file_exists(JPATH_ROOT.'/'.$src))) : 
								$src = Uri::root().$src; 
								$tip = '<img src=\''.$src.'\' style=\'max-width:250px;\' />'; 
								?>
								<img class="img-polaroid hasTooltip xbimgthumb" title="" 
									data-original-title="<?php echo $tip; ?>" data-placement="right"
									src="<?php echo $src; ?>" border="0" alt="" />							                          
	                    	<?php  endif; ?>	                    
						</td>
                    <?php endif; ?>
					<td>
						<p class="xbtitle">
							<a href="<?php echo Route::_(XbbooksHelperRoute::getBookLink($item->id)) ;?>" >
								<b><?php echo $this->escape($item->title); ?></b></a> 
								&nbsp;<a href="" data-toggle="modal" data-target="#ajax-bpvmodal" onclick="window.pvid=<?php echo $item->id; ?>;"><i class="far fa-eye"></i></a>
						<?php if (!empty($item->subtitle)) :?>
                        	<br /><span class="xb09"><?php echo $this->escape($item->subtitle); ?></span>
                        <?php endif; ?>
						</p>
						<p class="xbmb0" style="margin-bottom:0;">
                        <?php if($item->editcnt>0) : ?>
                           	<span class="xbnit">
                            	<?php echo Text::_($item->editcnt>1 ? 'XBCULTURE_EDITORS' : 'XBCULTURE_EDITOR' ); ?>
                            </span>: <?php echo $item->editlist['commalist']; ?>
							<?php if ($item->authcnt == 1) : ?>
								<br /><span class="xbnit">
									<?php echo Text::_('XBCULTURE_AUTHOR'); ?>
								</span>: <?php echo $item->authlist['commalist']; ?>
							<?php  elseif ($item->authcnt > 1 ) : ?>
								<details>
									<summary><span class="xbnit"><?php echo Text::_('XBCULTURE_AUTHORS'); ?></span></summary>
									<span class="xb09"><?php echo $item->authlist['commalist']; ?></span>
								</details>
							<?php endif; ?>
                        <?php else : ?>
                        	<?php if ($item->authcnt==0) {
                        		echo '<span class="xbnit">'.Text::_('XBBOOKS_NOAUTHOR').'</span>';
                        	} else { ?> 
	                        	<span class="xbnit">
	                        		<?php echo Text::_($item->authcnt>1 ? 'XBCULTURE_AUTHORS' : 'XBCULTURE_AUTHOR' ); ?>
	                        	</span>: 
                        		<?php echo $item->authlist['commalist']; 
                        	} ?>                          	
                        <?php endif; ?>
						</p>
						<span class="xb09">
							<?php if($item->pubyear > 0) {
								echo '<span class="xbnit">'.Text::_('XBCULTURE_PUBLISHED').'</span>: '.$item->pubyear.'<br />'; 
							}?>																		
						</span></p>
					</td>
                    <?php if($this->show_sum) : ?>
					<td class="hidden-phone">
						<p class="xb095">
							<?php if (!empty($item->summary)) : ?>
								<?php echo $item->summary; ?>
    						<?php else : ?>
    							<span class="xbnit">
    							<?php if (!empty($item->synopsis)) : ?>
    								<?php echo Text::_('XBBOOKS_SYNOPSIS_EXTRACT'); ?>: </span>
    								<?php echo XbcultureHelper::makeSummaryText($item->synopsis,250); ?>
    							<?php else : ?>
            						<span class="xbnote">
    								<?php echo Text::_('XBBOOKS_NO_SUMMARY_SYNOPSIS'); ?>
    								</span></span>
    							<?php endif; ?>
    						<?php endif; ?>
                        </p>
                        <?php if (!empty($item->synopsis)) : ?>
                        	<p class="xbnit xb09">   
                             <?php 
                             	echo Text::_('XBCULTURE_SYNOPSIS').' '.str_word_count(strip_tags($item->synopsis)).' '.Text::_('XBCULTURE_WORDS'); 
                             ?>
							</p>
						<?php endif; ?>
					</td>
                	<?php endif; ?>
					<?php if ($this->showrevs != 0 ) : ?>
    					<td>
    						<?php if ($item->revcnt==0) : ?>
    						   <i><?php  echo ($this->showrevs == 1)? Text::_( 'XBCULTURE_NO_RATING' ) : Text::_( 'XBCULTURE_NO_REVIEW' ); ?></i><br />
    						<?php else : ?> 
	                        	<?php $stars = (round(($item->averat)*2)/2); ?>
	                            <div>
								<?php if (($this->zero_rating) && ($stars==0)) : ?>
								    <span class="<?php echo $this->zero_class; ?>"></span>
								<?php else : ?>
	                                <?php echo str_repeat('<i class="'.$this->star_class.'"></i>',intval($item->averat)); ?>
	                                <?php if (($item->averat - floor($item->averat))>0) : ?>
	                                    <i class="<?php echo $this->halfstar_class; ?>"></i>
	                                <?php  endif; ?> 
	                             <?php endif; ?>                        
	                             <?php if ($item->revcnt>1) : ?>                     
	                                    <br/><span style="color:darkgray;">Ave. <?php echo round($item->averat,1); ?> of <?php echo $item->revcnt; ?> reviews</span>  
                                 <?php endif; ?>
	                            </div>
                                
     							<?php if ($this->showrevs == 2) : ?>
                                    <?php foreach ($item->reviews as $rev) : ?>                                   	
										<div>
    										<?php if ($item->revcnt>1) : ?>
    											<b><?php echo $rev->rating;?></b> <i class="<?php echo $this->star_class; ?>"></i> 
    			                            <?php endif; ?>
                                            <?php if (!empty($rev->summary)) echo '<a href="'.$rlink.$rev->id.'">'; ?>
                                            <span class="xbpop xbcultpop xbhover xbmb8 xb09" data-trigger="hover" 
                                            	tabindex="<?php echo $rev->id; ?>" title 
    											data-content="<?php echo htmlentities($rev->summary.'<br /><i>by '.$rev->reviewer.'</i>'); ?>"
    											data-original-title="<?php echo htmlentities($rev->title); ?>" 
                                    		>
                                    			<?php  echo HtmlHelper::date($rev->rev_date , 'd M Y'); ?>
                                            </span>
                                            <?php if (!empty($rev->summary)) echo '</a>'; ?>
											&nbsp;<a href="" data-toggle="modal" data-target="#ajax-rpvmodal" onclick="window.pvid=<?php echo $rev->id; ?>;"><i class="far fa-eye"></i></a>
        								</div>
        							<?php endforeach; ?> 
        						<?php endif; ?>
     						<?php endif; ?>   											
    					</td>
    				<?php endif; ?>
    				<?php if($this->show_bdates) :?>
    				<td>
        					<p><?php if($item->first_read) {
        					    $datefmt = xbCultureHelper::getDateFmt($item->first_read);
        					    echo HtmlHelper::date($item->first_read , $datefmt);
        					   }
    					       echo '<br />';
    					       if(($item->last_read) && ($item->last_read != $item->first_read)) {
    					           $datefmt = xbCultureHelper::getDateFmt($item->last_read);
    					           echo HtmlHelper::date($item->last_read , $datefmt); 
        					   }
        					?> </p>
     				</td>
     				<?php endif; ?>
                    <?php if($this->show_ctcol) : ?>
					<td class="hidden-phone">
    					<?php if ($this->showfict) : ?>
    					   <?php if ($item->fiction==1) : ?>
    					   		<span class="label">fiction</span>
    					   <?php else : ?>
    					   		<span class="label label-inverse">non-fiction</span>
    					   <?php endif; ?> 
    					<?php endif; ?>
 						<?php if($this->showcat) : ?>
 							<p>
 							<?php if($this->showcat==2) : ?>											
								<a class="label label-success" href="<?php echo $clink.$item->catid; ?>"><?php echo $item->category_title; ?></a>
							<?php else: ?>
								<span class="label label-success"><?php echo $item->category_title; ?></span>
							<?php endif; ?>
							</p>
						<?php endif; ?>
    					<?php if($this->showtags) {
    						$tagLayout = new FileLayout('joomla.content.tags');
        					echo '<p>'.$tagLayout->render($item->tags).'</p>';
    					} ?>
					</td>
                	<?php endif; ?>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
	<?php endif; ?>
	<?php echo HtmlHelper::_('form.token'); ?>
      </div>
      </div>
</form>
</div>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
<script>
jQuery(document).ready(function(){
//for preview modals
    jQuery('#ajax-ppvmodal').on('show', function () {
        // Load view vith AJAX
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=booklist&layout=modalppv&tmpl=component');
    })
    jQuery('#ajax-ppvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
    jQuery('#ajax-bpvmodal').on('show', function () {
        // Load view vith AJAX
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=booklist&layout=modalbpv&tmpl=component');
    })
    jQuery('#ajax-bpvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
    jQuery('#ajax-rpvmodal').on('show', function () {
        // Load view vith AJAX
       jQuery(this).find('.modal-content').load('/index.php?option=com_xbbooks&view=booklist&layout=modalrpv&tmpl=component');
    })
    jQuery('#ajax-rpvmodal').on('hidden', function () {
       document.location.reload(true);
    })    
});
</script>
<!-- preview modal windows -->
<div class="modal fade xbpvmodal" id="ajax-ppvmodal" style="max-width:800px">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
<div class="modal fade xbpvmodal" id="ajax-bpvmodal" style="max-width:1000px">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>
<div class="modal fade xbpvmodal" id="ajax-rpvmodal" style="max-width:1000px">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Ajax content will be loaded here -->
        </div>
    </div>
</div>


