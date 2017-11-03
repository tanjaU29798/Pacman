
<html style=" width: 99%; height: 99%;">
	<style type="text/css">
		<!--
			@import url(https://fonts.googleapis.com/css?family=VT323);
			body {
				color: orange;
				font-family: 'VT323', sans-serif;
				font-size: 2em;
			}
			a {
				color: orange;
				text-decoration: none;
			}
			a:hover {
				text-decoration: underline;
				color: white;
			}
			* {
				margin:0;
				padding:0;
			}

			html, body {
				height: 100%;
				width: 100%;
			}

			#distance {
				width: 10px;
				height: 50%;
				margin-bottom: -250px;
				float: left;
			}

			#drumrum {
				margin: 0 auto;
				position: relative;
				height: 500px;
				width: 700px;
				clear: left;
			}
			#level {
				height: 500px;
				width: 400px;
			}
		-->
	</style>
<body bgcolor="000000" onLoad="levelAnzeigen(1)"> 

	<script type="text/javascript">

		/**
		Lädt die Bilddatei und startet bei Erfolg alle().
		@param levelZahl Aktuelle Levelzahl
		*/
		function levelAnzeigen(levelZahl){
			var fertig=false;
			var img=new Image();
			var pfad="level/LevelDarstellung"+levelZahl+".jpg";
			img.onload = function(){ 
				//Bildladen war erfolgreich
				alle(levelZahl,pfad);
			};
			img.src=pfad;
		}
		
		/**
		Fügt das Level der Anzeige hinzu.
		@param levelZahl Zahl des Levels
		@param pfad Pfad der Bilddatei
		*/
		function alle(levelZahl,pfad){
			var alles=document.getElementById("alles");
			alles.innerHTML=alles.innerHTML+'<a href="javascript:level('+levelZahl+')"><div id="level'+levelZahl+'">Level '+levelZahl+'<br><br><div id="img'+levelZahl+'" style="width:400px; height: 400px;"></div></div></a><br><br>';
			document.getElementById("img"+levelZahl).style.backgroundImage='url('+pfad+')';
			document.getElementById("img"+levelZahl).style.backgroundImage='url('+pfad+')';
			document.getElementById("img"+levelZahl).style.backgroundSize='400px 400px';
			levelAnzeigen(levelZahl+1);
		}
		
		/**
		Das Spiel wird auf das gewaehlte Level eingestellt, der "alte Spielstand" wird überschrieben.
		@param levelZahl Auf dieses Level wird das Spiel gesetzt
		 **/
		function level(levelZahl){
			sessionStorage.setItem("startlevel",levelZahl);
			sessionStorage.setItem("leben",3);
			sessionStorage.setItem("levelZahl",levelZahl);
			sessionStorage.setItem("score",0);
			window.location.href = "game.php";
		}
		
	</script>

	<div id="distance"></div>
		<div id="drumrum" align="center">
			
			<div id="uberschrift">
				<img src="bilder/uberschrift2.jpg" style="width:336px; height:101.5px;"><br><br>
			</div>
			<a href="page1.php">Hauptmenu</a>
			<br><br><br>
			<div id="alles"></div>
		</div>
	</div>



</body>
</html>