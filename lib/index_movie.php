<?php
/**
Requiert deux parametres : link et rep
**/
require('config.php');
require('API-allocine.php');
require('API-TMDb.php');
require('functions.php');
connect($PASSWORD_SQL,$DATABASE);

$link=$_GET['link'];



$sql = mysql_query("SELECT link FROM movies WHERE link='".addslashes($link)."'");
$data=mysql_fetch_array($sql);
if(!empty($data['link'])) $sql = mysql_query("DELETE FROM movies WHERE link='".addslashes($link)."'");



if(is_serie($SERIES_DIR)){//indexation series
    switch($SERIES_DATABASE){
    	case 'Allocine':
    	$series = new Allocine();
    	break;
    	case 'TheTvDb':
    	$series = new TheTvDb();
    	break;
    }
	$infos = explode('/',$_GET['rep']);
	$infos = array_reverse($infos);
	$matches=array();
	preg_match('#[0-9]{1,2}#',$infos[0],$matches);
	$nbSeason = $matches[0];
	$recherche = $series->serieSearch(keywordsAdapt($infos[1],$DELETED_WORDS,1));//recherche serie
	if (empty($recherche['code'])){
		$sql = "INSERT INTO movies VALUES(\"0-0-0\",\"".addslashes($link)."\",\"".addslashes($link)."\",\"0\",\"0\",\"".$size."\",\"".addslashes($_GET['rep'])."\")";
		mysql_query ($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
	}
	else{
		$id=$recherche['code'];
		$serie = $series->serieInfos($recherche['code']);//infos serie
		if (empty($serie['code'])){
			echo 'Erreur';
		}
		if(!empty($serie['affiche'])){
			//echo $recherche['affiche'];
			$img = explode('/',$serie['affiche']);
			$end_url = '';
			for($j=3;$j<count($img);$j++){
				$end_url = $end_url.'/'.$img[$j];
			}
			$img = $img[0].'//'.$img[2].'/r_150_204'.$end_url;
			copy($img,'../images/poster_small/s-'.$serie['code'].'.jpg');
		}
		$code_season = $serie['tabSaisons'][$nbSeason];
		$id.='-'.$code_season;
		$season = $series->seasonInfos($code_season);//infos saison
		if (empty($season['code'])){
			$sql = "INSERT INTO movies VALUES(\"".$serie['code']."-0-0\",\"".addslashes($link)."\",\"".addslashes($link)."\",\"0\",\"0\",\"".$size."\",\"".addslashes($_GET['rep'])."\")";
			mysql_query ($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
		}
		else {
			preg_match('#e[0-9]{1,2}#i',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
			if (empty($matches[0])){
				preg_match('#x[0-9]{1,2}#i',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
			}
			if (empty($matches[0])){
				preg_match('#[0-9]{4}#',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
				$matches[0] = $matches[0]{2}.$matches[0]{3};
			}
			if (empty($matches[0])){
				preg_match('#[0-9]{3}#',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
				$matches[0] = $matches[0]{1}.$matches[0]{2};
			}
			$matches[0] = str_replace('e','',$matches[0]);
			$matches[0] = str_replace('E','',$matches[0]);
			$matches[0] = str_replace('x','',$matches[0]);
			$matches[0] = str_replace('X','',$matches[0]);
			if((10-$matches[0])>0) $matches[0] = str_replace('0','',$matches[0]);
			$nbEpisode = $matches[0];
			$code_episode = $season['tabEpisode'][$nbEpisode];
			$id.='-'.$code_episode;
			$episode = $series->episodeInfos($code_episode);//infos episode
			if (empty($episode['code'])){
				echo 'Erreur';
			}
			if (strlen($nbSeason) == 1) $nbSeason = '0'.$nbSeason;
			if (strlen($nbEpisode) == 1) $nbEpisode = '0'.$nbEpisode;
			$sql = "INSERT INTO movies VALUES(\"".$id."\",\"".addslashes($link)."\",\"".addslashes('[S'.$nbSeason.'E'.$nbEpisode.']'.$episode['titre'])."\",\"".$episode['note-public']."\",\"".$episode['date']."\",\"".$size."\",\"".addslashes($_GET['rep'])."\")";
			mysql_query ($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
		echo $link.' indexe en tant que serie';
		}
	}
}
else {//indexation films
	switch($MOVIES_DATABASE){
		case 'Allocine':
		$movies = new Allocine();
		break;
		
		case 'TMDb':
		$movies = new TMDb($LANGUAGE);
		break;
	}
	$recherche = $movies->movieSearch(keywordsAdapt($link,$DELETED_WORDS,1));
	if (empty($recherche['code'])){ //si aucun film trouve
		$sql = "INSERT INTO movies VALUES('0',\"".addslashes($link)."\",\"".keywordsAdapt(addslashes($link), $DELETED_WORDS)."\",'0','0','".$size."',\"".addslashes($_GET['rep'])."\")";
		mysql_query ($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
	}
	else { //si film trouve
    	$sql = "INSERT INTO movies VALUES(\"".$recherche['code']."\",\"".addslashes($link)."\",\"".addslashes($recherche['titre'])."\",\"".$recherche['note-public']."\",\"".$recherche['annee']."\",\"".$size."\",\"".addslashes($_GET['rep'])."\")";
		mysql_query ($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error()); 
		if(!empty($recherche['affiche'])){
			//echo $recherche['affiche'];
			if($MOVIES_DATABASE == 'Allocine'){
			$img = explode('/',$recherche['affiche']);
			$end_url = '';
			for($j=3;$j<count($img);$j++){
				$end_url = $end_url.'/'.$img[$j];
			}
			$img = $img[0].'//'.$img[2].'/r_150_204'.$end_url;
			copy($img,'../images/poster_small/'.$recherche['code'].'.jpg');
			}
			elseif($MOVIES_DATABASE == 'TMDb'){
			copy($recherche['affiche'],'../images/poster_small/'.$recherche['code'].'.jpg');
			}
		}
		$infos = $movies->movieInfos($recherche['code']);
		$genre = explode(',',$infos['genres']);
		$sql = "SELECT name FROM genres";
		$req = mysql_query($sql);
		$exist_genres = array();
		while ($data = mysql_fetch_array($req)){
			$exist_genres[] = $data['name'];
		}
		for($i=0;$i<count($genre);$i++){
			$gnre = trim($genre[$i]);
			if (!in_array($gnre,$exist_genres)){
				$sql = "INSERT INTO genres VALUES ('','".$gnre."')";
				mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
			}
			$sql = "SELECT id_genre FROM genres WHERE name='".$gnre."'";
			$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
			$data = mysql_fetch_array($req);
			$insert = "INSERT INTO movie_genre VALUES('','".$data['id_genre']."','".$recherche['code']."')";
			mysql_query($insert) or die ('Erreur SQL : '.mysql_error());
		}
	}
	//echo $link.' indexe en tant que '.$recherche['titre'];

}


?>