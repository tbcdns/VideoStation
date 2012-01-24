<?php
require_once("lib/API-allocine.php");
require_once('lib/API-TMDb.php');
require_once('lib/functions.php');
require_once('lib/lang.php');
if (ereg("-",$_GET['mid'])){
$sid = explode('-',$_GET['mid']);
$movie = new AlloCine();
$infos_epi = $movie->episodeInfos($sid[2]);
$infos_season = $movie->seasonInfos($sid[1]);
$infos_serie = $movie->serieInfos($sid[0]);
$serie = true;
}
else{
switch($MOVIES_DATABASE){
	case 'Allocine':
	$movie = new AlloCine();
	break;
	
	case 'TMDb':
	$movie = new TMDb($LANGUAGE);
	break;
}
$infos = $movie->movieInfos($_GET['mid']);
$serie = false;
}
if(!$MODAL){
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Home Server Videos</title>
<link type="text/css" rel="stylesheet" media="screen" href="css/default.css">
</head>
<body>
<?php
}
if($serie){
echo '<nav><div>';
echo '<h1>'.$infos_epi['titre'];
if ($infos_epi['titre']!=$infos_epi['titre-original']) echo ' ('.$infos_epi['titre-original'].')';
echo '</h1></div></nav>';
echo '<div id="content">';
?>

<table style="border:0px;">
	<tr><td rowspan="11" style="vertical-align:top;"><?php resize($infos_serie['affiche']);?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo length;?>:</b></td><td><?php echo $infos_serie['longueur'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo season;?>:</b></td><td><?php echo $infos_epi['saison'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo episode;?>:</b></td><td><?php echo $infos_epi['episode'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo note;?>:</b></td><td><?php echo $infos_epi['note-public'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo nbepisode;?>:</b></td><td><?php echo $infos_serie['nb-episodes'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo actors;?>:</b></td><td><?php echo $infos_serie['acteurs'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo synopsis;?>:</b></td><td><?php echo $infos_epi['resume'];?></td></tr>
</table>
<?php
}
else {
echo '<nav><div>';
echo '<h1>'.$infos['titre'];
if ($infos['titre']!=$infos['titre-original']) echo ' ('.$infos['titre-original'].')';
echo '</h1></div></nav>';
echo '<div id="content">';
?>

<table style="border:0px;">
	<tr><td rowspan="11" style="vertical-align:top;"><?php if (!empty($infos['affiche'])) { resize($infos['affiche']);} else echo 'Image indisponible';?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo year;?>:</b></td><td><?php echo $infos['annee'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo length;?>:</b></td><td><?php echo $infos['longueur'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo note;?>:</b></td><td><?php echo round($infos['note-public'],1);?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo votes;?>:</b></td><td><?php echo $infos['nb-note-public'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo country;?>:</b></td><td><?php echo $infos['pays'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo genres;?>:</b></td><td><?php echo $infos['genres'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo director;?>:</b></td><td><?php echo $infos['realisateur'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo actors;?>:</b></td><td><?php echo $infos['acteurs'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo synopsis;?>:</b></td><td><?php echo $infos['resume'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo trailer;?>:</b></td><td><?php
	if($MOVIES_DATABASE == 'Allocine' and !empty($infos['bande-annonce'])){
		echo '<div><object type="application/x-shockwave-flash" data="'.$infos['bande-annonce'].'" width="420" height="357"><param name="allowFullScreen" value="true"></object></div>';
	}
	elseif($MOVIES_DATABASE == 'TMDb'){
		if(empty($infos['bande-annonce'])){
			$movie_en = new TMDb('en');
			$trailer = $movie_en->movieInfos($_GET['mid']);
			$infos['bande-annonce'] = $trailer['bande-annonce'];
		}
		if(!empty($infos['bande-annonce'])){
			$codeyoutube = explode('v=',$infos['bande-annonce']);
			$codeyoutube = $codeyoutube[1];
			if(strlen($codeyoutube) != 11) {
			$codeyoutube = explode('&',$codeyoutube);
			$codeyoutube = $codeyoutube[0];
			}
			echo '<iframe width="420" height="315" style="margin-left:auto;margin-right:auto;" src="http://www.youtube.com/embed/'.$codeyoutube.'" frameborder="0" allowfullscreen></iframe>';
		}
	}
	?></td></tr>
</table>
<?php
}
?>
</div>
<?php
if (!$modal){
echo '</body>';
echo '</html>';
}
?>
