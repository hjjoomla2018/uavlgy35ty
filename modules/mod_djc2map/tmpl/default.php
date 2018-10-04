<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();

$document= JFactory::getDocument();
$config = JFactory::getConfig();

$cparams = Djcatalog2Helper::getParams();

$apiKey = $cparams->get('gm_api_key') ? '?key='.$cparams->get('gm_api_key') : '';
$document->addScript("https://maps.google.com/maps/api/js" . $apiKey);
$document->addScript(JURI::base(true).'/components/com_djcatalog2/assets/mapclustering/src/markerclusterer.js');

?>
<div class="djc_mapview mod_djc_map djc_clearfix" id="mod_map_items-<?php echo $module_id; ?>">
	<div id="djc2_map_box-<?php echo $module_id; ?>" style="display: none;"  class="djc_map_wrapper">
		<div id="djc2_map-<?php echo $module_id; ?>" class="djc2_map" style="width: <?php echo $params->get('gm_map_width', '100%');?>; height: <?php echo $params->get('gm_map_height', '400px');?>">
		</div>
	</div>
</div>

<?php 
$markers = array();

foreach ($items as $point) {
	if ($point->latitude == '' || $point->latitude == 0.00000000 || $point->longitude == '' || $point->longitude == 0.00000000) {
		continue;
	}

	$marker_link = JRoute::_(DJCatalogHelperRoute::getItemRoute($point->slug, $point->catslug));
	$marker_title = (htmlspecialchars($point->name));
	
	$marker_img = '';
	if ($point->item_image && $params->get('showimage', true)) {
		$marker_img = '<div class="pull-right"><a href="'.$marker_link.'"><img alt="'.$point->image_caption.'" src="'.DJCatalog2ImageHelper::getImageUrl($point->image_fullpath,'small').'"/></a></div>';
	}
	
	$address = array();
	$marker_address = '';

	if ($point->address && $params->get('location_address_item', 1) == '1') {
		$address[] = ($point->address);
	}
	if ($point->postcode && $params->get('location_postcode_item', 1)) {
		$address[] = ($point->postcode);
	}
	if ($point->city && $params->get('location_city_item', 1) == '1') {
		$address[] = ($point->city);
	}
	if ($point->country_name && $params->get('location_country_item', 1)) {
		$address[] = ($point->country_name);
	}

	if (count($address)) {
		$marker_address = implode(', ', $address);
		$marker_address = htmlspecialchars($marker_address);
	}

	$contact = array();
	$marker_contact = '';

	if ($point->phone && $params->get('location_phone_item', 1) == '1') {
		$contact[] = JText::_('COM_DJCATALOG2_UP_PHONE').': <span>'.(htmlspecialchars($point->phone)).'</span>';
	}
	if ($point->mobile && $params->get('location_mobile_item', 1) == '1') {
		$contact[] = JText::_('COM_DJCATALOG2_UP_MOBILE').': <span>'.(htmlspecialchars($point->mobile)).'</span>';
	}
	if ($point->fax && $params->get('location_fax_item', 1) == '1') {
		$contact[] = JText::_('COM_DJCATALOG2_UP_FAX').': <span>'.(htmlspecialchars($point->fax)).'</span>';
	}
	if ($point->website && $params->get('location_website_item', 1) == '1') {
		$point->website = (strpos($point->website, 'http') === 0) ? $point->website : 'http://'.$point->website;
		$point->website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', (htmlspecialchars($point->website)));
		$contact[] = JText::_('COM_DJCATALOG2_UP_WEBSITE').': <span>'.$point->website.'</span>';
	}
	if ($point->email && $params->get('location_email_item', 1) == '1') {
		$point->email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', (htmlspecialchars($point->email)));
		$contact[] = JText::_('COM_DJCATALOG2_UP_EMAIL').': <span>'.$point->email.'</span>';
	}

	if (count($contact)) {
		$marker_contact = implode('<br />', $contact);
		$marker_contact = $marker_contact;
	}


	$marker_txt = '<div style="min-width: 250px;">';
	$marker_txt .= $marker_img;
	$marker_txt .= '<p><a href="'.$marker_link.'">'.$marker_title.'</a></p>';
	
	if ($params->get('show_category_name')) {
		$marker_txt .= '<p>'.JText::_('MOD_DJC2MAP_CATEGORY').': <span>'.$point->category.'</span></p>';
	}
	if ($params->get('show_producer_name') && $point->producer) {
		$marker_txt .= '<p>'.JText::_('MOD_DJC2ITEMS_PRODUCER').': <span>'.$point->producer.'</span></p>';
	}
	if ($params->get('items_show_intro')) {
		$intro = DJCatalog2HtmlHelper::trimText($point->intro_desc, $params->get('items_intro_length'));
		if (trim($intro) != '') {
			$marker_txt .= '<p>'.$intro.'</p>';
		}
	}
	
	if ($params->get('show_location_details')) {
		if ($marker_address) {
			$marker_txt .= '<p>'.$marker_address.'</p>';
		}
		if ($marker_contact) {
			$marker_txt .= '<p>'.$marker_contact.'</p>';
		}
	}

	if ($cparams->get('gm_gmaps_link', 1)){
		$marker_txt .= '<p class="djc_gmaps_link"><a rel="nofollow" target="_blank" href="//maps.google.com/?q=loc:'.$point->latitude.','.$point->longitude.'">'.JText::_('COM_DJCATALOG2_SHOW_IN_GOOGLEMAPS').'</a></p>';
	}

	$marker_txt .= '</div>';

	$marker_txt = str_replace(array("\r", "\n"), "", $marker_txt);

	$marker = new stdClass();
	$marker->txt = $marker_txt;
	$marker->latitude = $point->latitude;
	$marker->longitude = $point->longitude;

	$markerIcon = $point->params->get('gm_map_marker', $cparams->get('gm_map_marker', ''));
	$marker->icon = $markerIcon == '' ? '' : JUri::base(false) . $markerIcon;

	$markers[] = $marker;
}

$map_styles = $params->get('gm_styles', $cparams->get('gm_styles'));
if (trim($map_styles) == '') {
	$map_styles = '[]';
}
?>

<script type="text/javascript">

jQuery(window).load(function(){

	var DJCatalog2GM<?php echo $module_id; ?> = {
		markers: <?php echo json_encode($markers); ?>,
		djc2_map: null,
		djc2_map_markers: [],
		djc2_geocoder: new google.maps.Geocoder(),
			
		ClusterMarker: function(position,txt,icon) {
			var self = this;
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
				for (var i = 0; i < self.djc2_map_markers.length; i++) {
					self.djc2_map_markers[i].infowindow.close();
				}
				djc2_map_info.setContent(marker.txt);
				djc2_map_info.open(self.djc2_map, marker);
			});

			marker.infowindow = djc2_map_info;

			self.djc2_map_markers.push(marker);
			
			return marker;
		},

		ClusterStart: function(){
			var self = this;
			self.djc2_geocoder.geocode({address: '<?php echo $params->get('gm_start_location', $cparams->get('gm_start_location', 'World')); ?>'}, function (results, status)
			{
				if(status == google.maps.GeocoderStatus.OK)
				{			   
				 	document.getElementById("djc2_map_box-<?php echo $module_id; ?>").style.display='block';
					var mapOpts = {
						zoom: <?php echo $params->get('gm_zoom', $cparams->get('gm_zoom', 10));?>,
						center: results[0].geometry.location,
						mapTypeId: google.maps.MapTypeId.<?php echo $params->get('gm_type', $cparams->get('gm_type','ROADMAP'));?>,
						navigationControl: true,
						scrollwheel: true,
						styles: <?php echo $map_styles; ?>
					};
					self.djc2_map = new google.maps.Map(document.getElementById("djc2_map-<?php echo $module_id; ?>"), mapOpts);									   
					 var size = new google.maps.Size(32,32);
					 var start_point = new google.maps.Point(0,0);
					 var anchor_point = new google.maps.Point(0,16);	

					 var icon = '<?php echo $cparams->get('gm_map_marker', '') ? JUri::base(false) . $cparams->get('gm_map_marker', '') : ''; ?>';
					   
					<?php if(count($markers)){ ?>
						for (var i=0; i < self.markers.length; i++) {
							var adLatlng = new google.maps.LatLng(self.markers[i].latitude, self.markers[i].longitude);
							var MapOptions = {
							   zoom: <?php echo $params->get('gm_zoom_item', $cparams->get('gm_zoom_item','10')); ?>,
						  		center: adLatlng,
						  		mapTypeId: google.maps.MapTypeId.<?php echo $params->get('gm_type_item', $cparams->get('gm_type_item','ROADMAP')); ?>,
						  		navigationControl: true
							};
							//djc2_map = new google.maps.Map(document.getElementById("djc2_map"), MapOptions); 				   
							var marker = self.ClusterMarker(adLatlng, self.markers[i].txt, self.markers[i].icon);
						}
						for (var i = 0; i < self.djc2_map_markers.length; i++) {
							self.djc2_map_markers[i].setMap(self.djc2_map);
						}
					<?php } ?>
					var mcOptions = {gridSize: 50, maxZoom: 14,styles: [{
						height: 53, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m1.png",width: 53},
						{height: 56, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m2.png",width: 56},
						{height: 66, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m3.png",width: 66},
						{height: 78, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m4.png",width: 78},
						{height: 90, url: "<?php echo JURI::base()?>components/com_djcatalog2/assets/mapclustering/images/m5.png",width: 90}]};
					var markerCluster = new MarkerClusterer(self.djc2_map, self.djc2_map_markers, mcOptions);																																									 
				}
			});
		}
	};

	DJCatalog2GM<?php echo $module_id; ?>.ClusterStart();
});

</script>
