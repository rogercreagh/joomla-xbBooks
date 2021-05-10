<?php 
/*******
 * @package xbBooks
 * @filesource site/views/blog/tmpl/default.php
 * @version 0.9.5 10th May 2021
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='rev_date';
    $orderDrn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_CAPTITLE'),'book_title'=>Text::_('COM_XBBOOKS_BOOK_TITLE'),
    'rating'=>Text::_('XBCULTURE_CAPRATING'), 'rev_date'=>Text::_('COM_XBBOOKS_DATE_READ'),
    'category_title'=>Text::_('XBCULTURE_CAPCATEGORY'));

require_once JPATH_COMPONENT.'/helpers/route.php';

//$itemid = XbbooksHelperRoute::getBooksRoute();
//$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
//$flink = 'index.php?option=com_xbbooks&view=book' . $itemid.'&id=';
//$flink = 'index.php?option=com_xbbooks&view=book&id=';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbbooks&view=category' . $itemid.'&id=';

?>
<div class="xbbooks">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
		echo XbbooksHelper::sitePageheader($this->header);
	} ?>
	
<form action="<?php echo JRoute::_('index.php?option=com_xbbooks&view=blog'); ?>" method="post" name="adminForm" id="adminForm">
		<?php  // Search tools bar
			if ($this->search_bar) {
				$hide = '';
				if ((!$this->show_bcat) || ($this->hide_cat)) { $hide .= 'filter_bcategory_id,';}
				if ((!$this->show_rcat) || ($this->hide_cat)) { $hide .= 'filter_category_id,';}
				if (((!$this->show_rcat) && (!$this->show_bcat)) || ($this->hide_cat)) { $hide .= 'filter_subcats,';}
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
	<?php $evenrow = false; ?>
	<?php foreach ($this->items as $i => $item) : ?>
		<?php $imgok = (JFile::exists(JPATH_ROOT.'/'.$item->cover_img));
			if ($imgok) {
				$src = Uri::root().$item->cover_img;
				$tip = '<img src=\''.$src.'\' style=\'width:400px;\' />';
			}
			$rowcol = ($evenrow) ? 'xbboxcyan' : 'xbboxmag';
			$evenrow = !$evenrow;
		?>
        <div class="xbbox xbboxwht">  
        <h4><?php echo HtmlHelper::date($item->rev_date , Text::_('l jS F Y')); ?></h4>
		<div class="row-fluid">
			<div class="xbbox <?php echo $rowcol; ?>">
				<div class="row-fluid">
					<?php if ($imgok) : ?>
						<div class="span2">
							<img class="img-polaroid hasTooltip xbimgthumb" title="" 
								data-original-title="<?php echo $tip; ?>" data-placement="right"
								src="<?php echo $src; ?>"
								border="0" alt="" />							                          
						</div>
					<?php endif; ?>
					<div class="span<?php echo ($imgok) ? '9' : '11'; ?>" >
						<div class="pull-right xbmr10" style="text-align:right;">
	                    	<div class="xbstar">
	                    		<p></p>
								<?php if($item->ratcnt > 1) { 
									echo 'Average rating from '.$item->ratcnt.' reviews';} 
									$thisrat = $item->averat;
								?>
								<?php if (($this->zero_rating) && ($thisrat==0)) : ?>
								    <span class="<?php echo $this->zero_class; ?>" style="color:red;font-size=1.5em;"></span>
								<?php else : ?>
	                                <?php echo str_repeat('<i class="'.$this->star_class.' xb12"></i>',$thisrat); ?>
								<?php endif; ?>                        
	                        </div>
							<h4 ><?php echo $item->pubyear; ?></h4>
								<?php if($item->orig_lang !='') : ?>
									<p><i>Original Language: </i><?php echo $item->orig_lang; ?> mins</p>
								<?php endif; ?>
						</div>
						<?php $flink = XbbooksHelperRoute::getBookLink($item->book_id);	?>
						<h2><a href="<?php echo JRoute::_($flink);?>"><?php echo $item->book_title; ?></a></h2>
				       	<?php if (!$item->subtitle == '') : ?>
							<h3><?php  echo $item->subtitle; ?></h3>
				       	<?php endif; ?>
						<div class="row-fluid">
							<div class="span9">
		                        <?php if ($item->acnt>0) : ?>
									<h4><span class="xbnit xbmr10">
										<?php echo Text::_(($item->acnt==1) ? 'XBCULTURE_CAPAUTHOR' : 'XBCULTURE_CAPAUTHORS'); ?>
									: </span>
									<?php echo $item->alist; ?>                          
									</h4>
								<?php else: ?>
									<p class="xbnit"><?php echo Text::_('no author listed'); ?></p>
		                        <?php endif; ?>
							</div>
						</div>   						
					</div>
				</div>
			</div>
		</div>
		<?php if ((trim($item->book_summary) != '') || (trim($item->synopsis) != '')): ?>
		<div class="row-fluid">
			<div class="span6">
				<?php $sumtext =  trim($item->book_summary);
				$sumlabel = 'Book Summary';
				if ($sumtext == '') {
					$sumtext = XbcultureHelper::makeSummaryText($item->synopsis, 0);
					$sumlabel='Synopsis extract';
				}
				if ( $sumtext != '') : ?>
					<div class="xbbox xbboxwht">
						<div class="pull-left"><span class="xbnit"><?php echo Text::_('XBCULTURE_CAPSUMMARY'); ?> 
						: </span></div>
					 	<div><?php echo $sumtext; ?></div> 
					</div>
				<?php  endif;?>
			</div>
			<div class="span6">
				<?php if ($this->show_bcat) : ?>
					<div class="pull-left xbnit xbmr10"><?php echo Text::_('Book category'); ?></div>
					<div class="pull-left">
					<?php if ($this->show_bcat == 2) : ?>
    					<a class="label label-success" href="<?php echo JRoute::_($clink.$item->catid); ?>">
    						<?php echo $item->bcat_title; ?></a>
    				<?php else : ?>
    					<span class="label label-success">
    					<?php echo $item->bcat_title; ?></a>
    					</span>
					<?php endif; ?>
	    			</div>	
	                <div class="clearfix"></div>
				<?php endif; ?>
				<?php if ($this->show_btags) : ?>
					<?php if (!empty($item->btags)) : ?>
						<div class="pull-left xbnit xbmr10"><?php echo Text::_('Book Tags'); ?>
						</div>
						<div class="pull-left">
							<?php  $tagLayout = new JLayoutFile('joomla.content.tags');
			    				echo $tagLayout->render($item->btags); ?>
						</div>
	                	<div class="clearfix"></div>
					<?php endif; ?>    
				<?php endif; ?>
			</div>
		</div>	
		<?php endif; ?>	
		<div class="row-fluid"><div class="span1"></div>
			<div class="span5">
				<p><span class="xbnit"> 
					<?php echo Text::_(trim($item->review != '') ? 'Reviewed' : 'Rated').' by '; ?> </span>
					<b><?php echo $item->reviewer; ?></b>,  
					<?php echo Text::_('COM_XBBOOKS_ON').'&nbsp;'.HtmlHelper::date($item->rev_date , Text::_('d M Y')) ; ?> 
				</p> 
			</div>
			<div class="span6">
				<?php $sumtext =  trim($item->summary);
				if ( $sumtext != '') : ?>
					<div class="xbbox xbboxwht">
						<div class="pull-left"><span class="xbnit"><?php echo Text::_('Review Summary'); ?> 
						: </span></div>
					 	<div><?php echo $sumtext; ?></div> 
					</div>
				<?php else: ?>
				<?php  endif;?>
			</div>
		</div>	
		<?php if (trim($item->review != '')) : ?>
		<div class="xbbox <?php echo $rowcol; ?>">
			<div class="row-fluid">
				<div class="span2">
					<div>
             			<h3></h3>
						<?php if (($this->zero_rating) && ($item->rating==0)) : ?>
					    	<span class="<?php echo $this->zero_class; ?> xb12"></span>
						<?php else : ?>
							<?php echo str_repeat('<i class="'.$this->star_class.'"></i>',$item->rating); ?>
						<?php endif; ?>                        
					</div>
				</div>
				<div class="span8">
					<h3><?php echo $item->title; ?></h3>
					<?php echo $item->review; ?>
				</div>
			</div>
			<?php if (($this->show_rcat) || ($this->show_rtags)) { echo '<hr />'; } ?>
			<div class="row-fluid">
			<?php if($this->show_rcat) : ?>
				<div class="span4">
					<div class="pull-left xbnit xbmr10"><?php echo Text::_('Review category'); ?></div>
					<div class="pull-left">
						<?php if($this->show_rcat ==2) : ?>
	    					<a class="label label-success" href="<?php echo JRoute::_($clink.$item->catid); ?>">
	    						<?php echo $item->category_title; ?></a>
	    				<?php else : ?>
    						<span class="label label-success">
	    					<?php echo $item->category_title; ?></a>
	    					</span>
						<?php endif; ?>
	    			</div>	
	                <div class="clearfix"></div>
				</div>
			<?php endif; ?>
			<?php if ($this->show_rtags) : ?>
		       	<div class="span<?php echo ($this->show_fcat) ? '8' : '12'; ?>">
				<?php if (!empty($item->tags)) : ?>
					<div class="pull-left xbnit xbmr10"><?php echo Text::_('Review Tags'); ?>
					</div>
					<div class="pull-left">
						<?php  $tagLayout = new JLayoutFile('joomla.content.tags');
		    				echo $tagLayout->render($item->tags); ?>
					</div>
				<?php endif; ?>    
	                <div class="clearfix"></div>
				</div>
			<?php  endif; ?>
			</div>
		</div>
		<?php endif; ?>														
		</div>
		<br /><hr /><br />
	<?php endforeach; ?>
	<?php echo $this->pagination->getListFooter(); ?>
<?php endif; ?>
<?php echo HTMLHelper::_('form.token'); ?>
</div>
	</div>
</form>
<div class="clearfix"></div>
<p><?php echo XbbooksGeneral::credit();?></p>
</div>

