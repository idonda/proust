<?php
//SQLITE
  class MyDB extends SQLite3
   {
      function __construct()
      {
         $this->open('proust.db');
      }
   }
   $sq = new MyDB();
   if(!$sq)
      echo $sq->lastErrorMsg();

$result = $sq->query("SELECT * FROM proust");
$nrows=0;
while ($result->fetchArray())
    $nrows++;
$result->reset();

$markers = "var markers = [\n";
$infoWindowContent = "var infoWindowContent = [\n";
$resarr = $result->fetchArray();
while ($resarr)
{
	
	if( !file_exists( "img/".$resarr[1].".jpg" ) )
		$empty_image[] = $resarr[1];
		
	if( !empty($_POST['popupempty']) AND $_POST['popupempty'] != "-1" AND $_POST['popupempty'] != $resarr[1] ){
   	$resarr = $result->fetchArray();
   	continue;
   }
   
   if( isset($_POST['emptyimages']) AND file_exists( "img/".$resarr[1].".jpg" ) ){
   	$resarr = $result->fetchArray();
   	continue;
   }
		
	$go=false;
	//marker
	if( !empty($resarr[9]) ){ 
		$markers .= "['".$resarr[1]."',".$resarr[9].",".$resarr[10]."]";
		//$search = array( "'", "?", "/", "\n" );
		$infoWindowContent .= "['<DIV ID=\"infoWindow\" STYLE=\"background-color:;height:120px;width:400px;\"><TABLE><TR><TD>";
		$infoWindowContent .= "<IMG BORDER=\"0\" ALIGN=\"Left\" WIDTH=100 SRC=\"img/".$resarr[1].".jpg\"></DIV></TD>";   	
		$infoWindowContent .= "<TD>(".$resarr[1].") "
									.htmlentities( $resarr[5], ENT_QUOTES ).", "
									.htmlentities( $resarr[4], ENT_QUOTES )." "
									.htmlentities( $resarr[2], ENT_QUOTES )."<BR>"
									.htmlentities( $resarr[6], ENT_QUOTES )."<BR>"
									//.htmlentities( $resarr[11], ENT_QUOTES )."<BR>"
									//.htmlentities( $resarr[12], ENT_QUOTES )."<BR>"
									."</TD>']";
		$go=true;   
   }
	$resarr = $result->fetchArray();
	if( $resarr AND $go){
		$markers .= ",\n";
		$infoWindowContent .= ",\n";
		$go=false;
	} 
		
}
$markers .= "\n];";
$infoWindowContent .= "\n];";

//$sq->close();


?>

<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Proust Markers</title>
    <style>
      html, body, #map-canvas {
        width: 1000px;
        height: 600px;
        margin: 0px;
        padding: 0px
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script type="text/javascript" src="markerclusterer.js"></script>
    
    <script>
		function initialize() {
  			var myLatlng = new google.maps.LatLng(48.856667, 2.350833);
  			var mapOptions = {
    			zoom: 10,
    			center: myLatlng
  			}
  			var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  	

<?php
echo $markers;
echo "\n";
echo $infoWindowContent;
?>

			// Display multiple markers on a map
			var infoWindow = new google.maps.InfoWindow(), marker, i;
			var markersArray = [];
               
			// Loop through our array of markers & place each one on the map
			for( i = 0; i < markers.length; i++ ) {
				var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
				//bounds.extend(position);
				marker = new google.maps.Marker({
					position: position,
					map: map,
					title: markers[i][0]
				});
				markersArray.push(marker)
             
				// Allow each marker to have an info window
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						infoWindow.setContent(infoWindowContent[i][0]);
						infoWindow.open(map, marker);
					}
				})(marker, i));
			}			
               
			var mcOptions = {gridSize: 50, maxZoom: 15, minimumClusterSize: 2, zoomOnClick: true};
			<?php if (isset($_POST['cluster']))
				echo "var markerCluster = new MarkerClusterer(map, markersArray, mcOptions);";
			?>

		}

		google.maps.event.addDomListener(window, 'load', initialize);

    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
<form method='post' action='final_sqlite.php'>
<input type='submit' name='GO' value='GO'/>
EMPTY: <input type='checkbox' name='emptyimages' value='emptyimages' <?php if (isset($_POST['emptyimages'])) echo "checked"; ?> />
CLUSTER: <input type="checkbox" name="cluster" value="cluster" <?php if (isset($_POST['cluster'])) echo "checked"; ?> />
<select name="popupempty" onchange="this.form.submit()">
<option value="-1"> popup </option>
	<?php foreach( $empty_image as $value ) echo "<option value=\"$value\"> $value </option>\n"; ?>
</select>
</form>

  </body>
</html>






