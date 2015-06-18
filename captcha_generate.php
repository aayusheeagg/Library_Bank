<?php
session_start();
$im = imagecreatetruecolor(50, 24);
$bg = imagecolorallocate($im, 245,222,179); //background color blue
$fg = imagecolorallocate($im, 25,25,112);//text color white

imagefill($im, 0, 0, $bg);
 
for($i=0;$i<2;$i++) {
    imageline($im,0,rand()%24,50,rand()%24,$fg);
}
$pixel_color = imagecolorallocate($im, 0,0,255);
for($i=0;$i<75;$i++) {
    imagesetpixel($im,rand()%50,rand()%24,$pixel_color);
} 
$cp=NULL;
for($i=0;$i<5;$i++){
	$txt=rand(0,9);
	imagestring($im,3,3+($i*10),rand(3,8),$txt,$fg);
	$cp.=$txt;
}
header("Content-Type: image/jpeg"); 
imagepng($im);
imagedestroy($im);

?>
