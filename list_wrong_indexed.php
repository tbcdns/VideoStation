<?php 
require('lib/config.php');
require('lib/API-allocine.php');
require('lib/functions.php');
connect($PASSWORD_SQL,$DATABASE);
echo '<div id="test"></div>';
$sql = "SELECT * FROM movies WHERE id_movie=0";
$req = mysql_query($sql) or die ('Erreur SQL '.mysql_error());
while($data = mysql_fetch_array($req)){
echo '<p rel="'.$data['id_movie'].'"><span class="link">'.$data['link'].'</span> <a href="update.php?link='.$data['link'].'&oldcode='.$data['id_movie'].'" class="nyroModal"><button value="Modifier">Modifier</button></a></p>';
}
?>

<script>
$('.nyroModal').nyroModal();
$('a.opener').click(function(){
			
			var id = $(this).parents('p').attr('rel');
			var link = $(this).prev().text();
			$('#test').html(link+' - '+id);
			$.ajax({
  				type: "GET",
  				url: "update.php",
   				data: "link="+link+"&oldcode="+id,
   				error:function(msg){
     				alert( "Error !: " + msg );
   				},
   				success:function(data){
   					//affiche le contenu du fichier dans le conteneur d&eacute;di&eacute;
					$('<div></div>').html(data).dialog({
					title: "Update",
					modal:true,
					height:400,
					width : 920
					});
				}
			});
			
			});
</script>