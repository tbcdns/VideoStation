<?php
require_once('lib/API-allocine.php');
require_once('lib/API-TMDb.php');
require_once('lib/functions.php');
?>
<script type="text/javascript">
$(function() {
  $('.nyroModal').nyroModal();
});
</script>
<nav>
<?php
echo '<div><h2>Modification de '.urldecode($_GET['link']).'</h2></div>';
?>
</nav>
<div id="content">
<?php
if(isset($_GET['code'])){
connect($PASSWORD_SQL,$DATABASE);
if($_GET['oldcode'] != 0){
$sql = "DELETE FROM movie_genre WHERE fk_id_movie = '".$_GET['oldcode']."'";
mysql_query($sql) or die ('Erreur SQL '.mysql_error());
}
$sql = 'UPDATE movies SET id_movie="'.$_GET['code'].'", note="'.$_GET['note'].'", name="'.$_GET['name'].'", year="'.$_GET['year'].'" WHERE link="'.urldecode($_GET['link']).'"';
mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$allo = new AlloCine();
	$infos = $allo->movieInfos($_GET['code']);
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
		$insert = "INSERT INTO movie_genre VALUES('','".$data['id_genre']."','".$_GET['code']."')";
		mysql_query($insert) or die ('Erreur SQL : '.mysql_error());
		}

if(isset($_GET['poster'])){
copy($_GET['poster'], 'images/poster_small/'.$_GET['code'].'.jpg');
}

echo '<div style="text-align:center;">'.$_GET['link'].' correctement mis &agrave; jour!<br />Recharger la page pour prendre les modifications en compte.</div>';
exit();
}
/*************************************************

Affichage du résultat de la recherche (si recherche)

*************************************************/
if(isset($_POST['recherche'])){
	switch($MOVIES_DATABASE){
	case 'Allocine':
	$moviesSearch = new AlloCine();
	break;
	case 'TMDb':
	$moviesSearch = new TMDb($LANGUAGE);
	break;
	}
	$recherche = $moviesSearch->movieMultipleSearch($_POST['recherche'],10);
	echo '<table border="0" style="width:100%;">';
	echo "<tr>
		<td style=\"width:2%;text-align:center;\"></td>
		<td style=\"width:60%;text-align:center;\"><b>Titre</b></td>
		<td style=\"text-align:center;\"><b>Note</b></td>
		<td style=\"text-align:center;\"><b>Ann&eacute;e</b></td>
		<td style=\"text-align:center;\"></td>
		</tr>\n";
	for($i=0;$i<count($recherche);$i++){
		if(empty($recherche[$i]['affiche'])) echo '<tr><td style="text-align:center;"><img src="images/movie.png" alt="video" /></td>';
		else {
			if($MOVIES_DATABASE == 'Allocine'){
			$img = explode('/',$recherche[$i]['affiche']);
			$end_url = '';
			for($j=3;$j<count($img);$j++){
			$end_url = $end_url.'/'.$img[$j];
			}
			$img = $img[0].'//'.$img[2].'/r_150_204'.$end_url;
			echo '<tr><td><img src="'.$img.'" /></td>';
			$img_link = '&poster='.$img;
			}
			elseif($MOVIES_DATABASE == 'TMDb'){
			echo '<tr><td><img src="'.$recherche[$i]['affiche'].'"></td>';
			$img_link = '&poster='.$recherche[$i]['affiche'];
			}
		}
		echo '<td>'.$recherche[$i]['titre'].'</td>';
		if ($recherche[$i]['note-public'] != '0'){
			echo	'<td><table style="border:solid #435196 1px;height:15px;width:120px;padding:0;color:white;font-size:75%;" cellspacing="0">'; echo "\n";
			echo	'<tr><td style="background-color:#435196;width:'.($recherche[$i]['note-public']*20).'%;text-align:center;">'.$recherche[$i]['note-public'].'</td><td style="background-color:#ffffff;"></td></tr>'; echo "\n";
			echo	'</table></td>'; echo "\n";
		}
		else echo '<td></td>';
		if ($recherche[$i]['annee'] != '0') echo '<td style="text-align:center;">'.$recherche[$i]['annee'].'</td>';	
		else echo '<td></td>';
		echo '<td style="text-align:center;"><a href="update.php?link='.urlencode($_GET['link']).'&code='.$recherche[$i]['code'].'&note='.$recherche[$i]['note-public'].'&name='.addslashes($recherche[$i]['titre']).$img_link.'&year='.$recherche[$i]['annee'].'&oldcode='.$_GET['oldcode'].'" class="nyroModal"><input type="button" value="Selectionner"></a></td>';
		echo '</tr>';
	}
	echo '</table>';
}
?>
<br />
<form method="POST" action="update.php?link=<?php echo urlencode($_GET['link']); ?>&oldcode=<?php echo $_GET['oldcode']; ?>" class="nyroModal" style="text-align:center;">
<?php if(isset($_POST['recherche'])) echo 'Nouvelle recherche : '; else echo 'Rechercher un film : '; ?><input type="text" name="recherche" class="form">
<input type="submit" value="Rechercher" class="form">
</form>
<br />
</div>