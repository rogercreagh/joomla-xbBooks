<?php
/*******
 * @package xbBooks
 * @filesource admin/views/cpanel/tmpl/default.php
 * @version 0.9.6.c 6th January 2022
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2021
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.html.html.bootstrap');

$belink='index.php?option=com_xbbooks&view=book&layout=edit&id=';
$relink='index.php?option=com_xbbooks&view=review&layout=edit&id=';
$pelink='index.php?option=com_xbbooks&view=person&layout=edit&id=';
$chelink='index.php?option=com_xbbooks&view=character&layout=edit&id=';

if (!$this->xbpeople_ok) : ?>
    <div class="alert alert-error">
        <h4>Warning - xbPeople Component appears not to be installed</h4>
        <p>It should have been installed with pkg_xbbooks_xxx.zip. Without it xbBooks will not work correctly. It should be installed with the xbBooks Package. All front-end xbBooks and other xbCulture pages will generate a 404 error.
        <br />To re-install xbPeople either reinstall the xbBooks pkg or copy this url <b> http://www.crosborne.uk/downloads?download=11 </b>, and use it on the 
        	<a href="index.php?option=com_installer&view=install#url">Install from URL</a> page.
        </p>
    </div>
<?php else: ?>

<form action="<?php echo JRoute::_('index.php?option=com_xbbooks&view=cpanel'); ?>" method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div id="j-sidebar-container">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" >
		<h4><?php echo Text::_( 'XBCULTURE_SUMMARY' ); ?></h4>
        <div class="row-fluid">
        	<div class="span5">
			<div class="xbbox xbboxcyan">
				<h2 class="xbtitle">
					<span class="badge badge-info pull-right"><?php echo $this->bookStates['total']; ?></span> 
					<?php echo Text::_('XBCULTURE_CAPBOOKS'); ?>
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbmr10"><?php echo $this->bookStates['published']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_PUBLISHED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->bookStates['unpublished']>0 ?'badge-yellow' : ''; ?> xbmr10"><?php echo $this->bookStates['unpublished']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNPUBLISHED'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge <?php echo $this->bookStates['archived']>0 ?'badge-warning' : ''; ?> xbmr10"><?php echo $this->bookStates['archived']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPARCHIVED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->bookStates['trashed']>0 ?'badge-important' : ''; ?> xbmr10"><?php echo $this->bookStates['trashed']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPTRASHED'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge xbml10"><?php echo $this->books['fiction']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_CAPFICTION'); ?>
						</div>
						<div class="span6">
							<span class="badge badge-inverse xbml10"><?php echo $this->books['nonfiction']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_CAPNONFICTION'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbml10"><?php echo $this->books['reviewed']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_REVIEWED'); ?>
						</div>
						<div class="span6">
							<span class="badge badge-important xbml10"><?php echo $this->bookStates['total']-$this->books['reviewed']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNREVIEWED'); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="xbbox xbboxmag">
				<h2 class="xbtitle">
					<span class="badge badge-info pull-right">
						<?php echo $this->revStates['total']; ?>
					</span> 
					<?php echo Text::_('XBCULTURE_CAPREVIEWS'); ?>
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbmr10"><?php echo $this->revStates['published']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_PUBLISHED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->revStates['unpublished']>0 ?'badge-yellow' : ''; ?> xbmr10"><?php echo $this->revStates['unpublished']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNPUBLISHED'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge <?php echo $this->revStates['archived']>0 ?'badge-warning' : ''; ?> xbmr10"><?php echo $this->revStates['archived']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPARCHIVED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->revStates['trashed']>0 ?'badge-important' : ''; ?> xbmr10"><?php echo $this->revStates['trashed']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPTRASHED'); ?>
						</div>
					</div>
				</div>
				<h2 class="xbsubtitle"><?php echo Text::_('COM_XBBOOKS_PUBRATINGS');?></h2>
				<div class="xbratingrow">
					<div class="row-fluid clearfix">
                    	<table style="width:100%;"><tr>
                    		<tr>
	     						<?php $s = $this->zero_rating ? 0 : 1;
	     						for ($i = $s; $i < 8; $i++): ?>
		                     		<td class="center xbstarcell">
							        	<?php if (($this->zero_rating) && ($i==0)) {
							            	echo '<span class="'.$this->zero_class.' "></span>';
							        	} else { ?>
			                      			<span style="font-size:9px;">
			                      				<?php echo str_repeat('&#11088',$i); ?>
			                      			</span>
			                          	<?php } //endif; ?>
		                      		</td>
	                      		<?php endfor; ?>
                      		</tr><tr>
								<?php for ($i = $s; $i < 8; $i++): ?>
                     				<td class="center" style="padding-top:5px;">
                       					<span class="badge <?php echo (key_exists($i,$this->ratCnts)) ? 'badge-info':''; ?> " >
                       					<?php echo (key_exists($i,$this->ratCnts))? $this->ratCnts[$i]:'0';?></span>
	                    			</td>
                      			<?php endfor; ?>
							</tr>
						</table>
					</div>
				</div>
			</div>			
			<div class="xbbox xbboxgrn">
				<h2 class="xbtitle"><?php echo Text::_('XBCULTURE_CAPPEOPLE'); ?>
					 <span class="pull-right"><span class="xbnit xbmr10 xb09">Total: </span><span class="badge percnt xbmr20"><?php echo $this->totPeople;?></span>
					 <span class="xbnit xbmr10 xb09">In Books: </span><span class="badge badge-info "><?php echo $this->perStates['total'];?></span></span>	
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbmr10"><?php echo $this->perStates['published']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_PUBLISHED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->perStates['unpublished']>0 ?'badge-yellow' : ''; ?> xbmr10"><?php echo $this->perStates['unpublished']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNPUBLISHED'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge <?php echo $this->perStates['archived']>0 ?'badge-warning' : ''; ?> xbmr10"><?php echo $this->perStates['archived']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPARCHIVED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->perStates['archived']>0 ?'badge-important' : ''; ?> xbmr10"><?php echo $this->perStates['trashed']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPTRASHED'); ?>
						</div>
					</div>

					<div class="row-fluid">
						<div class="span1"></div><div class="span11">
							<span class="badge badge-info xbmr10"><?php echo $this->people['authpub']+$this->people['authunpub'];?></span>
							<?php echo Text::_('XBCULTURE_CAPAUTHORS'); ?>
							<span class="pull-right" style="text-transform:none;font-weight:normal;font-size:10px;">
							 	<?php echo Text::_('XBCULTURE_PUBCOLON'); ?> 				
							 	<span class="badge badge-success xbmr10"><?php echo $this->people['authpub'];?></span>
								<?php echo Text::_('XBCULTURE_UNPUBCOLON'); ?>
								<span class="badge <?php echo $this->people['authunpub']>0 ?'badge-important' : ''; ?>"><?php echo $this->people['authunpub'];?></span>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span1"></div><div class="span11">
							<span class="badge badge-info xbmr10"><?php echo $this->people['editpub']+$this->people['editunpub'];?></span>
							<?php echo Text::_('COM_XBBOOKS_CAPEDITORS'); ?>
							<span class="pull-right" style="text-transform:none;font-weight:normal;font-size:10px;">
							 	<?php echo Text::_('XBCULTURE_PUBCOLON'); ?> 				
							 	<span class="badge badge-success xbmr10"><?php echo $this->people['editpub'];?></span>
								<?php echo Text::_('XBCULTURE_UNPUBCOLON'); ?>
								<span class="badge <?php echo $this->people['editunpub']>0 ?'badge-important' : ''; ?>"><?php echo $this->people['editunpub'];?></span>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span1"></div><div class="span11">
							<span class="badge badge-info xbmr10"><?php echo $this->people['menpub']+$this->people['menunpub'];?></span>
							<?php echo Text::_('COM_XBBOOKS_CAPMENTIONED'); ?>
							<span class="pull-right" style="text-transform:none;font-weight:normal;font-size:10px;">
							 	<?php echo Text::_('XBCULTURE_PUBCOLON'); ?> 				
							 	<span class="badge badge-success xbmr10"><?php echo $this->people['menpub'];?></span>
								<?php echo Text::_('XBCULTURE_UNPUBCOLON'); ?>
								<span class="badge <?php echo $this->people['menunpub']>0 ?'badge-important' : ''; ?>"><?php echo $this->people['menunpub'];?></span>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span1"></div><div class="span11">
							<span class="badge badge-info xbmr10"><?php echo $this->people['otherpub']+$this->people['otherunpub'];?></span>
							<?php echo Text::_('XBCULTURE_CAPOTHERS'); ?>
							<span class="pull-right" style="text-transform:none;font-weight:normal;font-size:10px;">
							 	<?php echo Text::_('XBCULTURE_PUBCOLON'); ?> 				
							 	<span class="badge badge-success xbmr10"><?php echo $this->people['otherpub'];?></span>
								<?php echo Text::_('XBCULTURE_UNPUBCOLON'); ?>
								<span class="badge <?php echo $this->people['otherunpub']>0 ?'badge-important' : ''; ?>"><?php echo $this->people['otherunpub'];?></span>
							</span>
							<?php if(!empty($this->otherRoles)) :?>
								<br /><span class="xbnit xbmr10">Other roles:</</span>
								<?php echo implode(', ',$this->otherRoles); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="xbbox xbboxcyan">
				<h2 class="xbtitle">
					 <span class="pull-right"><span class="xbnit xbmr10 xb09">Total: </span><span class="badge chcnt xbmr20"><?php echo $this->totChars;?></span>
					 <span class="xbnit xbmr10 xb09">In Books: </span><span class="badge badge-info "><?php echo $this->charStates['total'];?></span></span>	
					<?php echo Text::_('XBCULTURE_CAPCHARACTERS'); ?>
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbmr10"><?php echo $this->charStates['published']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_PUBLISHED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->charStates['unpublished']>0 ?'badge-yellow' : ''; ?> xbmr10"><?php echo $this->charStates['unpublished']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNPUBLISHED'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge <?php echo $this->charStates['archived']>0 ?'badge-warning' : ''; ?> xbmr10"><?php echo $this->charStates['archived']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPARCHIVED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->charStates['trashed']>0 ?'badge-important' : ''; ?> xbmr10"><?php echo $this->charStates['trashed']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPTRASHED'); ?>
						</div>
					</div>
				</div>
			</div>
			<?php if((!empty($this->orphanrevs)) || (!empty($this->orphanpeep)) || (!empty($this->orphanchar))) : ?>
			<div class="xbbox xbboxred">
				<h2 class="xbtitle">
					<?php echo Text::_('XBCULTURE_CAPORPHANS'); ?>
				</h2>
               <?php if(!empty($this->orphanrevs)) : ?>
				<div class="row-striped">
					<span class="badge badge-important pull-right"><?php echo count($this->orphanrevs); ?></span>
					<?php echo Text::_('XBCULTURE_CAPREVIEWS'); ?>
					<?php foreach($this->orphanrevs as $rev) {
					        echo '<br /><a class="xbml10" href="'.$relink.$rev['id'].'">'.$rev['title'].' ('.$rev['id'].')</a> ';
					}?>
				</div>
                <?php endif; ?>
                <?php if(!empty($this->orphanpeep)) : ?>
				<div class="row-striped">
					<span class="badge badge-important pull-right"><?php echo count($this->orphanpeep); ?></span>
					<?php echo Text::_('XBCULTURE_CAPPEOPLE'); ?>
					<?php foreach($this->orphanpeep as $rev) {
						echo '<br /><a class="xbml10" href="'.$pelink.$rev['id'].'">'.$rev['name'].' ('.$rev['id'].')</a> ';
					}?>
				</div>
                <?php endif; ?>
                <?php if(!empty($this->orphanchars)) : ?>
				<div class="row-striped">
					<span class="badge badge-important pull-right"><?php echo count($this->orphanchars); ?></span>
					<?php echo Text::_('XBCULTURE_CAPCHARACTERS'); ?>
					<?php foreach($this->orphanchars as $rev) {
						echo '<br /><a class="xbml10" href="'.$chelink.$rev['id'].'">'.$rev['name'].' ('.$rev['id'].')</a> ';
					}?>
				</div>
                <?php endif; ?>
			</div>
			<?php  endif; ?>
		</div>
            <div class="span4">
			<div class="xbbox xbboxyell">
				<h2 class="xbtitle">
					<span class="badge badge-info pull-right">
						<?php echo $this->catStates['total']; ?></span> 
					<?php echo Text::_('Book Categories'); ?>
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbmr10"><?php echo $this->catStates['published']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_PUBLISHED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->catStates['unpublished']>0 ?'badge-yellow' : ''; ?> xbmr10"><?php echo $this->catStates['unpublished']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNPUBLISHED'); ?>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<span class="badge <?php echo $this->catStates['archived']>0 ?'badge-warning' : ''; ?> xbmr10"><?php echo $this->catStates['archived']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPARCHIVED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->catStates['trashed']>0 ?'badge-important' : ''; ?> xbmr10"><?php echo $this->catStates['trashed']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPTRASHED'); ?>
						</div>
					</div>
                 <h3 class="xbsubtitle">Counts per category<span class="xb09 xbnorm"> <i> (books:reviews:people)</i></span></h3>
                 <div class="row-striped">
					<div class="row-fluid">
						    <?php echo $this->catlist; ?>
					</div>
				</div>
				<br />
<?php if ($this->xbpeople_ok !==false) : ?>
 				<h2 class="xbtitle">
					<span class="badge badge-info pull-right">
						<?php echo $this->pcatStates['total']; ?></span> 
					<?php echo Text::_('People Categories'); ?>
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span6">
							<span class="badge badge-success xbmr10"><?php echo $this->pcatStates['published']; ?></span>
						<?php echo Text::_('COM_XBBOOKS_PUBLISHED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->pcatStates['unpublished']>0 ?'badge-yellow' : ''; ?> xbmr10"><?php echo $this->pcatStates['unpublished']; ?></span>
							<?php echo Text::_('COM_XBBOOKS_UNPUBLISHED'); ?>
						</div>
 					</div>
 					<div class="row-fluid">
						<div class="span6">
							<span class="badge <?php echo $this->pcatStates['archived']>0 ?'badge-warning' : ''; ?> xbmr10"><?php echo $this->pcatStates['archived']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPARCHIVED'); ?>
						</div>
						<div class="span6">
							<span class="badge <?php echo $this->pcatStates['trashed']>0 ?'badge-important' : ''; ?> xbmr10"><?php echo $this->pcatStates['trashed']; ?></span>
							<?php echo Text::_('XBCULTURE_CAPTRASHED'); ?>
						</div>
					</div>
                 </div>
                 <h3 class="xbsubtitle">Counts per category<span class="xb09 xbnorm"> <i>(people:characters)</i></span></h3>
                 <div class="row-striped">
					<div class="row-fluid">
						    <?php echo $this->pcatlist; ?>
					</div>
				</div>
<?php endif; ?>
			</div>
			<div class="xbbox xbboxgrey">
				<h2 class="xbtitle">
					<span class="badge badge-info pull-right"><?php echo ($this->tags['tagcnts']['bkcnt'] + $this->tags['tagcnts']['percnt']   + $this->tags['tagcnts']['charcnt']  + $this->tags['tagcnts']['revcnt']) ; ?></span> 
					<?php echo Text::_('Tagged Items'); ?>
				</h2>
				<div class="row-striped">
					<div class="row-fluid">
                      <?php echo 'Books: ';
						echo '<span class="bkcnt badge  pull-right">'.$this->tags['tagcnts']['bkcnt'].'</span>'; ?>
                    </div>  
                    <div class="row-fluid">
                      <?php echo 'Reviews: ';
						echo '<span class="revcnt badge pull-right">'.$this->tags['tagcnts']['revcnt'].'</span>'; ?>
                    </div>  
                    <div class="row-fluid">
                      <?php echo 'People: ';
						echo '<span class="percnt badge pull-right">'.$this->tags['tagcnts']['percnt'].'</span>'; ?>
                    </div>  
                    <div class="row-fluid">
                      <?php echo 'Characters: ';
						echo '<span class="chcnt badge pull-right">'.$this->tags['tagcnts']['charcnt'].'</span>'; ?>
                    </div>  
                 </div>
				 <h2 class="xbtitle">Tag counts <span class="xb09 xbnorm"><i>(books:reviews:people:chars)</i></span></h2>
              <div class="row-fluid">
                 <div class="row-striped">
					<div class="row-fluid">
						<?php echo $this->taglist; ?>
                   </div>
                 </div>
			</div>
          	</div></div></div>
            <div class="span3">
              <div class="row-fluid hidden-phone">
                  <?php echo HtmlHelper::_('bootstrap.startAccordion', 'slide-cpanel', array('active' => 'sysinfo')); ?>
 		        		<?php echo HTMLHelper::_('bootstrap.addSlide', 'slide-cpanel', Text::_('Key Config Options'), 'keyconfig','xbaccordion'); ?>
		        		<?php //config params to report
		        		//show rating in lists, review with items

		        		
		        		?>
		        		<p>
		        		<?php echo Text::_('Show categories').'<br />';
		        		if (($this->show_cat==0) || 
		        		    (($this->show_bookcat==0) && ($this->show_revcat==0) && ($this->show_percat==0))) {
		        		    echo Text::_('Categories hidden for all types');
		        		} else {
		        		    echo Text::_('Show').' ';
		        		    echo ($this->show_bookcat) ? Text::_('books').' ' : '';
		        		    echo ($this->show_revcat) ? Text::_('reviews').' ' : '';
		        		    echo ($this->show_percat) ? Text::_('people').' ' : '';
		        		    echo Text::_('categories');
		        		}
		        		?>
		        		</p>
		        		<p>
		        		<?php echo Text::_('Show tags').'<br />';
		        		if (($this->show_tags==0) || 
		        		    (($this->show_booktags==0) && ($this->show_revtags==0) && ($this->show_pertags==0))) {
		        		    echo Text::_('Tags hidden for all types');
		        		} else {
		        		    echo Text::_('Show').' ';
		        		    echo ($this->show_booktags) ? Text::_('books').' ' : '';
		        		    echo ($this->show_revtags) ? Text::_('reviews').' ' : '';
		        		    echo ($this->show_pertags) ? Text::_('people').' ' : '';
		        		    echo Text::_('tags');
		        		}
		        		?>
		        		</p>
		        		<p>
		        		<?php echo Text::_('Allow search & filter in lists').': '.($this->show_search==0)? Text::_('JNO') : Text::_('JYES'); ?>
		        		</p>
		        		<p>
		        		<?php echo Text::_('Hide empty fields in item views').': '.($this->hide_empty==0)? Text::_('JNO') : Text::_('JYES'); ?>
		        		</p>
		        		
		        		<p>
		        		<?php echo Text::_('Image folders').'<br />';
    		        		echo Text::_('Book covers').': '.$this->covers.'<br />';
    		        		echo Text::_('Portraits').': '.$this->portraits.'<br />';
		        		?>	
		        		</p>
		        		<p>
		        		<?php echo Text::_('Show covers').'<br />'; 
		        		echo Text::_('In Lists').': '.($this->show_booklist_covers==0)? Text::_('JNO') : Text::_('JYES').' ';
		        		echo Text::_('in book views').': '.($this->show_book_cover==0)? Text::_('JNO') : Text::_('JYES').' ';
		        		echo Text::_('in reviews').': '.($this->show_review_cover==0)? Text::_('JNO') : Text::_('JYES').' ';
		        		?>	        		
		        		</p>
		        		<p>
		        		<?php echo Text::_('Show portraits').'<br />'; 
		        		echo Text::_('In Lists').': '.($this->show_people_portraits==0)? Text::_('JNO') : Text::_('JYES').' ';
		        		echo Text::_('in people views').': '.($this->show_person_portrait==0)? Text::_('JNO') : Text::_('JYES').' ';
		        		?>	        		
		        		</p>
		        		<p>
		        		<?php echo Text::_('Show ratings in book list').' '.($this->show_booklist_rating==0)? Text::_('JNO') : Text::_('JYES').' '; ?>
		        		<br />
		        		<?php echo Text::_('Show reviews with book view').' '.($this->show_book_review==0)? Text::_('JNO') : Text::_('JYES').' '; ?>
		        		</p>
	        			<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
                      <?php echo HtmlHelper::_('bootstrap.addSlide', 'slide-cpanel', Text::_('COM_XBBOOKS_SYSINFO'), 'sysinfo'); ?>
                          <p><b><?php echo Text::_( 'COM_XBBOOKS_COMPONENT' ); ?></b>
                              <br /><?php echo Text::_('XBCULTURE_VERSION').': '.$this->xmldata['version'].' '.
                                  $this->xmldata['creationDate'];?>
                                  </p><?php echo Text::_('Other xbCulture COmponents'); ?><ul>
                              	<?php $coms = array('com_xbfilms','com_xblive','com_xbpeople');
                              	foreach ($coms as $element) {
                              	    echo '<li>';
                                  	$ext = XbcultureHelper::getExtensionInfo($element);
                                  	if ($ext) {
                                  	    //todo add mouseover description
                                  	    echo $ext['name'].' v'.$ext['version'].' '.Text::_('Installed');
                                  	    if (!$ext['enabled']) echo '<b><i>'.text::_('not enabled').'</i></b>';
                                  	} else {
                                  	    echo '<i>'.$element.' '.Text::_('not installed').'</i>';
                                  	}
                                    echo '</li>';
                              	}
                              	
                              	?>
                              	</ul>
                              <br />xbCulture Modules: 
                              	<ul>
                              	<?php $mods = array('mod_xbculture_list','mod_xbculture_randimg','mod_xbculture_recent');
                              	foreach ($mods as $element) {
                              	    echo '<li>';
                                  	$mod = XbcultureHelper::getExtensionInfo($element);
                                  	if ($mod) {
                                  	    echo $mod['name'].' v'.$mod['version'].' '.Text::_('Installed');
                                  	    if (!$mod['enabled']) echo '<b><i>'.text::_('not enabled').'</i></b>';
                                  	} else {
                                  	    echo '<i>'.$element.' '.Text::_('not installed').'</i>';
                                  	}
                                    echo '</li>';
                              	}                             	
                              	?>
                              	</ul>

                          </p>
                          <p><b><?php echo Text::_( 'XBCULTURE_CAPCLIENT' ); ?></b>
                              <br/><?php echo $this->client['platform'].'<br/>'.$this->client['browser']; ?>
                          </p>
                      <?php echo HtmlHelper::_('bootstrap.endSlide'); ?>
                      <?php echo HtmlHelper::_('bootstrap.addSlide', 'slide-cpanel', Text::_('COM_XBBOOKS_ABOUT'), 'about'); ?>
                          <p><?php echo Text::_( 'COM_XBBOOKS_ABOUT_INFO' ); ?></p>
                      <?php echo HtmlHelper::_('bootstrap.endSlide'); ?>
                      <?php echo HtmlHelper::_('bootstrap.addSlide', 'slide-cpanel', Text::_('COM_XBBOOKS_LICENSE'), 'license'); ?>
                          <p><?php echo Text::_( 'COM_XBBOOKS_LICENSE_INFO' ); ?>
                              <br /><?php echo $this->xmldata['copyright']; ?>
                          </p>
                      <?php echo HtmlHelper::_('bootstrap.endSlide'); ?>
              </div>
          </div>
	</div>
</div>

<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

<div class="clearfix"></div>
<p><?php echo XbbooksGeneral::credit();?></p>

<?php endif; ?>


