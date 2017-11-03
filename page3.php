<html style=" width: 99%; height: 99%;">
	<style type="text/css">
		<!--
			@import url(https://fonts.googleapis.com/css?family=VT323);
			body {
				color: orange;
				font-family: 'VT323', sans-serif;
				font-size: 2em;
			}
			table {
				font-size: 1em;
			}
			table th{
				font-size: 1.2em;
			}
			a {
				color: orange;
				text-decoration: none;
			}
			a:hover {
				text-decoration: underline;
				color: white;
			}
			table {
				text-align: center;
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
			<script type="text/javascript">
			document.write(sessionStorage.getItem("pname")+", du hast einen Score von "+sessionStorage.getItem("score")+" erreicht!");
			</script>
			<br><br>
		</div>
		<div align="center">
			<div><a href="page1.php">Hauptmenu</a></div>
		</div>
	</div>
</body>
</html>