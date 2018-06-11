<?php
require 'thumbnailer.php';

use Images\Thumbnailer;

$input_img = 'orig_img.png';
$output_img = 'thumb_img.png';
$aspect_ratio = 16/9;

$tn = new Images\Thumbnailer($input_img);
$tn->cropImageByAspectRatio($aspect_ratio);
$tn->makeThumbnail(200, 200);
$tn->saveImage($output_img);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Thumbnailer Sample</title>
<style>
body {
	font-family: Verdana;
	font-size: 10pt;
}

td {
	vertical-align: top;
}
</style>
</head>

<body>

<table border="0" cellpadding="3" cellspacing="1">
	<tr>
		<td>Original: </td>
		<td><img src="<?= $input_img; ?>" width="50%"></td>
	</tr>
	<tr>
		<td>Thumbnail: </td>
		<td><img src="<?= $output_img; ?>"></td>
	</tr>
</table>

</body>

</html>
