<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');


$app = JFactory::getApplication();

$document= JFactory::getDocument();
$config = JFactory::getConfig();


if($this->params->get('show_location_map_item', true )){
	$apiKey = $this->params->get('gm_api_key') ? '?key='.$this->params->get('gm_api_key') : '';
	$document->addScript("https://maps.google.com/maps/api/js" . $apiKey);
}

$item = $this->item;

?>
<div class="djc_location">
	<h3>
		<?php echo JText::_('COM_DJCATALOG2_LOCATION'); ?>
	</h3>
	<div class="row-fluid">

		<?php if( (int)$this->params->get('show_location_details_item', true) > 0) { ?>
			<?php
			$address = array();
			 
			if (($this->params->get('location_address_item', 1) == '1') && $item->address) {
				$address[] = $item->address;
			}
			if (($this->params->get('location_postcode_item', 1) == '1') && $item->postcode) {
				$address[] = $item->postcode;
			}
			if (($this->params->get('location_city_item', 1) == '1') && $item->city) {
				$address[] = $item->city;
			}
			if (($this->params->get('location_country_item', 1) == '1') && $item->country_name) {
				$address[] = $item->country_name;
			}
			
			if (count($address)) { ?>
			<p class="djc_address"><?php echo implode(', ', $address); ?></p>
			<?php }
				
				$contact = array();
				
				if (($this->params->get('location_phone_item', 1) == '1') && $item->phone) {
					$contact[] = JText::_('COM_DJCATALOG2_UP_PHONE').': <span>'.$item->phone.'</span>';
				}
				if (($this->params->get('location_mobile_item', 1) == '1') && $item->mobile) {
					$contact[] = JText::_('COM_DJCATALOG2_UP_MOBILE').': <span>'.$item->mobile.'</span>';
				}
				if (($this->params->get('location_fax_item', 1) == '1') && $item->fax) {
					$contact[] = JText::_('COM_DJCATALOG2_UP_FAX').': <span>'.$item->fax.'</span>';
				}
				if (($this->params->get('location_website_item', 1) == '1') && $item->website) {
					$website = (strpos($item->website, 'http') === 0) ? $item->website : 'http://'.$item->website;
					$website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', htmlspecialchars($item->website));
					$contact[] = JText::_('COM_DJCATALOG2_UP_WEBSITE').': <span>'.$website.'</span>';
				}
				if (($this->params->get('location_email_item', 1) == '1') && $item->email) {
					$email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', htmlspecialchars($item->email));
					$contact[] = JText::_('COM_DJCATALOG2_UP_EMAIL').': <span>'.$email.'</span>';
				}
				
				if (count($contact)) { ?>
			<p class="djc_contact"><?php echo implode('<br />', $contact);?></p>
			<?php } ?>
		<?php } ?>

		<?php if($this->params->get('show_location_map_item', 1) && $this->item->latitude != 0.0 && $this->item->longitude != 0.0 ) {?>
		<div id="google_map_box" style="display: none;" class="djc_map_wrapper">
			<a class="djc_gmaps_link" rel="nofollow" target="_blank" href="//maps.google.com/?q=loc:<?php echo $this->item->latitude.','.$this->item->longitude; ?>"><?php echo JText::_('COM_DJCATALOG2_SHOW_IN_GOOGLEMAPS'); ?></a>
			<div id="map" style="width: <?php echo $this->params->get('gm_map_width_item', '100%');?>; height: <?php echo $this->params->get('gm_map_height_item', '300px');?>"></div>
		</div>
		<?php }	?>

	</div>
</div>

<?php if($this->params->get('show_location_map_item', true) /*&& $this->item->latitude != 0.0 && $this->item->longitude != 0.0*/ ) {
	$map_points = array();
	if ($item->latitude != 0.0 && $item->longitude != 0.0) {
		$map_points[] = $item;	
	}
	if (!empty($this->children)) {
		$map_points = array_merge($map_points, $this->children);
	}
	
	$markers = array();
	foreach ($map_points as $point) {
		if ($point->latitude == '' || $point->latitude == 0.00000000 || $point->longitude == '' || $point->longitude == 0.00000000) {
			continue;
		}
		
		$marker_link = JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug));
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
?>
<?php if (count($markers)) { ?>
	<script type="text/javascript">
		jQuery(window).load(function(){ 
			DJCatalog2GMStart();
		});
		<?php 
		
		$map_styles = $this->params->get('gm_styles');
		if (trim($map_styles) == '') {
			$map_styles = '[]';
		}
		?>
		
		var markers = <?php echo json_encode($markers); ?>;

		var djc2_map;
		var djc2_map_markers = [];//new google.maps.InfoWindow();
		var djc2_geocoder = new google.maps.Geocoder();
		
		function DJCatalog2GMAddMarker(position,txt,icon)
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
		
		 function DJCatalog2GMStart()
		 {
			var icon = '<?php echo $this->params->get('gm_map_marker', '') ? JUri::base(false) . $this->params->get('gm_map_marker', '') : ''; ?>';
			document.getElementById("google_map_box").style.display='block';
			<?php if(count($markers)){ ?>
				for (var i=0; i < markers.length; i++) {
					var adLatlng = new google.maps.LatLng(markers[i].latitude, markers[i].longitude);

					if (i == 0) {
						var MapOptions = {
							zoom: <?php echo $this->params->get('gm_zoom_item','10'); ?>,
							center: adLatlng,
							mapTypeId: google.maps.MapTypeId.<?php echo $this->params->get('gm_type_item','ROADMAP'); ?>,
							navigationControl: true,
							styles: <?php echo $map_styles; ?>
						};
						djc2_map = new google.maps.Map(document.getElementById("map"), MapOptions);
					}
						
					var marker = DJCatalog2GMAddMarker(adLatlng, markers[i].txt, markers[i].icon);
				}
				for (var i = 0; i < djc2_map_markers.length; i++) {
					djc2_map_markers[i].setMap(djc2_map);
				}
			<?php } ?>
		 }
	</script>
	<?php } ?>
<?php } ?>