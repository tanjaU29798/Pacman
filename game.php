<!DOCTYPE html>
<html style=" width: 99%; height: 99%;">
<head>
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
				text-align:center;
			}
			a:hover {
				text-decoration: underline;
				color: white;
			}
		-->
</style>			
<script type="text/javascript">

	//Default
	var startL = 1;
	var life = 3;
	var level = 1;
	var punkte = 0;
	
	//Variablen für den Spielstart
	var levelZahl;
	var startlevel;
	
	//feste Werte für alle Level
	var width=17;
	var zwischenschritte=10;
	var geistSleep=100;
	var maxSuche=5;
	var ladeZeit=300;
	
	//dynamische Level-Variablen (vom Level und/oder der Fenstergröße abhängig)
	var pixelBreite=0;
	var bildschirmbreite=0;
	var logikImg;
	var darstellungImg;
	var context;
	var contextP;
	
	//dynamische Spiel-Variablen 
	var geisterzahl=0;
	var pacman;
	var geisterjagd=0;
	var gesammelt;
	var gewonnen;
	var score=0;
	var first=true;
	var leben;
	var frequenz=0;
	var markierungCount=0;
	
	//Bildinformationen
	var imageData;
	var dotsC;
	
	//Arrays
	var richtungen=new Array();
	var dots=new Array();
	var geister=new Array();
	var strategien=new Array();
	
	//Variablen für die Routinen (Intervalle)
	var pacRun;
	var geistRun;
	var geistZeit;
	
	/**
	Initialisiert ein Spiel anhand des sessionStorages
	*/
	function spiel(){
		//setzt Startwerte
		startlevel=parseInt(sessionStorage.getItem("startlevel"));
		leben=parseInt(sessionStorage.getItem("leben"));
		levelZahl=parseInt(sessionStorage.getItem("levelZahl"));
		score=parseInt(sessionStorage.getItem("score"));
		
		if(leben<=0 || levelZahl >=8){
			verloren();
		}
		
		initGroesse();
		initHTML();
		levelAufbauen();
		initRichtungen();
		initChars();
		
		//Eventlistener erkennt Pfeiltasten
		document.addEventListener("keydown", pfeiltaste);
		//Spiel wird "startbar"
		first=true;

		//Inhalt der Infobox
		document.getElementById("oben").innerHTML="Score: "+score+"<br><br>";
		document.getElementById("mitte").style.width=pixelBreite+"px";
		document.getElementById("mitte").style.height=pixelBreite*3+"px";
		lebensanzeige(leben);
	}
	
	/**
	Speichert den aktuellen Spielstand in ein sessionStorage
	*/
	function spielSpeichern(){
		//speichert startlevel, leben, levelZahl und score
		sessionStorage.setItem("startlevel",startlevel);
		sessionStorage.setItem("leben",leben);
		sessionStorage.setItem("levelZahl",levelZahl);
		sessionStorage.setItem("score",score);
	}
	
	/**
	Lädt das nächste Level 
	*/
	function nextLevel(){
		spielSpeichern();
		//erhöht die Levelzahl
		levelZahl++;
		
		if(levelZahl >=8){
			verloren();
		}
		
		levelAufbauen();
		home();
		
		//Timer werden gestoppt um später wieder neu gestartet zu werden
		clearInterval(pacRun);
		clearInterval(geistRun);
		clearInterval(geistZeit);
		
		geisterjagd=0;
		
		//Spiel wird nach Timeout "startbar"
		setTimeout (function(){first=true}, 800);

		
		//Bilder der Charaktere werden aktualisiert
		document.getElementById("pacimg").src="bilder/pacman_8.png";

		var geist0 = document.getElementById('geist0');
		var geist1 = document.getElementById('geist1');
		var geist2 = document.getElementById('geist2');
		var geist3 = document.getElementById('geist3');
		contextG0=geist0.getContext('2d');
		contextG1=geist1.getContext('2d');
		contextG2=geist2.getContext('2d');
		contextG3=geist3.getContext('2d');

		geist0Img = new Image();
		geist0Img.onload = function(){
			contextG0.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist0Img.src = "bilder/geist0.png";
		geist1Img = new Image();
		geist1Img.onload = function(){
			contextG1.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist1Img.src = "bilder/geist1.png";
		geist2Img = new Image();
		geist2Img.onload = function(){
			contextG2.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist2Img.src = "bilder/geist2.png";
		geist3Img = new Image();
		geist3Img.onload = function(){
			contextG3.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist3Img.src = "bilder/geist3.png";
	}
	
	/**
	Errechnet die Größe des Spielfeldes durch die Fenstergröße
	*/
	function initGroesse(){
		window.addEventListener("keydown", function(e) {
    		// space and arrow keys
    		if([32, 37, 38, 39, 40].indexOf(e.keyCode) > -1) {
       			e.preventDefault();
    		}
		}, false);
		//Spielbreite Defaultgröße
		var spielbreite=width*zwischenschritte*2;
		var infobox=200;
		
		//Rechnet die mögliche Gesamtbreite des Spiels aus (muss gemeinsamer Teiler von width und der Zahl der Zwischenschritte sein --> Sonst Rundungsfehler)
		if (window.innerHeight<window.innerWidth){
			bildschirmbreite=window.innerHeight;
			document.getElementById("drumrum").style.minWidth=bildschirmbreite+infobox+"px";
			spielbreite=parseInt(bildschirmbreite/width/(zwischenschritte))*width*zwischenschritte;
		}else if(window.innerHeight>window.innerWidth){
			bildschirmbreite=window.innerWidth-infobox;
			spielbreite=parseInt(bildschirmbreite/width/(zwischenschritte))*width*zwischenschritte;
		}
		document.getElementById("infobox").style.height=spielbreite+"px";

		//Mindestgröße
		if (spielbreite==0){
			spielbreite=width*zwischenschritte;
		}
		
		//Breite für einen Pixel
		pixelBreite=spielbreite/width;
	}
	
	/**
	Setzt die Größenwerte für den HTML-Code mit DOM
	*/
	function initHTML(){
		
		document.getElementById("mycanvas").width=width;
		document.getElementById("mycanvas").height=width;

		document.getElementById("level").style.width=width*pixelBreite+"px";
		document.getElementById("level").style.height=width*pixelBreite+"px";
		
		document.getElementById("dots").width=width*pixelBreite;
		document.getElementById("dots").height=width*pixelBreite;
		
		document.getElementById("markierung").width=width*pixelBreite;
		document.getElementById("markierung").height=width*pixelBreite;
		
		document.getElementById("game").width=width*pixelBreite;
		document.getElementById("game").height=width*pixelBreite;		
	}
	
	/**
	Belegt das Array für die Richtungen und ordnet für jede Richtung links, rechts und back zu
	*/
	function initRichtungen(){
		//oben
		richtungen["oben"]=new Richtung(0,pixelBreite*-1,"oben");
		//links
		richtungen["links"]=new Richtung(pixelBreite*-1,0,"links");
		//rechts
		richtungen["rechts"]=new Richtung(pixelBreite,0,"rechts");
		//unten
		richtungen["unten"]=new Richtung(0,pixelBreite,"unten");
		
		richtungen["oben"].links=richtungen["links"];
		richtungen["oben"].rechts=richtungen["rechts"];
		richtungen["oben"].back=richtungen["unten"];
		
		richtungen["links"].links=richtungen["unten"];
		richtungen["links"].rechts=richtungen["oben"];
		richtungen["links"].back=richtungen["rechts"];
		
		richtungen["rechts"].links=richtungen["oben"];
		richtungen["rechts"].rechts=richtungen["unten"];
		richtungen["rechts"].back=richtungen["links"];
		
		richtungen["unten"].links=richtungen["rechts"];
		richtungen["unten"].rechts=richtungen["links"];
		richtungen["unten"].back=richtungen["oben"];
		
	}
	/**
	Fügt die Geister zum Level hinzu
	*/
	function initChars(){
		//erster Geist
		document.getElementById("geist0").width=pixelBreite;
		document.getElementById("geist0").height=pixelBreite;
		document.getElementById("geist0").style.marginLeft=8*pixelBreite+"px";
		document.getElementById("geist0").style.marginTop=6*pixelBreite+"px";
		//Neuer Geist in das Geister-Array
		geister[0]=new Char(8*pixelBreite,6*pixelBreite,richtungen["oben"],richtungen["oben"],"geist0",0*geistSleep, strategie1);
		
		//zweiter Geist
		document.getElementById("geist1").width=pixelBreite;
		document.getElementById("geist1").height=pixelBreite;
		document.getElementById("geist1").style.marginLeft=7*pixelBreite+"px";
		document.getElementById("geist1").style.marginTop=7*pixelBreite+"px";
		//Neuer Geist in das Geister-Array
		geister[1]=new Char(7*pixelBreite,7*pixelBreite,richtungen["links"],richtungen["oben"],"geist1",1*geistSleep, strategie0);
		
		//dritter Geist
		document.getElementById("geist2").style.marginLeft=8*pixelBreite+"px";
		document.getElementById("geist2").style.marginTop=7*pixelBreite+"px";
		//Neuer Geist in das Geister-Array
		geister[2]=new Char(8*pixelBreite,7*pixelBreite,richtungen["unten"],richtungen["oben"],"geist2",2*geistSleep, strategie0);
		
		//vierter Geist
		document.getElementById("geist3").style.marginLeft=9*pixelBreite+"px";
		document.getElementById("geist3").style.marginTop=7*pixelBreite+"px";
		//Neuer Geist in das Geister-Array
		geister[3]=new Char(9*pixelBreite,7*pixelBreite,richtungen["rechts"],richtungen["oben"],"geist3",3*geistSleep, strategie0);
		
		//pacman
		pacman=new Char(8*pixelBreite,10*pixelBreite,richtungen[1],richtungen[1],"pacman",0, strategie0);
		document.getElementById("pacman").style.marginTop=pacman.y+"px";
		document.getElementById("pacman").style.marginLeft=pacman.x+"px";
		document.getElementById("pacman").style.width=pixelBreite+"px";
		document.getElementById("pacman").style.height=pixelBreite+"px";
		document.getElementById("pacimg").style.width=pixelBreite+"px";
		document.getElementById("pacimg").style.height=pixelBreite+"px";
		
		//zeichnet die Bilder der Geister
		var geist0 = document.getElementById('geist0');
		var geist1 = document.getElementById('geist1');
		var geist2 = document.getElementById('geist2');
		var geist3 = document.getElementById('geist3');
		contextG0=geist0.getContext('2d');
		contextG1=geist1.getContext('2d');
		contextG2=geist2.getContext('2d');
		contextG3=geist3.getContext('2d');

		geist0Img = new Image();
		geist0Img.onload = function(){
			contextG0.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist0Img.src = "bilder/geist0.png";
		geist1Img = new Image();
		geist1Img.onload = function(){
			contextG1.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist1Img.src = "bilder/geist1.png";
		geist2Img = new Image();
		geist2Img.onload = function(){
			contextG2.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist2Img.src = "bilder/geist2.png";
		geist3Img = new Image();
		geist3Img.onload = function(){
			contextG3.drawImage(this,0,0,this.width * pixelBreite / 1120,this.height * pixelBreite / 1200);
		}
		geist3Img.src = "bilder/geist3.png";
	}
	
	/**
	Baut das Level auf
	*/
	function levelAufbauen(){		
		//Startwerte für gewonnen und gesammelt (gewonnen wird später noch hochgezählt)
		//sie sind -1, weil Pacman am Anfang bereits auf einem Punkt steht und dieser nicht mitgezählt werden soll
		gesammelt=-1;
		gewonnen=-1;
		imageData=0;
		geisterzahl=2;
		frequenz=50;
		
		if (levelZahl>=2){
			document.getElementById("geist2").width=pixelBreite;
			document.getElementById("geist2").height=pixelBreite;
			geisterzahl=3;
			frequenz=40;
		}
		
		if (levelZahl>=3){
			document.getElementById("geist3").width=pixelBreite;
			document.getElementById("geist3").height=pixelBreite;
			geisterzahl=4;
			frequenz=frequenz-10;
		}
		
		//Bilder zeichnen (Bild der Bearbeitung im Hintergrund)
		var element = document.getElementById('mycanvas');
		var game=document.getElementById('game');

		contextP=game.getContext('2d');
		context = element.getContext('2d');
		
		logikImg = new Image();
		logikImg.onload = function(){ 
			context.drawImage(this,0,0,width,width);
		}; 
		logikImg.onerror = function(){ 
			//window.location.href = "page3.php";
			alert("Level"+levelZahl+".jpg");
		};
		logikImg.src = "level/Level"+levelZahl+".jpg";
		
		darstellungImg = new Image();
		darstellungImg.onload = function(){ 
			contextP.drawImage(this,0,0,width*pixelBreite,width*pixelBreite);
		}; 
		darstellungImg.src = "level/LevelDarstellung"+levelZahl+".jpg";
	
		setTimeout(function(){
			imageData=context.getImageData(0,0,width,width);
			punkteSetzen();	
		}, ladeZeit);

		

	}
	
	/**
	Setzt alle Punkte auf die Wege
	*/
	function punkteSetzen(){
		//Ebene für Punkte
		var dot = new Image();
		dot.src="bilder/dot.png";
		setTimeout(function(){
			var e=document.getElementById("dots");
			dotsC=e.getContext("2d");
			dotsC.clearRect(0, 0, width*pixelBreite, width*pixelBreite);
			//Geht die Pixel einzeln durch und setzt Punkte bei Weiß (bzw Megapunkte bei Grün --> noch nicht implementiert)
			for (var i=0; i<imageData.data.length; i+=4) {
				var r = imageData.data[i];
				var g = imageData.data[i+1];
				var x=(i/4)%width;
				var y=(i/4-x)/width;
				if(g<150 && r > 150){
					//Kraftpille (Farbe lila)
					dotsC.drawImage(dot,x*pixelBreite+pixelBreite*1.6/5,y*pixelBreite+pixelBreite*1.6/5, pixelBreite*1.8/5, pixelBreite*1.8/5);
					dots[i/4]=2;
					gewonnen++;
				}else if (r > 150) {
					//Pixel ist weiß --> dots werden gesetzt
					dotsC.drawImage(dot,x*pixelBreite+pixelBreite*2/5,y*pixelBreite+pixelBreite*2/5, pixelBreite/5, pixelBreite/5);
					dots[i/4]=1;
					gewonnen++;
				}else{
					//Pixel ist schwarz
					dots[i/4]=0;	
				}
			}
		}, ladeZeit);
	}

	/**
	Fängt die Eingaben über die Tastuatur ab und setzt die entsprechenden Richtungen
	@event die gedrückte Taste
	*/
	function pfeiltaste(event) {
		//Pfeiltasten
		if (event.keyCode==37){
			//links
			pacman.neuRichtung=richtungen["links"];
			document.getElementById("pacimg").src="bilder/pacman_3.gif";
		}else if (event.keyCode==38){
			//oben
			pacman.neuRichtung=richtungen["oben"];
		}else if (event.keyCode==39){
			//rechts
			pacman.neuRichtung=richtungen["rechts"];
			document.getElementById("pacimg").src="bilder/pacman_2.gif";
		}else if (event.keyCode==40){
			//unten
			pacman.neuRichtung=richtungen["unten"];
		}
		//startet das Spiel sobald zum ersten Mal eine Tate gedrückt wurde
		if (first){
			clearInterval(geistRun);
			clearInterval(geistZeit);
			
			//Timer starten mit dem Bewegen der Figuren
			pacRun=setInterval(runPacman, frequenz);
			geistRun=setInterval(runGeister, frequenz);
			//macht das Spiel vorerst "nichtstartbar" (es läuft ja bereits)
			first=false;
		}
	}
	
	/**
	Erstellt die Charaktere (Pacman und Geister --> evtl. werden beide noch getrennt)
	@param x Homeposition X
	@param y Homeposition Y
	@param richtung aktuelle Richtung
	@param neuRichtung gewünschte Richtungn(pacman)
	@param name Stringname der Figur
	@param startzeit Count, nach dem die Geister starten (geister)
	@param strategie Suchstrategie
	*/
	function Char(x,y,richtung,neuRichtung,name,startzeit,strategie){
		//aktueller Ort
		this.x=x;
		this.y=y;
		//Home
		this.homeX=x;
		this.homeY=y;
		//aktuelle Richtung
		this.richtung=richtung;
		//gewünschte Richtung
		this.neuRichtung=neuRichtung;
		//Stringname
		this.name=name;
		//Count, nach dem die Geister starten
		this.startzeit=startzeit; 
		
		//benötigte Attribute für die Methoden
		this.wegFrei=false; //die Figuren machen nur Schritte, wenn der Weg frei ist
		this.schrittzahl=0; //eine Figur macht Zwischenschritte bis zum nächsten Punkt (wird bis 0 runter gezählt)
		
		//merkt sich die Y-Höhe wenn eine Brücke über das Feld hinaus genommen wird
		this.brueckeY;
		
		this.strategie=strategie;
	}
	
	/**
	Erstellt an sich Richtungen, wäre aber privat (Richtungen werden gezielt von initRichtungen erstellt, da es nur 4 geben darf)
	@param x und y bilden zusammen eine Art Vektor
	@param y und x bilden zusammen eine Art Vektor
	@param name Name der Richtung
	*/
	function Richtung(x,y,name){
		//"Vektor"
		this.x=x;
		this.y=y;
		//Von dieser Richtung aus gesehen Referenzen auf die Richtungen links/rechts oder entgegengesetzt davon
		this.links;
		this.rechts;
		this.back;
		//Name als String
		this.name=name;
	}
	
	/**
	Guckt, ob ein Weg in der gewünschten Richtung oder der alten Richtung frei ist
	@param char Figur, die den Weg finden muss
	*/
	function wegFinden(char){
		//Gewünschte Richtung frei?
		char.wegFrei=istWegFrei((char.x+char.neuRichtung.x)/pixelBreite,(char.y+char.neuRichtung.y)/pixelBreite,char);
		if (char.wegFrei){
			//neue Richtung wird gesetzt
			char.richtung=char.neuRichtung;
			//Schrittzahl wuird wieder hoch gesetzt
			char.schrittzahl=zwischenschritte;	
		}else{
			//alte/aktuelle Richtung frei?
			char.wegFrei=istWegFrei((char.x+char.richtung.x)/pixelBreite,(char.y+char.richtung.y)/pixelBreite,char);
			if (char.wegFrei){
				//Schrittzahl wuird wieder hoch gesetzt
				char.schrittzahl=zwischenschritte;
			}
		}
	}
	
	/**
	Guckt mit Hilfe des Bildes, ob ein Weg an der Position frei/weiß ist und überprüft den Weg über die Brücken
	@param x X-Wert der zu überprüfenden Position
	@param y Y-Wert der zu überprüfenden Position
	@param char die Figur, die fragt
	*/
	function istWegFrei(x, y, char){	
		//Will die Figur die Brücke überqueren? Merkt sich die Y-Höhe
		if (char.x==0 || char.x/pixelBreite==width-1){
			char.brueckeY=y;
		}
		
		//gibt den Weg frei, wenn die Y-Höhe gleich bleibt und setzt den X-Wert neu
		if (x>=width || x<=-1){
			if(y==char.brueckeY){
				//setzt den X-Wert neu (je nach Laufrichtung)
				if (x>width+1)char.x=-1*pixelBreite;
				if (x<-1)char.x=width*pixelBreite;
				return true;
			}else{
				return false;
			}
		}
		return istWegWeiss(x,y);
	}
	
	/**
	Guckt mit Hilfe des Bildes, ob ein Weg an der Position frei/weiß ist
	@param x X-Wert der zu überprüfenden Position
	@param y Y-Wert der zu überprüfenden Position
	*/
	function istWegWeiss(x, y){	
		//Überprüft das Bild
		var test=imageData.data[(y*width+x)*4];
		if (test>150){
			return true;
		}
		return false;
	}
	
	/**
	Setzt mit Dom die Margin neu
	@param char Zu versetzende Figur
	*/
	function positionSetzen(x,y,char){
		document.getElementById(char.name).style.marginTop=y+"px";
		document.getElementById(char.name).style.marginLeft=x+"px";
		char.x=x;
		char.y=y;
	}
	
	/**
	Geht in die Richtung des "Vektors" einen Zwischenschritt
	@param char Figur, die läuft
	*/
	function schritt(char){
		var x=char.y+char.richtung.y/(zwischenschritte);
		var y=char.x+char.richtung.x/(zwischenschritte);
		positionSetzen(y,x,char);
		//schrittzahl wird bei jedem Schritt runter gezählt (bis wieder auf 0 --> Ziel erreicht)
		char.schrittzahl--;
	}
	
	/**
	Löscht das Feld auf der Punkteebenen, das Pacman betreten hat und setzt neue Arraywerte
	Startet bei einer Kraftpille die Geisterjagd. 
	*/
	function dotFressen(){
		var punktWert=dots[pacman.y/pixelBreite*width+pacman.x/pixelBreite];
		if(punktWert!=0){
			//hier kann etwas gefressen werden
			++gesammelt;
			
			if (punktWert==2){
				//Kraftpille --> Geisterjagd wird aktiviert
				geist0Img.src = "bilder/geist_blau.png";
				geist1Img.src = "bilder/geist_blau.png";
				geist2Img.src = "bilder/geist_blau.png";
				geist3Img.src = "bilder/geist_blau.png";
				geisterjagd=10;
				geistZeit=setInterval(function(){
					if(geisterjagd>0){
						geisterjagd--;
					}else{
						geist0Img.src = "bilder/geist0.png";
						geist1Img.src = "bilder/geist1.png";
						geist2Img.src = "bilder/geist2.png";
						geist3Img.src = "bilder/geist3.png";
						clearInterval(geistRun);
						geistRun=setInterval(runGeister, frequenz);
						clearInterval(geistZeit);
					}
				}, 1000);
				clearInterval(geistRun);
				geistRun=setInterval(runGeister, 90);
				score=score+40;
			}
			//Punkt wird gefressen
			dots[pacman.y/pixelBreite*width+pacman.x/pixelBreite]=0;
			dotsC.clearRect(pacman.x, pacman.y, pixelBreite, pixelBreite);
			score = score+10;
		}
		//Inhalt der Infobox
		document.getElementById("oben").innerHTML="Score: "+score+"<br><br>";
	}

	/** 
	Setzt Bilder in der Lebensanzeige entsprechend Pacmans verbleibenden Leben
	*/
	function lebensanzeige(){
		if(leben==3){
			document.getElementById("pl1").src="bilder/leben.png";
			document.getElementById("pl2").src="bilder/leben.png";
			document.getElementById("pl3").src="bilder/leben.png";
		} else if(leben==2){
			document.getElementById("pl1").src="bilder/leben.png";
			document.getElementById("pl2").src="bilder/leben.png";
			document.getElementById("pl3").src="bilder/schwarz.png";
		} else if(leben==1){
			document.getElementById("pl1").src="bilder/leben.png";
			document.getElementById("pl2").src="bilder/schwarz.png";
			document.getElementById("pl3").src="bilder/schwarz.png";
		} else {
			document.getElementById("pl1").src="bilder/schwarz.png";
			document.getElementById("pl2").src="bilder/schwarz.png";
			document.getElementById("pl3").src="bilder/schwarz.png";
		}
		
	}
	
	/**
	Zufallsstrategie
	@param char Der jeweilige Geist
	*/
	function strategie0(char){
		//guckt zufällig, wo ein Weg frei ist
		for(var j=0;j<5;j++){
			var i= Math.floor(Math.random()*(3));
			if (i==0){
				//probiert links
				char.wegFrei=istWegFrei((char.x+char.richtung.links.x)/pixelBreite, (char.y+char.richtung.links.y)/pixelBreite, char);
				if (char.wegFrei){
					char.richtung=char.richtung.links;
					return;
				}
				i++;
			}
			if (i==1){
				//probiert rechts
				char.wegFrei=istWegFrei((char.x+char.richtung.rechts.x)/pixelBreite, (char.y+char.richtung.rechts.y)/pixelBreite, char);
				if (char.wegFrei){
					char.richtung=char.richtung.rechts;
					return;
				}
				i++;
			}
			if (i==2){
				//probiert geradeaus
				char.wegFrei=istWegFrei((char.x+char.richtung.x)/pixelBreite, (char.y+char.richtung.y)/pixelBreite, char);
				if (char.wegFrei){
					return;
				}
			}
		}
		//läuft bei Sackgasse rückwärts
		char.richtung=char.richtung.back;
		char.wegFrei=true;
	}
	
	/**
	Verfolgungsstrategie
	@param char Der jeweilige Geist
	*/
	function strategie1(char){
		if (geisterjagd>0){
			//hat bei Geisterjagd die Zufallsstrategie
			strategie0(char);
			return;
		}
		var schrittzahl=maxSuche;
		var obergrenze=schrittzahl;
		//rundet Pacmans Position
		var zielX=Math.round(pacman.x/pixelBreite)*pixelBreite;
		var zielY=Math.round(pacman.y/pixelBreite)*pixelBreite;
		var directions=besteRichtung(char.x,char.y,char.richtung,zielX,zielY);
		var rueckgabe=directions[0];
		
		//bei einer Kreuzung sucht er nach dem kürzesten Weg
		if (directions.length>1){
			for (var i=0;i<directions.length;i++){
				var weg=kuerzesterWeg(char.x+directions[i].x, char.y+directions[i].y, directions[i], obergrenze, zielX,zielY);
				if(weg<schrittzahl){
					schrittzahl=weg;
					rueckgabe=directions[i];
					obergrenze=schrittzahl;
				}
			}
		}
		//setzt die gefundene Richtung
		char.richtung=rueckgabe;
		char.wegFrei=true;

	}
	
	/**
	Sucht rekursiv den kürzesten Weg zu einem Ziel
	@param x X-Position, von der aus gesucht wird
	@param y Y-Position, von der aus gesucht wird
	@param richtung des Suchenden
	@param zielX X-Position des Ziels
	@param zielY Y-Position des Ziels
	*/
	function kuerzesterWeg(x,y,richtung,max,zielX,zielY){
		var obergrenze=max;
		
		if (x==zielX && y==zielY){
			//Abbruchbedingung 1 --> Ziel gefunden
			return 1;
		}
		
		if (obergrenze<=0){
			//Abbruchbedingung 2 --> Maximale Schrittzahl
			return maxSuche;
		}
		
		var directions=besteRichtung(x,y,richtung,zielX,zielY);
		var schrittzahl=obergrenze;
		
		//Sucht nach kurzen Wegen in allen möglichen Richtungen
		for (var i=0;i<directions.length;i++){
			var weg=kuerzesterWeg(x+directions[i].x,y+directions[i].y,directions[i],obergrenze-1,zielX,zielY);
			if(weg<schrittzahl){
				schrittzahl=weg;
				obergrenze=schrittzahl;
			}
		}
		return schrittzahl+1;
	}
	
	/**
	Schreibt mögliche Richtungen in ein Array.
	@param x X-Position, von der aus gesucht wird
	@param y Y-Position, von der aus gesucht wird
	@param richtung des Suchenden
	@param zielX X-Position des Ziels
	@param zielY Y-Position des Ziels
	@return das Array mit den Richtungen
	*/
	function moeglicheRichtungen(x,y,richtung,zielX, zielY){
		
		var directions=new Array();
		var i=0;
		
		if (istWegWeiss((x+richtung.x)/pixelBreite, (y+richtung.y)/pixelBreite)){
			//geradeaus
			directions[i]=richtung;
			i++;
		}
		if (istWegWeiss((x+richtung.links.x)/pixelBreite, (y+richtung.links.y)/pixelBreite)){
			//links
			directions[i]=richtung.links;
			i++;
		}
		if (istWegWeiss((x+richtung.rechts.x)/pixelBreite, (y+richtung.rechts.y)/pixelBreite)){
			//rechts
			directions[i]=richtung.rechts;
			i++;
		}
		if (i==0){
			//Sackgassse
			directions[i]=richtung.back;
		}
		return directions;		
	}
	
	/**
	Schreibt Richtungen in der besten Reihenfolge in ein Array.
	@param x X-Position, von der aus gesucht wird
	@param y Y-Position, von der aus gesucht wird
	@param richtung des Suchenden
	@param zielX X-Position des Ziels
	@param zielY Y-Position des Ziels
	@return das Array mit den Richtungen
	*/
	function besteRichtung(x,y,richtung,zielX, zielY){
		//diese Funktion kommt erst ab Level 5 zum Einsatz
		if (levelZahl<=4){
			return moeglicheRichtungen(x,y,richtung,zielX, zielY);
		}
		var directions=new Array();
		var i=0;
		var eins;
		var zwei;
		var drei;
		var vier;
		
		//Legt die günstigste Reihenfolge fest
		if (zielX>x){
			eins=richtungen["rechts"];
		}else{
			eins=richtungen["links"];
		}
		if (zielY>y){
			zwei=richtungen["unten"];
		}else{
			zwei=richtungen["oben"];
		}
		//diese beiden Richtungen führen vom Ziel weg und werden daher als letztes in das Array gelegt
		drei=eins.back;
		vier=zwei.back;
		
		//Schreibt die günstigen Richtungen nur in das Array, wenn der Weg möglich ist und die Richtung nicht die "back"-Richtung von "rchtung" ist
		if (eins!=richtung.back && istWegWeiss((x+eins.x)/pixelBreite, (y+eins.y)/pixelBreite)){
			directions[i]=eins;
			i++;
		}
		if (zwei!=richtung.back && istWegWeiss((x+zwei.x)/pixelBreite, (y+zwei.y)/pixelBreite)){
			directions[i]=zwei;
			i++;
		}
		if (drei!=richtung.back && istWegWeiss((x+drei.x)/pixelBreite, (y+drei.y)/pixelBreite)){
			directions[i]=drei;
			i++;
		}
		if (vier!=richtung.back && istWegWeiss((x+vier.x)/pixelBreite, (y+vier.y)/pixelBreite)){
			directions[i]=vier;
			i++;
		}
		if (i==0){
			//Sackgasse
			directions[i]=richtung.back;
		}
		return directions;	
		
	}
	
	/**
	Routine von Pacman (wird durch Timer aktiviert)
	*/
	function runPacman(){
		//Überprüft nur, wenn er auf einem Zielpunkt steht
		if (pacman.schrittzahl==0){
			wegFinden(pacman);
			dotFressen();
			if (gesammelt>=gewonnen){
				nextLevel();
			}
		}
		//Geht sonst einen Zwischenschritt (wenn der Weg frei ist)
		if (pacman.wegFrei){
			schritt(pacman);
		}
	}
	
	/**
	Spiel ist verloren
	*/
	function verloren(){
		//weiterleiten zum Score
		spielSpeichern();		
		window.location.href = "page3.php";
	}
	
	/**
	Setzt die Figuren zurück auf ihre Home-Positionen und setzt neue Startwerte
	*/
	function home(){
		for (var i=0; i<geisterzahl; i++){
			geister[i].schrittzahl=0;
			geister[i].startzeit=i*geistSleep+1;
			positionSetzen(geister[i].homeX,geister[i].homeY,geister[i]);
		}
		positionSetzen(pacman.homeX,pacman.homeY,pacman);
		pacman.schrittzahl=0;
		pacman.wegFrei=false;
		setTimeout (function(){first=true}, 800);
	}

	/**
	Regelt den Zusammenstoß von Geist und Pacman
	*/
	function collide(char){
		if (geisterjagd==0){
			//Pacman verliert ein Leben
			clearInterval(geistRun);
			clearInterval(pacRun);
			leben--;
			lebensanzeige();
			if (leben==0){
				verloren();
			}else {
				home();
			}
		}else{
			char.schrittzahl=0;
			char.startzeit=100;
			positionSetzen(char.homeX,char.homeY,char);
			score=score+500;
		}
	}
	
	/**
	Routine der Geister
	*/
	function runGeister(){
		for(var i=0; i<geisterzahl; i++){
			//Geist muss gestartet sein
			if (geister[i].startzeit==0){
				if (geister[i].schrittzahl==0){
					geister[i].schrittzahl=zwischenschritte;
					geister[i].strategie(geister[i]);
				}
				//Geister laufen immer, da wegFrei nicht false wird (eine mögliche Richtung wird in der Strategie immer gefunden)
				if (geister[i].wegFrei){
					schritt(geister[i]);
				}
				//Grenzen zum Abstand zu Pacman
				if (geister[i].x-pacman.x<=0.25*pixelBreite && geister[i].x-pacman.x>=0.25*pixelBreite*-1 && geister[i].y-pacman.y<=0.25*pixelBreite && geister[i].y-pacman.y>=0.25*pixelBreite*-1){
					collide(geister[i]);
				}
			}else{
				geister[i].startzeit--;
			}
		}
	}
	
	/**
	Funktion zum Lesen des Namens
	**/
	function getName(cname){
		return sessionStorage.getItem(cname);
	}

</script>
</head>
<body onload="spiel();" bgcolor="000000">
<canvas id="mycanvas" width="0" height="0" style="position: absolute;z-index:-5;display:none;"></canvas>
<div id="Center" style="text-align: center;">
	<div id="drumrum" style="text-align:left; display: inline-block;"> 
		<div id="level" style="background-color: black; width: 0px; height: 0px; overflow: hidden; float: left; position:block;">
			<canvas id="dots" width="0" height="0" style="margin-top: 0px; margin-left: 0px; position: absolute;z-index:2;" ></canvas>
			<canvas id="markierung" width="0" height="0" style="margin-top: 0px; margin-left: 0px; position: absolute;z-index:2;" ></canvas>
			<canvas id="game" width="0" height="0" style="position: absolute;margin-top: 0px; margin-left: 0px;z-index:1;"></canvas> 
			<div style="position: relative;">
				<canvas id="geist0" width="0" height="0" style="margin-top:0px; margin-left: 0px; position: absolute;z-index:3;" ></canvas>
				<canvas id="geist1" width="0" height="0" style="margin-top:0px; margin-left: 0px; position: absolute;z-index:3;" ></canvas>
				<canvas id="geist2" width="0" height="0" style="margin-top:0px; margin-left: 0px; position: absolute;z-index:3;" ></canvas>
				<canvas id="geist3" width="0" height="0" style="margin-top:0px; margin-left: 0px; position: absolute;z-index:3;" ></canvas>
			</div>
			<div id="pacman" style="margin-top: 0px; margin-left: 0px; width:0; height:0; position: relative;z-index:3;"><img id="pacimg" style="width:0; height:0;" src="bilder/pacman_8.png"></div> 

		</div>
		<div id="infobox" style="float: left; width: 200px; height: 850px; position: block; background-color:black;">
			<br>
			<div id="oben" style="text-align: center;;"></div>
			<div id="mitte" style="margin: 0 auto; width: 100%; height:50%">
				<img id="pl1" src="bilder/schwarz.png"><br>
				<img id="pl2" src="bilder/schwarz.png"><br>
				<img id="pl3" src="bilder/schwarz.png">
			
			<br><br>
			<div style="margin-left:-50%;" ><a href="page1.php">Hauptmenu</a></div>
			</div>
		</div>
	</div>
</div>
<br>
<script type="text/javascript">
</script>
</body>
</html>