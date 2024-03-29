<?php
/*******
 * @package xbBooks
 * @filesource admin/views/reviews/tmpl/modalpv.php
 * @version 1.0.3.2 9th January 2023
 * @author Roger C-O
 * @copyright Copyright (c) Roger Creagh-Osborne, 2022
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<script>
    jQuery(document).ready(function(){
      	jQuery('#pv').attr('src',function(i, origValue){return origValue + window.pvid;});
    });
  	jQuery('.iframe-full-height').on('load', function(){
    	this.style.height=this.contentDocument.body.scrollHeight+20 +'px';
	});
</script>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" 
    	style="opacity:unset;line-height:unset;border:none;">&times;</button>
     <h4 class="modal-title">Preview Review</h4>
</div>
<div class="modal-body">
    <div style="margin:0 30px;">
		<iframe src="<?php echo JURI::root(); ?>/index.php?option=com_xbbooks&view=bookreview&layout=default&tmpl=component&id=" 
			title="Preview Review" id="pv" class="iframe-full-height"></iframe>   
	</div>
</div>

