
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
			.textbox {
				background-color: #000000;
				border: 1px solid orange;
				height: 25px;
				width: 200px;
				outline: 0;
				font-family: 'VT323', sans-serif;
				font-size: 0.8em;
				color: orange;
			}
			
			#button {
				width:150px; 
				background-color: black; 
				border: none; color: 
				orange; font-family: 'VT323', sans-serif; 
				font-size: 0.8em;
			}
			
			#button:hover
			{
			  cursor: pointer;
			}
		-->
	</style>
<body bgcolor="000000"> 

	<script type="text/javascript">
		/**
			Speichert den eigegebenen Text in den sessionStorage
		**/
			function save_data_to_Session(name){
			sessionStorage.setItem("pname",name);
			//alert(sessionStorage.getItem("pname"));
			window.location.href = "page1.php";
		}
	</script>

	<div id="distance"></div>
	<div id="drumrum" align="center">
		<div id="uberschrift">
			<img src="bilder/uberschrift2.jpg" style="width:336px; height:101.5px;"><br><br>
		</div>
		<div>
			<div>
				<p>Hallo!<br>Bevor Du mit dem Spielen anfängst, willst Du mir Deinen Namen verraten?</p>
				<form name="player" method="post">
					<input class="textbox" type="text" name="nplayer">
					<input id="button" type="submit" value="Das bin ich">
					
				</form>
			</div>
		</div>
	</div>

<?php 

	$final=""; 

	/**
	Diese Funktion zerlegt einen String rekursiv so lange in kleinere Worte, bis der eigentliche Teilname gefunden wurde. Dieser wird dann so korrigiert, dass der erste Buchstabe groß ist und alle anderen klein folgen.

	So können auch Eingaben mit mehr als zwei Vornamen bearbeitet und korrigiert werden. Durch die Rekursion wird auch sicher gestellt, dass mehrere Fehler in einem Wort auftreten können. 

	Folgende Korrekturen sind nicht möglich:
	Pe ter --> Peter (es gibt auch Namen wie Ed oder An mit zwei Buchstaben)
	Peter Peter --> Peter (das Programm merkt sich durch die Rekursion den voherigen String nicht und kann daher nicht vergleichen)

	Folgende Korrekturen sind möglich:
	PEter --> Peter
	Per peter --> Per Peter
	klausPEter --> Klaus Peter
	Klaus-pEter --> Klaus-Peter

	und außerdem:
	PETER --> Peter
	kLaus-peter-kArl --> Klaus-Peter-Karl (Korrekturen in beliebiger Wortzahl)
	Klaus Peter-kArl heinz -->  Klaus Peter-Karl Heinz (richtiges Setzen von Bindestrich und Leerzeichen)
	klausPeter-Heinz --> Klaus Peter-Heinz (richtiges Setzen von Bindestrich und Leerzeichen, wenn klaus ursprünglich mit vor dem Bindestrich war)
	HansPeterKlaus --> Hans Peter Klaus (Namen werden durch die Rekursion auch ohne Leerzeichen hintereinander erkannt)
	*/
	function correct($wort, $after){
			
			//trennt die Teilstrings an den Bindestrichen
			$bindestrichArray = explode('-',$wort);
			
			//schickt die Teilstrings in die Rekursion, falls ein Bindestrich enthalten war
			if(count($bindestrichArray)>1){
				//Durchlaueft das bindestrichArray und schickt die Teilstrings in Rekursion
				for ($j=0; $j<count($bindestrichArray); $j=$j+1){
				
					//setzt ein Bindestrich an das Ende jedes Teilstrings (ausser beim letzten)
					if ($j<count($bindestrichArray)-1){
						correct($bindestrichArray[$j],"-");
					}else{
						correct($bindestrichArray[$j]," ");
					}
				}
			//trennt ähnlich wie beim Bindestrich nun bei Großbuchstaben mitten im Wort
			//dies ist nur ab einer Wortlänge von 4 möglich, das Wort darf nicht komplett aus Buchstaben bestehen und es muss sich mindestens ein Großbuchstabe in der Mitte befinden
			}else if (strlen($wort)>=4 && strtoupper($wort)!=$wort && substr($wort, 2, strlen($wort)-3)!=strtolower(substr($wort, 2, strlen($wort)-3))){
				//sucht bis der erste Großbuchstabe ab der 3. Position gefunden wurde und trennt an dieser Stelle
				for ($x=2; $x<strlen($wort)-1; $x++){
					//schickt die Teilstrings erneut in Rekursion
					if ($wort[$x]==strtoupper($wort[$x])){
						correct (substr ($wort, 0, $x), " ");
						correct (substr ($wort, $x, strlen($wort)), $after);
						break;
					}
				}	
			}else {
					//das Wort ist in sein kleinstes Wort/den Namen "gesiebt" 
					//der erste Buchstabe wird nun groß und alle folgenden klein korrigiert
					$wort = ucfirst(strtolower($wort));
					global $final;
					$final=$final.$wort.$after;				
			}

	}

	if(isset($_POST['nplayer'])){
		$eingabe=$_POST["nplayer"];
		//trennt den String zwischen den Leerzeichen und speichert sie in ein Array
		$eingabeArray = explode(' ',$eingabe);
		//Durchlaueft das eingabeArray und schickt die einzelnen Strings zur Korrektur
		for ($i=0; $i<count($eingabeArray); $i=$i+1){
			$wort=$eingabeArray[$i];
			correct($wort, " ");
		}
		echo "<script type='text/javascript'>save_data_to_Session('$final');</script>";
	}

?>
</body>
</html>