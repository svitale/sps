<?php
if (isset($_GET['total'])) {
	$total = $_GET['total'];
} else {
	$total = 1;
}
$numCircles= $total;
if (isset($_GET['num'])) {
	$num = $_GET['num'] + 1;
} else {
	$num = 1;
}
$imgSize = 32;
$radius = 15/$num;
function drawCircle($radius) { 
	global $imgSize,$numCircles;
	$strokewidth = ($imgSize/5)/$numCircles;
	$centerX = $imgSize/2;
	$centerY = $imgSize/2;
	$width = $imgSize;
	$height = $imgSize;
	$image = new Imagick();
	$image->newImage( $width, $height, new ImagickPixel( 'transparent' ) );
	$draw = new ImagickDraw();
	$draw->setFillOpacity(.5);
	$draw->setFillColor('transparent');
	$draw->setStrokeWidth($strokewidth);
	$draw->setStrokeColor( new ImagickPixel( 'black' ) );
	$draw->circle( $centerX, $centerY, $centerX, $centerY - $radius );
	$image->drawImage( $draw ); 
	$image->setImageFormat('gif'); 
	return $image;
}
header('Content-type: image/gif'); 
echo drawCircle($radius); 
?>
