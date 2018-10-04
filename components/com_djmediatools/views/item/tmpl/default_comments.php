<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined ('_JEXEC') or die('Restricted access'); 

$uri = JFactory::getURI(); 
$lang = JFactory::getLanguage();
$languge_tag = str_replace('-', '_', $lang->getTag());
$item = $this->slides[$this->current];
$comments = (int)$this->params->get('comments',0);
 
if(isset($item->comments)) {
?>
<div class="djmt_comments">
	
	<?php if($comments == '3') { ?>
		<?php if ($this->params->get('facebook_sdk', '1') == '1') { ?>
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/<?php echo $languge_tag; ?>/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
		<?php } ?>				
		<div class="fb-comments" data-href="<?php echo $item->comments; ?>" data-num-posts="2" data-width="100%"></div>
	<?php } else if($comments == '2' && $this->params->get('disqus_shortname','') != '') {?>
    	<?php 
    	$devlist = array('localhost', '127.0.0.1');
    	$disqus_shortname = $this->params->get('disqus_shortname','');
    	$disqus_url = $item->comments['url'];
    	$disqus_identifier = $item->comments['identifier'];
    	$disqus_developer = (in_array($_SERVER['HTTP_HOST'], $devlist)) ? 1 : 0;
    	?>
    	<div id="disqus_thread"></div>
	    <script type="text/javascript">
	        var disqus_shortname = '<?php echo $disqus_shortname; ?>';
	        var disqus_url = '<?php echo $disqus_url; ?>';
	        var disqus_identifier = '<?php echo $disqus_identifier; ?>';
			var disqus_developer = <?php echo $disqus_developer; ?>;
			
	        /* * * DON'T EDIT BELOW THIS LINE * * */
	        (function() {
	            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	            dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
	            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	        })();
	    </script>
	    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
	    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
	<?php } else if ($comments == '1') { ?>
   	<?php 
		$jcomments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
		if (file_exists($jcomments)) {
			require_once($jcomments);
			echo JComments::show($item->comments['id'],$item->comments['group'], $item->title);
		}
	?>
	<?php } /*else if ($comments == '4') { ?>
   	<?php 
   		$komento =  JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_komento' . DIRECTORY_SEPARATOR . 'bootstrap.php';
		if (file_exists($komento)) {
			require_once($komento);
			
			$options			= array();
			$options['trigger']	= 'onDJMediatoolsItem';
			$options['context']	= 'com_djmediatools.item';
			$options['params']	= $this->params;
			
			$result = Komento::commentify( 'com_djmediatools', $item, $options );
			if (!empty($result)) {
				echo $result;
			}
		}
	?>
	<?php } */ ?>					
</div>
<?php
}
