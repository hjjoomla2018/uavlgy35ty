<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();

$document= JFactory::getDocument();
$config = JFactory::getConfig();

$apiKey = $this->params->get('gm_api_key') ? '?key='.$this->params->get('gm_api_key') : '';
$document->addScript("https://maps.google.com/maps/api/js" . $apiKey);

$document->addScript(JURI::base(true).'/components/com_djcatalog2/assets/mapclustering/src/markerclusterer.js');

?>

<?php if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1
	class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php } ?>

<div id="djcatalog"
	class="djc_mapview<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">
	
	<?php if (($this->params->get('show_category_filter_map', 1) > 0 || $this->params->get('show_producer_filter_map', 1) > 0  || $this->params->get('show_search_map', 1) > 0)) { ?>
		<div class="djc_filters djc_clearfix" id="tlb">
			<?php echo $this->loadTemplate('filters'); ?>
		</div>
	<?php } ?>

	<div id="djc2_map_box" style="display: none;"  class="djc_map_wrapper">
		<div id="djc2_map" class="djc2_map" style="width: <?php echo $this->params->get('gm_map_width', '100%');?>; height: <?php echo $this->params->get('gm_map_height', '400px');?>">
		</div>
	</div>


	<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
	?>
</div>

<script type="text/javascript">

jQuery(window).load(function(){
	DJCatalog2GMClusterStart();
});

<?php 

$markers = array();

foreach ($this->items as $point) {
	if ($point->latitude == '' || $point->latitude == 0.00000000 || $point->longitude == '' || $point->longitude == 0.00000000) {
		continue;
	}

	$marker_link = JRoute::_(DJCatalogHelperRoute::getItemRoute($point->slug, $point->catslug));
	$marker_title = (htmlspecialchars($point->name));

	$address = array();
	$marker_address = '';

	if ($point->address) {
		$address[] = ($point->address);
	}
	if ($point->postcode) {
		$address[] = ($point->postcode);
	}
	if ($point->city) {
		$address[] = ($point->city);
	}
	if ($point->country_name) {
		$address[] = ($point->country_name);
	}

	if (count($address)) {
		$marker_address = implode(', ', $address);
		$marker_address = htmlspecialchars($marker_address);
	}

	$contact = array();
	$marker_contact = '';

	if ($point->phone) {
		$contact[] = JText::_('COM_DJCATALOG2_UP_PHONE').': <span>'.(htmlspecialchars($point->phone)).'</span>';
	}
	if ($point->mobile) {
		$contact[] = JText::_('COM_DJCATALOG2_UP_MOBILE').': <span>'.(htmlspecialchars($point->mobile)).'</span>';
	}
	if ($point->fax) {
		$contact[] = JText::_('COM_DJCATALOG2_UP_FAX').': <span>'.(htmlspecialchars($point->fax)).'</span>';
	}
	if ($point->website) {
		$point->website = (strpos($point->website, 'http') === 0) ? $point->website : 'http://'.$point->website;
		$point->website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', (htmlspecialchars($point->website)));
		$contact[] = JText::_('COM_DJCATALOG2_UP_WEBSITE').': <span>'.$point->website.'</span>';
	}
	if ($point->email) {
		$point->email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', (htmlspecialchars($point->email)));
		$contact[] = JText::_('COM_DJCATALOG2_UP_EMAIL').': <span>'.$point->email.'</span>';
	}

	if (count($contact)) {
		$marker_contact = implode('<br />', $contact);
		$marker_contact = $marker_contact;
	}


	$marker_txt = '<div style="min-width: 250px;">';
	$marker_txt .= '<p><a href="'.$marker_link.'">'.$marker_title.'</a></p>';
	$marker_txt .= '<p>'.$marker_address.'</p>';
	$marker_txt .= '<p>'.$marker_contact.'</p>';

	if ($this->params->get('gm_gmaps_link', 1)){
		$marker_txt .= '<p class="djc_gmaps_link"><a rel="nofollow" target="_blank" href="//maps.google.com/?q=loc:'.$point->latitude.','.$point->longitude.'">'.JText::_('COM_DJCATALOG2_SHOW_IN_GOOGLEMAPS').'</a></p>';
	}

	$marker_txt .= '</div>';

	$marker_txt = str_replace(array("\r", "\n"), "", $marker_txt);

	$marker = new stdClass();
	$marker->txt = $marker_txt;
	$marker->latitude = $point->latitude;
	$marker->longitude = $point->longitude;

	$markerIcon = $point->params->get('gm_map_marker', $this->params->get('gm_map_marker', ''));
	$marker->icon = $markerIcon == '' ? '' : JUri::base(false) . $markerIcon;

	$markers[] = $marker;
}

$map_styles = $this->params->get('gm_styles');
if (trim($map_styles) == '') {
	$map_styles = '[]';
}

?>

	var markers = <?php echo json_encode($markers); ?>;
		var djc2_map;
		var djc2_map_markers = [];//new google.maps.InfoWindow();
		var djc2_geocoder = new google.maps.Geocoder();
				
		function DJCatalog2GMClusterMarker(position,txt,icon)
		{
			var MarkerOpt =  
			{ 
				position: position, 
				icon: icon
			} 
			
			var marker = new google.maps.Marker(MarkerOpt);
			marker.txt=txt;

			var djc2_map_info = new google.maps.InfoWindow();
			
			google.maps.event.addListener(marker,"click",function()
			{
				for (var i = 0; i < djc2_map_markers.length; i++) {
					djc2_map_markers[i].infowindow.close();
				}
				djc2_map_info.setContent(marker.txt);
				djc2_map_info.open(djc2_map, marker);
			});

			marker.infowindow = djc2_map_info;

			djc2_map_markers.push(marker);
			
			return marker;
		}

		 function DJCatalog2GMClusterStart()	
		 {		   
			djc2_geocoder.geocode({address: '<?php echo ($this->lists['search']) ? $this->escape($this->lists['search']) : $this->params->get('gm_start_location', 'World'); ?>'}, function (results, status)
			{
				if(status == google.maps.GeocoderStatus.OK)
				{			   
				 document.getElementById("djc2_map_box").style.display='block';
					var mapOpts = {
						zoom: <?php echo $this->params->get('gm_zoom', (($this->lists['search']) ? '10' : '1'));?>,
						center: results[0].geometry.location,
						mapTypeId: google.maps.MapTypeId.<?php echo $this->params->get('gm_type','ROADMAP');?>,
						navigationControl: true,
						scrollwheel: true,
						styles: <?php echo $map_styles; ?>
					};
					djc2_map = new google.maps.Map(document.getElementById("djc2_map"), mapOpts);									   
					 var size = new google.maps.Size(32,32);
					 var start_point = new google.maps.Point(0,0);
					 var anchor_point = new google.maps.Point(0,16);	

					 var icon = '<?php echo $this->params->get('gm_map_marker', '') ? JUri::base(false) . $this->params->get('gm_map_marker', '') : ''; ?>';
					   
					<?php if(count($markers)){ ?>
						for (var i=0; i < markers.length; i++) {
							var adLatlng = new google.maps.LatLng(markers[i].latitude, markers[i].longitude);
							var MapOptions = {
							   zoom: <?php echo $this->params->get('gm_zoom_item','10'); ?>,
						  		center: adLatlng,
						  		mapTypeId: google.maps.MapTypeId.<?php echo $this->params->get('gm_type_item','ROADMAP'); ?>,
						  		navigationControl: true
							};
							//djc2_map = new google.maps.Map(document.getElementById("djc2_map"), MapOptions); 				   
							var marker = DJCatalog2GMClusterMarker(adLatlng, markers[i].txt, markers[i].icon);
						}
						for (var i = 0; i < djc2_map_markers.length; i++) {
							djc2_map_markers[i].setMap(djc2_map);
						}
					<?php } ?>
					var mcOptions = {gridSize: 50, maxZoom: 14,styles: [{
						height: 53, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m1.png",width: 53},
						{height: 56, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m2.png",width: 56},
						{height: 66, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m3.png",width: 66},
						{height: 78, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m4.png",width: 78},
						{height: 90, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m5.png",width: 90}]};
					var markerCluster = new MarkerClusterer(djc2_map, djc2_map_markers, mcOptions);																																									 
				
					}
				});					 
			}  
</script>
