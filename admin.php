<?php
$time_start = microtime(true);
session_start();
if($_GET['action'] == 'mod'){
	if($_POST['oldvideobase'] != $_POST['videobase']){
	$db = mysql_connect("localhost", "root", $_POST['pass']);
	if (!$db) {
    	die('Connexion impossible : ' . mysql_error());
	}
	mysql_select_db($_POST['bdd'],$db);
	$sql_movies = "TRUNCATE TABLE movies";
	$sql_genres = "TRUNCATE TABLE genres";
	$sql_movie_genre = "TRUNCATE TABLE movie_genre";
	mysql_query($sql_movies) or die ('Erreur SQL '.mysql_error());
	mysql_query($sql_genres) or die ('Erreur SQL '.mysql_error());
	mysql_query($sql_movie_genre) or die ('Erreur SQL '.mysql_error());
	mysql_close($db);
	}
	$file = fopen('lib/config.php','w');
	ftruncate($file,0);
	
	$ext = $_POST['ext'];
	$ext = explode(',',$ext);
	$ext_array = 'array(';
	for($i=0;$i<count($ext);$i++){
	$ext_array .= '"'.$ext[$i].'"';
	if($i != (count($ext)-1)) $ext_array .= ',';
	}
	$ext_array .= ')';
	
	$del = $_POST['deletedwords'];
	$del = explode(',',$del);
	$del_array = 'array(';
	for($i=0;$i<count($del);$i++){
	$del_array .= '"'.$del[$i].'"';
	if($i != (count($del)-1)) $del_array .= ',';
	}
	$del_array .= ')';
	
	$hid = $_POST['hiddenfiles'];
	$hid = explode(',',$hid);
	$hid_array = 'array(';
	for($i=0;$i<count($hid);$i++){
	$hid_array .= '"'.$hid[$i].'"';
	if($i != (count($hid)-1)) $hid_array .= ',';
	}
	$hid_array .= ')';
	
	if(empty($_POST['login'])) $login='FALSE';
	else $login='TRUE';
	
	if(empty($_POST['ftp'])) $ftp='FALSE';
	else $ftp='TRUE';
	
	if(empty($_POST['inauto'])) $inauto='FALSE';
	else $inauto='TRUE';
	
	if(empty($_POST['modal'])) $modal='FALSE';
	else $modal='TRUE';
	
	$content_config = '<?php
	$APP_NAME = "'.$_POST['title'].'";
	$PASSWORD_SQL = "'.$_POST['pass'].'";
	$DATABASE = "'.$_POST['bdd'].'";
	$PORT_SYNO = "'.$_POST['port'].'";
	$EXT = '.$ext_array.';
	$HIDDEN_FILES = '.$hid_array.';
	$DELETED_WORDS = '.$del_array.';
	$SERIES_DIR = "'.$_POST['seriesdir'].'";
	$MOVIES_DATABASE = "'.$_POST['videobase'].'" ;
	$SERIES_DATABASE = "'.$_POST['seriebase'].'" ;
	$LANGUAGE = "'.$_POST['lang'].'";
	$LOGIN = '.$login.';
	$MODAL = '.$modal.';
	$FTP = '.$ftp.';
	$INDEXATION_AUTO = '.$inauto.';
?>';
	
	if(fputs($file, $content_config)) $message = 'Configuration modifi&eacute;e avec succ&egrave;s!';
	else echo 'echec';
}
require_once('lib/config.php');
require_once('lib/API-allocine.php');
require_once('lib/functions.php');
connect($PASSWORD_SQL,$DATABASE);
login_check($LOGIN,$PORT_SYNO);
$root = admin($root);
if(!$root) die (include('login.php'));
$listmovies = listage('./video',$HIDDEN_FILES,$EXT);
$moviesinbase = moviesinbase();
foreach($listmovies as $movie){
	if(!in_array($movie,$moviesinbase)) $nonindexed[] = $movie;
}
?>
<?php //echo round((microtime(true)-$time_start),3);?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="css/nyroModal.css">
<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.17.custom.css" />

<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="http://code.jquery.com/ui/jquery-ui-git.js"></script>
<script type="text/javascript" src="js/jquery.nyroModal.custom.min.js"></script>
</head>
<body>
<!-- HEADER -->
<header>
<div class="header_left logo"><b><?php echo $APP_NAME;?></b></div>

	<div id="logout" class="header_left" style="margin-left:8px;">
		<?php echo '['.$_SESSION['user'].'] <span><a href="index.php?action=logout">Logout</a>';
		if ($root) echo ' | <a href="index.php">Accueil</a>';
		echo '</span>';?>
	</div>
	
	<div id="empty" class="header_left" style="margin-left:30px;padding-top:3px;">
	</div></header>
<!-- /HEADER -->

<!-- NAVIGATION -->
<nav class="margin">
<div class=""><h2><?php echo administration;?></h2></div>
</nav>
<!-- /NAVIGATION -->

<div id="content">
<div id="tabs" style="width:85%;margin-left:auto;margin-right:auto;max-height:400px;overflow:auto;">
	<ul>
		<li><a href="#tabs-1"><?php echo basicparameters;?></a></li>
		<li><a href="list_non_indexed.php"><?php echo nonindexedvideos;?></a></li>
		<li><a href="list_wrong_indexed.php"><?php echo wrongindexedvideos;?></a></li>
		<li><a href="#tabs-2"><?php echo donate;?></a></li>
	</ul>
	<div id="tabs-1">
		<p>
		<form method="POST" action="admin.php?action=mod">
		<table>
			<?php if(isset($message)) echo '<tr><td colspan="2" style="text-align:center;color:green;">'.$message.'</td></tr>';?>
			<tr>
				<td><?php echo appname;?></td><td><input type="text" name="title" value=<?php echo "\"".$APP_NAME."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo login;?></td><td><input type="checkbox" name="login" value="login" <?php if($LOGIN) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo modal;?></td><td><input type="checkbox" name="modal" value="modal" <?php if($MODAL) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo ftp;?></td><td><input type="checkbox" name="ftp" value="ftp" <?php if($FTP) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo autoindexing;?></td><td><input type="checkbox" name="inauto" value="inauto" <?php if($INDEXATION_AUTO) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo dbmovies;?></td><td><input type="radio" name="videobase" value="Allocine" onchange="changeAlert()" <?php if($MOVIES_DATABASE == 'Allocine') echo 'checked';?>>Allocine<input type="radio" name="videobase" value="TMDb" onchange="changeAlert()" <?php if($MOVIES_DATABASE == 'TMDb') echo 'checked';?>>TMDb <span class="changeAlert" style="color:red;"></span>
				<input type="hidden" name="oldvideobase" value="<?php echo $MOVIES_DATABASE;?>">
				</td>
			</tr>
			<tr>
				<td><?php echo dbseries;?></td><td><input type="radio" name="seriebase" value="Allocine" <?php if($SERIES_DATABASE == 'Allocine') echo 'checked';?>>Allocine<input type="radio" name="seriebase" value="TheTvDb" <?php if($SERIES_DATABASE == 'TheTvDb') echo 'checked';?> disabled>TheTvDb</td>
			</tr>
			<tr>
				<td><?php echo lang;?></td><td>
				<select name="lang">
					<option value="fr" <?php if($LANGUAGE == 'fr') echo 'selected';?>>Francais</option>
					<option value="en" <?php if($LANGUAGE == 'en') echo 'selected';?>>English</option>
				</select>
				</td>
			</tr>
			<tr>
				<td><?php echo sqlpass;?></td><td><input type="password" name="pass" value=<?php echo "\"".$PASSWORD_SQL."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo dbsql;?></td><td><input type="text" name="bdd" value=<?php echo "\"".$DATABASE."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo confport;?></td><td><input type="text" name="port" value=<?php echo "\"".$PORT_SYNO."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo seriesdir;?></td><td><input type="text" name="seriesdir" value=<?php echo "\"".$SERIES_DIR."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo videoext;?></td><td><input type="text" name="ext" value="<?php for($i=0;$i<count($EXT);$i++){echo $EXT[$i];if($i != (count($EXT)-1)) echo ',';}?>"></td>
			</tr>
			<tr>
				<td><?php echo hidden_files;?></td><td><input type="text" size="70" name="hiddenfiles" value="<?php for($i=0;$i<count($HIDDEN_FILES);$i++){echo $HIDDEN_FILES[$i];if($i != (count($HIDDEN_FILES)-1)) echo ',';}?>"></td>
			</tr>
			<tr>
				<td><?php echo deleted_words;?></td><td><input type="text" size="70" name="deletedwords" value="<?php for($i=0;$i<count($DELETED_WORDS);$i++){echo $DELETED_WORDS[$i];if($i != (count($DELETED_WORDS)-1)) echo ',';}?>"></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="<?php echo update;?>"></td>
			</tr>
		</table>
		</form>
		</p>
	</div>
	
	<div id="tabs-2">
	<p style="width:100%;">
	<?php echo donatetext;?>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" align="center" src="https://www.paypalobjects.com/fr_FR/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>
	</p>
	</div>
</div>

</div>

<footer>

</footer>
<script>

$( "#tabs" ).tabs({
	cache:true,
	load: function (e, ui) {
    	$(ui.panel).find(".tab-loading").remove();
   	},
   	select: function (e, ui) {
    	var $panel = $(ui.panel);
		if ($panel.is(":empty")) {
        	$panel.append("<div class='tab-loading' style='text-align:center;'><img src='images/ajaxLoader.gif'></div>")
     	}
    }
});

function changeAlert(){
	$('span.changeAlert').html('<?php echo warning1;?>');
}
	
</script>
</body>
</html>