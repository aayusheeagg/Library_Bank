<?php
header('Content-type: text/html');
set_time_limit(0);
http_response_code(200);
error_reporting(0);
session_start();

include_once('class.tlbConfig.php');
$config = new tlbConfig();
$base = $config->baseServer;
include_once($_SERVER['DOCUMENT_ROOT'].$base.'class.pagecontent.php');
include_once($_SERVER['DOCUMENT_ROOT'].$base.'class.misc.php');
$misc = new misc();
$pgcontent = new pagecontent();

$semester = $misc->getSemester();
$session = $misc->getSession();
if(isset($_SESSION['student'])){
	$Student = $misc->getInfo($_SESSION['student'],'student');
	$Student['course'] = $misc->getCourse($_SESSION['student']);
	$Student['branch'] = $misc->getBranch($_SESSION['student']);
}
elseif(isset($_SESSION['admin']))
	$Admin = $misc->getInfo($_SESSION['admin'],'admin');
elseif(isset($_SESSION['staff']))
	$Staff = $misc->getInfo($_SESSION['staff'],'staff');
	
$path = basename($_SERVER['REQUEST_URI'],".php");
$page = strtok($path,'?');
$pages = array('home','instruction','cancelreg','books','choice','notice','contact','password','feecollect','reprint','feecancel','stats','bookedit','allot','print','tokenlist','tokens');
$page = (in_array($page,$pages))? $page : 'home';
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="author" content="Aayushee Aggrawal">
<title>TextBook Lending Bank | Central Library | MNNIT Allahabad</title>
<link rel="icon" type="image/ico" href="<?php echo $base; ?>images/tb.png"/>
<script src="<?php echo $base; ?>scripts/jquery-min.js" type="text/javascript"></script>
<script src="<?php echo $base; ?>scripts/jquery.sortable.min.js" type="text/javascript"></script>
<script src="<?php echo $base; ?>scripts/default.js" type="text/javascript"></script>
<link href="<?php echo $base; ?>styles/default.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="header">
	<h1><a href="#">TLB Portal for <?php echo $semester; ?> Semester <?php echo $session; ?></a></h1>
	<?php $misc->add_navmenu(); ?>
<div id="headerbg"></div>
</div>
<div id="page">
	<div id="content">
		<div id="title"><h2><?php echo $pgcontent->title($page); ?></h2>
		<span class="welcome">Welcome 
		<?php if(isset($Student)) echo $Student['name'];
			elseif(isset($Admin)) echo $Admin['name'];
			elseif(isset($Staff)) echo $Staff['name'];
			else echo 'Guest'; ?>
		</span></div>
		<div class="body" align="center">
		<?php $pgcontent->content($page); ?>
		</div>
	</div>
	<?php $misc->add_sidemenu($page); ?>
	<div style="clear:both"></div>
</div>

<div id="footer" style="position:relative">
<p id="legal">&copy;2014 Central Library, MNNIT. All Rights Reserved. Designed by <a href="<?php echo $base; ?>contact">Webteam TLB</a></p>
	<p id="links"><a href="#">Privacy</a> | <a href="#">Terms</a> | 
	<a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional"><abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a> |
	<a href="http://jigsaw.w3.org/css-validator/check/referer" title="This page validates as CSS"><abbr title="Cascading Style Sheets">CSS</abbr></a></p>
</div>

</body>
</html>