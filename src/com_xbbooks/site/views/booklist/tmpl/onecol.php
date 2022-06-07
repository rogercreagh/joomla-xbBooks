<?php 
/*******
 * @package xbBooks
 * @filesource site/views/booklist/tmpl/onecol.php
 * @version 0.9.8.7 4th June 2022
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
HTMLHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='sort_date';
    $orderDrn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'),'pubyear'=>Text::_('XBBOOKS_YEARPUB'), 'averat'=>Text::_('XBCULTURE_AVERAGE_RATING'), 
    'acq_date'=>Text::_('XBCULTURE_ACQ_DATE'),'sort_date'=>Text::_('XBCULTURE_SORT_DATE'), 'category_title'=>Text::_('XBCULTURE_CATEGORY'));

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbbooks&view=category'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getBooksRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$blink = 'index.php?option=com_xbbooks&view=book'.$itemid.'&id=';

$itemid = XbbooksHelperRoute::getReviewsRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$rlink = 'index.php?option=com_xbbooks&view=bookreview'.$itemid.'&id=';

?>
<div class="xbbooks">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
		echo XbbooksHelper::sitePageheader($this->header);
	} ?>
	
	<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=booklist&layout=onecol'); ?>" method="post" name="adminForm" id="adminForm">       
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
				<th>
					<?php echo HtmlHelper::_('searchtools.sort','XBCULTURE_TITLE','title',$listDirn,$listOrder).				
    						', '.Text::_('XBCULTURE_AUTHOR').', '.
    						HtmlHelper::_('searchtools.sort','XBBOOKS_PUBYEARCOL','pubyear',$listDirn,$listOrder );
    						  echo ', '.HtmlHelper::_('searchtools.sort','PubYear','pubyear',$listDirn,$listOrder );	
					?>
				</th>					
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $reviews = ''; ?>
				<tr class="row<?php echo $i % 2; ?>">	
					<td>
						<h3>
							<a href="<?php echo Route::_(XbbooksHelperRoute::getBookLink($item->id)) ;?>" >
								<b><?php echo $this->escape($item->title); ?></b></a>
						<?php if (!empty($item->subtitle)) :?>
                        	<br /><span class="xb08" style="padding-left:15px;"><?php echo $this->escape($item->subtitle); ?></span>
                        <?php endif; ?>
						</h3>
                  		<?php if($this->show_pic) : ?>
                          <div class="pull-left" style="width:90px;margin-right:20px;">
    						<?php  $src = trim($item->cover_img);
    							if ((!$src=='') && (file_exists(JPATH_ROOT.'/'.$src))) : 
    								$src = Uri::root().$src; 
    								$tip = '<img src=\''.$src.'\' style=\'max-width:250px;\' />'; 
    								?>
    								<img class="img-polaroid hasTooltip" title="" 
    									data-original-title="<?php echo $tip; ?> data-placement="right"
    									src="<?php echo $src; ?>" border="0" alt="" />							                          
    	                     <?php  endif; ?>
                          </div>   
                        <?php endif; ?>
						<p>
                        <?php if($item->editcnt>0) : ?>
                           	<?php if ($item->authcnt>0) {
								echo '<span class="hasTooltip" title data-original-title="Authors: '.$item->alist.'">';
                            } else {
                              echo '<span>';
                            } ?>
                       	<span class="xbnit">
                        		<?php echo Text::_($item->editcnt>1 ? 'XBCULTURE_EDITORS' : 'XBCULTURE_EDITOR' ); ?>
                        	</span></span>: 
                        	<?php echo $item->elist; ?>
                        <?php else : ?>
                        	<?php if ($item->authcnt==0) {
                        		echo '<span class="xbnit">'.Text::_('XBBOOKS_NOAUTHOR').'</span>';
                        	} else { ?> 
	                        	<span class="xbnit">
	                        		<?php echo Text::_($item->authcnt>1 ? 'XBCULTURE_AUTHORS' : 'XBCULTURE_AUTHOR' ); ?>
	                        	</span>: 
                        		<?php echo $item->alist; 
                        	} ?>                          	
                        <?php endif; ?>
						</p>
						
						<p>
							<span class="icon-calendar"></span>&nbsp;<span class="xbnit">
								<?php echo Text::_('XBCULTURE_PUBLISHED'); ?>
							</span>
							<?php if($item->pubyear > 0) { echo ': '.$item->pubyear; }?>	
							<br />
							<span class="icon-book"></span>&nbsp;
                            <?php if($this->show_sum) : ?>
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
                                <?php if (!empty($item->synopsis)) : ?>
                                	&nbsp;<span class="xbnit xb09">   
                                     <?php 
                                     	echo Text::_('XBCULTURE_SYNOPSIS').' '.str_word_count(strip_tags($item->synopsis)).' '.Text::_('XBCULTURE_WORDS'); 
                                     ?>
                                     </span>
        						<?php endif; ?>
                        	<?php endif; ?>
							<br />						
		                    <?php if($this->show_ctcol) : ?>
		     					<?php if($this->showcats) : ?>
		     						<span class="icon-folder"></span> &nbsp;										
	    							<a class="label label-success" href="<?php echo $clink.$item->catid; ?>"><?php echo $item->category_title; ?></a>
    		    					<?php echo ($item->fiction==1) ? ' <span class="label">fiction</span>' : ' <span class="label label-inverse">non-fiction</span>'; ?>
    		    					<br />
		    					<?php endif; ?>
        						<?php if($this->showtags) {
        						    echo '<span class="icon-tags"></span> &nbsp;';
        							$tagLayout = new FileLayout('joomla.content.tagline');
            						echo $tagLayout->render($item->tags);
        						}
            					?>
            					</p>
	                		<?php endif; ?>
					</td>
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

