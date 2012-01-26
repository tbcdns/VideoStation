<?php
$time_start = microtime(true);
session_start();
require_once('lib/config.php');
require_once('lib/API-allocine.php');
require_once('lib/functions.php');
require_once('lib/lang.php');
login_check($LOGIN,$PORT_SYNO);
if($INSTALL){
if($_GET['action'] == 'login') echo '<script>document.location.href="index.php"</script>';
die (include('INSTALL.php'));
 }
$root = admin($root);
if($LOGIN){ if(empty($_SESSION['user'])) die (include('login.php'));}
$dir = rep($_GET['rep']);
$tri = tri($_GET['tri']);
connect($PASSWORD_SQL,$DATABASE);
//$folders = check_files_folders($dir,$tri,$DELETED_WORDS,$ext,$HIDDEN_FILES,$SERIES_DIR);
$folders = folders($dir,$HIDDEN_FILES);
if (isset($_GET['recherche'])){
	$string = explode(' ',$_GET['recherche']);
	for($i=0;$i<count($string);$i++){
		if ($i == 0) $desc = "name LIKE '%".$string[$i]."%'"; 
		else $desc .= " OR name LIKE '%".$string[$i]."%'";
	}
	$sql = "SELECT * FROM movies WHERE ".$desc." ORDER BY ".$tri;
	$folders = null;
}
elseif (isset($_GET['genre'])){
	$sql = "SELECT DISTINCT id_movie, name, note, link, dir, year FROM movies, movie_genre WHERE fk_id_genre = '".$_GET['genre']."' and id_movie = fk_id_movie ORDER BY ".$tri;
	$folders = null;
}
else $sql = "SELECT * FROM movies WHERE dir='".$dir."' ORDER BY ".$tri;
$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
$nb_entree_bdd = mysql_num_rows($req); 
?>
<?php //echo round((microtime(true)-$time_start),3);?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $APP_NAME;?></title>
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="css/nyroModal.css">
<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.17.custom.css" />

<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="http://code.jquery.com/ui/jquery-ui-git.js"></script>
<script type="text/javascript" src="js/jquery.nyroModal.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.tools.min.js"></script>

<style>.ui-progressbar-value { background-image: url(css/images/pbar-ani.gif); }</style>
</head>
<body>
<!-- HEADER -->
<header>

	<div class="header_left logo"><b><?php echo $APP_NAME;?></b></div>

	<div id="logout" class="header_left" style="margin-left:8px;">
		<?php if(isset($_SESSION['user'])) echo '['.$_SESSION['user'].'] <span><a href="index.php?action=logout">Logout</a>';
			else echo '<a href="login.php">Login</a>';
		if ($root) echo ' | <a href="admin.php">Administration</a>';
		echo '</span>';?>
	</div>
	
	<div id="empty" class="header_left" style="margin-left:30px;padding-top:3px;">
	</div>
			
	<div class="header_right" style="margin-right:5px;padding-top:1px;">
		<form method="GET" action="index.php" >
			<input type="text" name="recherche">
			<button value="Rechercher" id="search" onclick="this.form.submit()">Rechercher</button>
		</form>
	</div>
			
	<div class="header_right">
		<form method="GET" action="<?php echo $_SERVER['REQUEST_URI'];?>">
			<select name="tri" onChange="this.form.submit()">
				<option><?php echo sortby;?></option>
				<option value="name"><?php echo name;?></option>
				<option value="note DESC"><?php echo note;?></option>
				<option value="year"><?php echo year;?></option>
			</select>
			<input type="hidden" name="<?php 
			if(isset($_GET['rep'])) echo 'rep'; 
			elseif(isset($_GET['recherche'])) echo research;
			else echo genre;?>" value="<?php 
			if (isset($_GET['rep'])) echo $_GET['rep']; 
			elseif (isset($_GET['recherche'])) echo $_GET['recherche'];
			else echo $_GET['genre'];?>">
		</form>
	</div>
			
	<div class="header_right">
		<?php
		$sql_genres = "SELECT * FROM genres ORDER BY name";
		$req_genres = mysql_query($sql_genres) or die ('Erreur SQL '.mysql_error());
		echo '<form method="GET" action="index.php"><select onChange="this.form.submit()" name="genre"><option>--'.display.'--</option>';
		while($data_genres = mysql_fetch_array($req_genres)){
			echo '<option value="'.$data_genres['id_genre'].'">'.$data_genres['name'].'</option>';
		}
		echo '</select></form>';
		?>
	</div>
		
			
			
			
</header>
<!-- /HEADER -->

<!-- NAVIGATION -->
<nav class="margin">
<?php
if(is_serie($SERIES_DIR)){
$src = banner_serie();
if(!empty($src)) $style='style="background-image:url('.$src.');" class="banner"';
}
?>
<div <?php echo $style;?>><?php 
if(isset($_GET['recherche'])) echo '<a href="index.php"><img src="images/home.png" alt="home"></a> <a href="index.php">'.home.'</a> / '.research.' ['.$_GET['recherche'].']';
elseif (isset($_GET['genre'])){
$sql_search_genre = "SELECT name FROM genres WHERE id_genre=".$_GET['genre'];
$req_search_genre = mysql_query($sql_search_genre) or die ('Erreur SQL :'.mysql_error());
$name_genre = mysql_fetch_array($req_search_genre);
echo '<a href="index.php"><img src="images/home.png" alt="home"></a> <a href="index.php">'.home.'</a> / Genre ['.$name_genre['name'].']';
}
else repertoire($dir);?></div>
</nav>
<!-- /NAVIGATION -->

<!-- CONTENU -->
<div id="content">
<?php
if (count($folders)!=0 and !isset($_GET['recherche']) and !isset($_GET['genre'])){
	echo '<hr>';
	foreach ($folders as $folder){
		echo '<a href="?rep='.$dir.'/'.$folder.'" class="movielist"><p class="folder"><img src="images/folder.png" alt="folder"> <span>'.$folder.'</span></p></a>';
	}
	echo '<hr>';
}
?>
<ul class="movielist">
<?php
$i=1;
while ($data = mysql_fetch_array($req)){
	echo '<li id="'.$i.'">';
	//echo '<a href="'.$dir.'/'.$data['link'].'">'.lenght($data['name'],18).'</a><br>';
	if($root) echo keywordsAdapt($data['link'],$DELETED_WORDS,1).'<br>';
	if(is_serie($SERIES_DIR)){
		$affiche = explode('-',$data['id_movie']);
		$affiche = 's-'.$affiche[0];
	}
	else $affiche = $data['id_movie'];
	if (is_file('images/poster_small/'.$affiche.'.jpg')){
		echo '<a href="#null" rel="'.$data['id_movie'].'"';
		if ($MODAL) echo 'class="opener movielist"';
		echo '><img src="images/poster_small/'.$affiche.'.jpg" alt="'.$data['name'].'" class="poster"></a>';
	}
	else { 
	if($data['id_movie'] != '0' and $data['id_movie'] != '0-0-0') echo '<a href="#null" rel="'.$data['id_movie'].'" class="opener movielist">';
	echo '<img src="images/movie.png" style="margin-top:20%;" alt="Film" class="poster">';
	if($data['id_movie'] != '0' and $data['id_movie'] != '0-0-0') echo '</a>';
	}
	//DIV TOOLTIP
	echo '<div class="tooltip">
	<table border="0"';
	if(!$root) echo 'style="margin-left:20px;"';
	echo '><tr>';
	if($data['id_movie'] != '0' and $data['id_movie'] != '0-0-0') echo '<td><a href="#null" rel="'.$data['id_movie'].'" class="opener movielist"><img src="images/info.png" alt="Info"></a></td>';
	echo '<td><a href="';
	if($FTP) echo 'ftp://'.$_SERVER['SERVER_NAME'].'/'.$data['dir'].'/'.$data['link'];
	else echo $data['dir'].'/'.$data['link'];
	echo '" class="movielist" title="'.$data['link'].'"><img src="images/down.png"></a></td>';
	if($root) echo '<td><a href="update.php?link='.urlencode($data['link']).'&oldcode='.$data['id_movie'].'" class="nyroModal"><img src="images/update.png"></a></td>';
	echo '</tr>
	<tr>';
	if($data['id_movie'] != '0' and $data['id_movie'] != '0-0-0') echo '<td>'.infos.'</td>';
	echo '<td>'.link.'</td>';
	if($root and !is_serie($SERIES_DIR)) echo '<td>'.update.'</td>';
	echo '</tr>
	</table>
	</div>';
	// /DIV TOOLTIP
	echo '<div class="title"><h5><a href="';
	if($FTP) echo 'ftp://'.$_SERVER['SERVER_NAME'].'/'.$data['dir'].'/'.$data['link'];
	else echo $data['dir'].'/'.$data['link'];
	echo '" class="movielist" title="'.$data['link'].'">'.length($data['name'],22).'</a></h5><p>'.$data['year'].'</p></div>';
	echo '<div class="stars">'.stars($data['note']).'</div>';
	echo '</li>';
	$i++;
}
?>
</ul>
<div class="resume">
<?php echo count($folders).' '.folders.' - '.$nb_entree_bdd.' '.files;?>
</div>
</div>
<!-- /CONTENU -->

<!-- FOOTER -->
<footer>
<?php echo pagegeneration.' '.round((microtime(true)-$time_start),3);?> s.
</footer>
<!-- /FOOTER -->
<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
	$('.nyroModal').nyroModal();
	
	$('.poster').tooltip({ 
		effect: 'slide', 
		predelay:1100, 
		delay:600,
		opacity:1,
		offset:[15, 0]
		});
	
	
	$('#search').button({
	icons: {
                primary: "ui-icon-search"
            },
            text:false
            
        });
	$('input[type="text"]').buttonset();

	$('#content ul li img.poster').hover(function(){
	$(this).addClass('gallerie_onMouse');
	},
	function(){
	$(this).removeClass('gallerie_onMouse');
	});	
	
	$('#content p.folder').hover(function(){
	$(this).addClass('folderHover');
	},
	function(){
	$(this).removeClass('folderHover');
	});
	
	$('a.opener').click(function(){
			
			var mid = $(this).attr('rel');
			var screenheight = (screen.height-200);
			$.ajax({
  				type: "GET",
  				url: "video.php",
   				data: "mid="+mid,
   				error:function(msg){
     				alert( "Error !: " + msg );
   				},
   				success:function(data){
   					//affiche le contenu du fichier dans le conteneur d&eacute;di&eacute;
					$('<div id="dialog"></div>').html(data).dialog({
					title: '<?php echo details;?>',
					modal:true,
					maxHeight: screenheight,
					width : 940,
					draggable:true,
					resizable:false
					});
				}
			});
			
			});
			
			
			
	$('#loading').hide().ajaxStart(function() {
        $(this).show();
    }).ajaxStop(function() {
        $(this).hide();
    });
    
    $('#logout span').hide();
    $('#logout').hover(function(){
    $('#logout span').fadeIn();
    },function(){
    $('#logout span').delay(1200).fadeOut();
    });
    
});
</script>
<?php if($INDEXATION_AUTO) index_auto($dir,$HIDDEN_FILES,$EXT);?>
<div id="loading"><img src="images/ajaxLoader.gif" style="margin-top:20%;"><br><br><?php echo loading;?> ...</div>
</body>
</html>