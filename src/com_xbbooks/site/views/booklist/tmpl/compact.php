<?php
/*******
 * @package xbBooks
 * @filesource site/views/booklist/tmpl/compact.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\LayoutHelper;

HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HtmlHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='cat_date';
    $orderDrn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'), 'averat'=>'Average Rating', 'cat_date'=>'Last Read');

require_once JPATH_COMPONENT.'/helpers/route.php';

?>
<div class="xbbooks">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
		echo XbbooksHelper::sitePageheader($this->header);
	} ?>
	
	<form action="<?php echo JRoute::_('index.php?option=com_xbbooks&view=booklist'); ?>" method="post" name="adminForm" id="adminForm">       
		<?php  // Search tools bar
			if ($this->search_bar) {
				$hide = '';
				if ($this->hide_fict) { $hide .= 'filter_fictionfilt,';}
				if ($this->hide_peep) { $hide .= 'filter_perfilt,filter_prole,';}
				if ($this->hide_char) { $hide .= 'filter_charfilt,';}
				if ((!$this->show_cat) || $this->hide_cat) { $hide .= 'filter_category_id,filter_subcats,';}
				if ((!$this->show_tags) || $this->hide_tag) { $hide .= 'filter_tagfilt,filter_taglogic,';}
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

	<table class="table table-striped table-hover"  id="xbbooklist">	
		<thead>
			<tr>
				<th>
					<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_TITLE','title',$listDirn,$listOrder);				
					?>
				</th>					
				<th>
					<?php echo Text::_('XBCULTURE_AUTHOR');?>
				</th>
				<th class="xbtc">
					<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_RATING','averat',$listDirn,$listOrder); ?>
				</th>
				<th class="hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort','COM_XBBOOKS_DATE_READ','cat_date',$listDirn,$listOrder ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $reviews = ''; ?>
				<tr class="row<?php echo $i % 2; ?>">	
					<td>
						<p class="xbtitle">
							<a href="<?php echo JRoute::_(XbbooksHelperRoute::getBookLink($item->id)) ;?>" >
								<b><?php echo $this->escape($item->title); ?></b></a> 
						<?php if (!empty($item->subtitle)) :?>
                        	<br /><span class="xb095 xbnorm"><?php echo $this->escape($item->subtitle); ?></span>
                        <?php endif; ?>
						</p>
					</td>
					<td>
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
                        		echo '<span class="xbnit">'.Text::_('COM_XBBOOKS_NOAUTHOR').'</span>';
                        	} else { ?> 
	                        	<span class="xbnit">
	                        		<?php // echo Text::_($item->authcnt>1 ? 'XBCULTURE_AUTHORS' : 'XBCULTURE_AUTHOR' ); ?>
	                        	</span>: 
                        		<?php echo $item->alist; 
                        	} ?>                          	
                        <?php endif; ?>
						</p>
					</td>
					<td>
						<?php if ($item->revcnt==0) : ?>
						   <?php  echo '<i>'.Text::_( 'COM_XBBOOKS_NOREVIEW' ).'</i><br />'; ?>
						<?php else : ?> 
                            <?php foreach ($item->reviews as $rev) : ?>
								<div class="xb09">
    								<?php if (($this->zero_rating) && ($rev->rating==0)) {
    									echo '<span class="'.$this->zero_class.' "></span>';
    								} else {
    									echo str_repeat('&#11088',$rev->rating);
    								}?>
						        </div>
							<?php endforeach; ?>
							<?php if ($item->revcnt>1) : ?>
								<div class="center" style="border-top:solid 1px lightgray;">
									<span class="xbnt">Average rating</span>: <b><?php echo number_format($item->averat,2); ?></b>
								</div>
							<?php endif; ?>
						<?php endif; ?>
											
					</td>
					<td class="hidden-phone">
    					<p><?php if($item->lastread) {
    						echo HtmlHelper::date($item->lastread , 'd M Y'); 
    					}?> </p>
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

