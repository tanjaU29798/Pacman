
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
			-->
	</style>

<body bgcolor="000000"> 

	<script type="text/javascript">

		/** 
			Funktion zum Auslesen des sessionStorages
			@param cname Spielername
		**/
		function getName(cname){
			return sessionStorage.getItem(cname);
		}

	</script>
	<div id="distance"></div>
	<div id="drumrum" align="center">
		<div id="uberschrift">
			<img src="bilder/uberschrift2.jpg" style="width:336px; height:101.5px;"><br><br>
		</div>
		<div>
			<p><script type="text/javascript">document.write("Viel Spaß "+getName("pname"));</script></p><br><br>
			<a onclick="neu()" href="game.php">Neues Spiel</a><br><br>
			<a href="page2.php">Level auswählen</a><br><br>
			<a href="game.php">Spiel fortsetzen</a><br><br>
			<a href="page3.php">Score</a><br><br><br><br>
			<a href="page0.php">Anderer Spieler?</a>		
		</div>
	</div>

	<script type="text/javascript">

		/**
			Setzt das sessionStorage auf "Neues Spiel", der "alte Spielstand" wird überschrieben
		 **/
		function neu(){
			sessionStorage.setItem("startlevel",1);
			sessionStorage.setItem("leben",3);
			sessionStorage.setItem("levelZahl",1);
			sessionStorage.setItem("score",0);
		}
	</script>
</body>
</html>