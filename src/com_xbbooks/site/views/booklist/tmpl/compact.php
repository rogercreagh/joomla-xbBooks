<?php
/*******
 * @package xbBooks
 * @filesource site/views/booklist/tmpl/compact.php
 * @version 0.9.9.9 31st October 2022
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

HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => Text::_('JOPTION_SELECT_TAG')));
HtmlHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape(strtolower($this->state->get('list.direction')));
if (!$listOrder) {
    $listOrder='last_read';
    $orderDrn = 'descending';
}
$orderNames = array('title'=>Text::_('XBCULTURE_TITLE'), 'averat'=>'Average Rating', 
    'first_read'=>Text::_('First Read'), 'last_read'=>Text::_('Last Read'), );

require_once JPATH_COMPONENT.'/helpers/route.php';

?>
<div class="xbbooks">
	<?php if(($this->header['showheading']) || ($this->header['title'] != '') || ($this->header['text'] != '')) {
	    echo XbcultureHelper::sitePageheader($this->header);
	} ?>
	
	<form action="<?php echo Route::_('index.php?option=com_xbbooks&view=booklist&layout=compact'); ?>" method="post" name="adminForm" id="adminForm">       
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
                <?php if ($this->showrevs != 0 ) : ?>
    				<th class="xbtc">
    					<?php echo HTMLHelper::_('searchtools.sort','XBCULTURE_RATING','averat',$listDirn,$listOrder); ?>
    				</th>
				<?php endif; ?>
				<?php if ($this->show_bdates) : ?>				
    				<th class="hidden-phone">
    					<?php echo HTMLHelper::_('searchtools.sort','First','first_read',$listDirn,$listOrder ).'-'; ?>
    					<?php echo HTMLHelper::_('searchtools.sort','Last','last_read',$listDirn,$listOrder ).' read'; ?>
    				</th>
    			<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $reviews = ''; ?>
				<tr class="row<?php echo $i % 2; ?>">	
					<td>
						<p class="xbtitle">
							<a href="<?php echo Route::_(XbbooksHelperRoute::getBookLink($item->id)) ;?>" >
								<b><?php echo $this->escape($item->title); ?></b></a> 
						<?php if (!empty($item->subtitle)) :?>
                        	<br /><span class="xb09 xbnorm" style="padding-left:15px;"><?php echo $this->escape($item->subtitle); ?></span>
                        <?php endif; ?>
						</p>
					</td>
					<td>
						<p>
                        <?php if($item->editcnt>0) : ?>
                           	<?php if ($item->authcnt>0) {
								echo '<span class="xbpop xbcultpop xbfocus" data-trigger="focus" tabindex="'.$item->id.'" 
                                    title data-original-title="Authors: " data-content="'.$item->alist.'">';
                            } else {
                              echo '<span>';
                            } ?>
                       	<span class="xbnit">
                        		<?php echo Text::_($item->editcnt>1 ? 'Eds.' : 'Ed.' ); ?>
                        	</span></span>: 
                        	<?php echo $item->elist; ?>
                        <?php else : ?>
                        	<?php if ($item->authcnt==0) {
                        		echo '<span class="xbnit">'.Text::_('XBBOOKS_NOAUTHOR').'</span>';
                        	} else { ?> 
                        		<?php echo $item->alist; 
                        	} ?>                          	
                        <?php endif; ?>
						</p>
					</td>
					<?php if ($this->showrevs != 0 ) : ?>					
    					<td>
    						<?php if ($item->revcnt==0) : ?>
    						   <i><?php  echo ($this->showrevs == 1)? Text::_( 'Not rated yet' ) : Text::_( 'XBBOOKS_NOREVIEW' ); ?></i><br />
    						<?php else : ?> 
	                        	<?php $stars = (round(($item->averat)*2)/2); ?>
	                            <div class="xbstar">
								<?php if (($this->zero_rating) && ($stars==0)) : ?>
								    <span class="<?php echo $this->zero_class; ?>" style="color:red;"></span>
								<?php else : ?>
	                                <?php echo str_repeat('<i class="'.$this->star_class.'"></i>',intval($item->averat)); ?>
	                                <?php if (($item->averat - floor($item->averat))>0) : ?>
	                                    <i class="<?php echo $this->halfstar_class; ?>"></i>
	                                    <span style="color:darkgray;"> (<?php echo round($item->averat,1); ?>)</span>                                   
	                                <?php  endif; ?> 
	                             <?php endif; ?>                        
	                            </div>
    						<?php endif; ?>    											
    					</td>
    				<?php endif; ?>
    				<?php if ($this->show_bdates ) : ?>   				
    					<td class="hidden-phone">
        					<p><?php if($item->first_read) {
        					    $datefmt = xbCultureHelper::getDateFmt($item->first_read, 'j M Y');
        					    echo HtmlHelper::date($item->first_read , $datefmt);
        					   }
    					       echo ' - ';
    					       if(($item->last_read) && ($item->last_read != $item->first_read)) {
        					       $datefmt = xbCultureHelper::getDateFmt($item->last_read, 'j M Y');
        					       echo HtmlHelper::date($item->last_read , $datefmt); 
        					   }
        					?> </p>
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

