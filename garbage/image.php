<?

function getImage($string) {

	$im = imagecreatetruecolor(250, 100);
	$red = imagecolorallocate($im, 250, 0, 0);
	$px = (imagesx($im) - 7.5 * strlen($string)) / 2;

	imagettftext($im, 20, 0, 30, 50, $red, "./11528.ttf", $string);

	ob_start();
	imagepng($im);
	imagedestroy($im);

	$image_data = ob_get_contents();


	ob_end_clean();

	return $image_data;
}

?>