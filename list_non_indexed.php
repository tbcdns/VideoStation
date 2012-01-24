<?php 
require('lib/config.php');
require('lib/API-allocine.php');
require('lib/functions.php');
connect($PASSWORD_SQL,$DATABASE);

$listmovies = listage('./video',$HIDDEN_FILES,$EXT);
$moviesinbase = moviesinbase();
foreach ($moviesinbase as $movie){
	if(!in_array($movie,$listmovies)){
	$renamed[] = $movie;//movies in the database but not in the folders list
	//mysql_query('DELETE FROM movies WHERE link="'.addslashes($data['link']).'"') or die ('Erreur SQL : '.mysql_error());
	//mysql_query("DELETE FROM movie_genre WHERE fk_id_movie = '".$data['id_movie']."'") or die ('Erreur SQL '.mysql_error());
	}	
}
foreach($listmovies as $movie){
	if(!in_array($movie,$moviesinbase)) $nonindexed[] = $movie;//movies in the folders list but not in the database
}
print_r($renamed);
$tot = count($nonindexed);
if(!empty($tot)){
natcasesort($nonindexed);
?>
<button id="index" value="indexer">Tout indexer!</button>
<div id="patient"></div><div id="progressbar"></div>
<?php
foreach($nonindexed as $key => $movie){
$separate = explode('/',$movie);
$niv = count($separate);
$link=$separate[$niv-1];
$rep = '';
for($j=0;$j<count($separate)-1;$j++){
$rep .= $separate[$j];
if($j != (count($separate)-2)) $rep .= '/';
}
echo '<p id="'.$key.'" class="movie"><span class="dir" style="font-size:80%;">'.$rep.'</span> - <span class="link" style="font-size:80%;">'.$link.'</span><span class="complete"></span></p>';


}
}
else echo 'Toutes les videos sont indexes';

 ?>
 <script>
$('button').button();

$('#index').click(function(){
	$('#index').hide();
	var i = 1;
	$('p.movie').each(function(){
		var rep = $(this).children('.dir').html();
		var link = $(this).children('.link').html();
		var key = $(this).attr('id');
		$.ajax({
  				type: "GET",
  				url: "lib/index_movie.php",
   				data: "rep="+rep+"&link="+link,
   				error:function(msg){
     				alert( "Error ! : " + msg );
   				},
   				success:function(data){
   					//insere le resultat dans le textarea 'descy'
					//$('#'+key).children('span.complete').html('complete');
					$('#'+key).fadeOut('fast');
					$('#patient').html(Math.round((((i)/<?php echo $tot;?>)*100))+' %');
					$( "#progressbar" ).progressbar({
						value: (((i)/<?php echo $tot;?>)*100)
					});
					i++;
				}
		});
	});
});
</script>