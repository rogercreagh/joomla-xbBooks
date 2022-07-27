<?php
/*******
 * @package xbBooks
 * @filesource site/views/character/tmpl/default.php
 * @version 0.9.8.9 10th June 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\FileLayout;

$item = $this->item;

require_once JPATH_COMPONENT.'/helpers/route.php';

$itemid = XbbooksHelperRoute::getCategoriesRoute();
$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
$clink = 'index.php?option=com_xbbooks&view=category'.$itemid.'&id=';

$imgok = (($this->show_image >0) && (JFile::exists(JPATH_ROOT.'/'.$item->image)));
if ($imgok) {
    $src = Uri::root().$item->image;
    $tip = '<img src=\''.$src.'\' style=\'width:400px;\' />';
}

?>
<div class="xbbooks">
<div class="row-fluid">
	<?php if ($imgok && ($this->show_image == 1)) : ?>
		<div class="span2">
			<img class="hasTooltip" title="" data-original-title="<?php echo $tip; ?>"
				 data-placement="right" src="<?php echo $src; ?>" border="0" alt="" style="max-width:100%;" />
		</div>
	<?php endif; ?>
	<div class="<?php echo $imgok==true ? 'span10' : 'span12'; ?>">
		<div class="row-fluid">
			<div class="span12">
				<div class="xbbox xbboxcyan">
					<h3><?php echo $item->name; ?></h3>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<?php if ($item->bcnt>0) : ?>
				<div class="span6">
					<div class="xbnit">
						<?php echo Text::_('XBCULTURE_BOOKS_U'); ?>
					</div>	
					<div class="xbml20">		
						<p><?php echo $item->clist; ?></p>
					</div>
				</div>
			<?php endif; ?>	
			<?php if ($item->summary != '') : ?>
				<div class="span6">
					<div class="xbnit xbmb8"><?php echo Text::_('XBCULTURE_SUMMARY'); ?></div>
					<div class="xbbox xbboxwht">
					 	<div class="xbsubtitle"><?php echo $item->summary; ?></div> 
					</div>
				</div>
			<?php  endif;?>
		</div>		
	</div>	
	<?php if ($imgok && ($this->show_image == 2)) : ?>
		<div class="span2">
			<img class="hasTooltip" title="" data-original-title="<?php echo $tip; ?>"
				 data-placement="left" src="<?php echo $src; ?>" border="0" alt="" style="max-width:100%;" />
		</div>
	<?php endif; ?>
</div>
<div class="row-fluid">
	<div class="span12">
		<?php if(trim($item->description)=='') : ?>
			<p><i><?php echo Text::_('XBCULTURE_NO_DESCRIPTION');?></i></p>
		<?php else: ?>
			<div class="xbnit xbmb8"><?php echo Text::_('XBCULTURE_DESCRIPTION');?></div>
			<div class="xbbox xbboxcyan">
				<?php echo $item->description; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<div class="row-fluid">
	<?php if ($this->show_cat) : ?>
		<div class="span5">
			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_CATEGORY'); ?></div>
			<div class="pull-left label label-success">
				<?php if ($this->show_cat==2) : ?>
					<a href="<?php echo $clink.$item->catid; ?>"><?php echo $item->category_title; ?></a>
				<?php else : ?>
					<?php echo $item->category_title; ?>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>
		</div>
	<?php endif; ?>
	<?php if(($this->show_tags) && (!empty($item->tags))) : ?>
		<div class="span<?php $this->showcat ? '7' : '12';?>">
			<div class="pull-left xbnit xbmr10"><?php echo Text::_('XBCULTURE_TAGS_U'); ?></div>
			<div class="pull-left">
				<?php  $tagLayout = new FileLayout('joomla.content.tags');
			    	echo $tagLayout->render($item->tags);
			    ?>
			</div>	
		</div>
	<?php endif; ?>			
</div>
<div class="row-fluid">
	<div class="span12 xbbox xbboxgrey">
		<div class="row-fluid">
			<div class="span2">
				<?php if (($item->prev>0) || ($item->next>0)) : ?>
				<span class="xbpop xbcultpop xbinfo fas fa-info-circle" data-trigger="hover" title 
					data-original-title="Prev-Next Info" data-content="<?php echo JText::_('XBBOOKS_INFO_PREVNEXT'); ?>" >
				</span>&nbsp;
				<?php endif; ?>
				<?php if($item->prev > 0) : ?>
					<a href="index.php?option=com_xbbooks&view=character&id=<?php echo $item->prev ?>" class="btn btn-small">
						<?php echo Text::_('XBCULTURE_PREV'); ?></a>
			    <?php endif; ?>
			</div>
			<div class="span8"><center>
				<a href="index.php?option=com_xbbooks&view=characters" class="btn btn-small">
					<?php echo Text::_('XBCULTURE_CHAR_LIST'); ?></a></center>
			</div>
			<div class="span2">
			<?php if($item->next > 0) : ?>
				<a href="index.php?option=com_xbbooks&view=character&id=<?php echo $item->next ?>" class="btn btn-small pull-right">
					<?php echo Text::_('XBCULTURE_NEXT'); ?></a>
		    <?php endif; ?>
			</div>
	      </div>
      </div>
</div>
<div class="clearfix"></div>
<p><?php echo XbcultureHelper::credit('xbBooks');?></p>
</div>
