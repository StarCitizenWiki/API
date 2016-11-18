<?php
if(isset($_GET["copy"])){
  if($_GET["copy"] == 1){$copy = 1;}
}else{
  $copy = 0;
}
if(isset($_GET["alphaslots"])){
  if($_GET["alphaslots"] == 1){$alpha = 1;}
}else{
  $alpha = 0;
}
if(isset($_GET["tage"])){
  if($_GET["tage"] == 1){$tage = 1;}
}else{
  $tage = 0;
}

if(isset($_GET["days"])){
  if($_GET["days"] == 1){$tage = 1;}
}

if(isset($_GET["balken"])){
  if($_GET["balken"] == 1){$balken = 1;}
}else{
  $balken = 0;
}

if(isset($_GET["spenden"])){
  if($_GET["spenden"] == 1){$spenden = 1;}
}else{
  $spenden = 0;
}

if(isset($_GET["text"])){
  if($_GET["text"] != null){$text = $_GET["text"];}
}else{
  $text = 0;
}

if(isset($_GET["lang"])){
  if($_GET["lang"] == "en"){$lang = "en";}
}else{
  $lang = "de";
}

if($spenden == 0 && $alpha == 0 && $tage == 0 && $balken == 0)
  {
    $alpha = 0;
    $balken = 1;
    $tage = 1;
    $spenden = 1;
  }
#Url der Api
$Url = "https://robertsspaceindustries.com/api/stats/getCrowdfundStats";

#Arry mit Post Informationen
$datatopost = array (
"chart" => "hour",
"alpha_slots" => true,
"funds" =>  true,

);

#Initialisierung von cUrl
$ch = curl_init();

#cUrl die Url übergeben
curl_setopt($ch, CURLOPT_URL, $Url);

#Bei der Ausgabe keine Headerinforamtionen mit senden
curl_setopt($ch, CURLOPT_HEADER, 0);

#Informationen werden per POST übergeben
curl_setopt($ch, CURLOPT_POST, 1);

#cUrl die benötigten POST Infos geben
curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);

#cUrl soll die Daten als return übergeben und nicht ausgeben (fals = ausgeben)
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

#RSI benutzt ein SSL-Zertifikat, cUrl soll dieses NICHT überprüfen
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

#Timeout von cUrl
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

#return von cUrl wird in $output gespeichert, $ch wird von cUrl ausgeführt
$output = curl_exec($ch);

#cUrl schließen
curl_close($ch);

#Informationen von cUrl kommen im json Format an, PHP dekodiert uns diese und speichert sie im Array $test
$test = json_decode($output, true);

#RSI sendet mit, ob die Abfrage geklappt hat, wenn ja:
if($test["success"] == 1)
  {
    #Aktuelle Spenden in $ziel_num speichern
    $ziel_num = $test["data"]["funds"];
    if( empty( $test["data"]["next_goal"]["goal"] ) ){
      $test["data"]["next_goal"]["goal"] = 'REDACTED';
      $test["data"]["next_goal"]["percentage"] = 0;
    }
    // $test["data"]["next_goal"]["goal"] = round(substr($ziel_num,0,-2), -6) ;
    // $test["data"]["next_goal"]["percentage"] = round( ( ( round(substr($ziel_num,0,-2), -6) - substr($ziel_num,0,-2) ) / 1000000 ) * 100 , 2 );
    // $test["data"]["next_goal"]["goal"] = number_format($test["data"]["next_goal"]["goal"], 0, '', ',');

    #RSI sendet Cent Beträge mit, die wir durch substr entfernen, außerdem wandeln wir mit number_format die Spenden in das amerikanische Format
    $ziel = number_format(substr($ziel_num,0,-2), 0, '', ',');
    #Wir erstellen das Bild mit den Größen abhängig von den Parametern
    if($balken == 1 && $alpha == 0 && $tage == 0 && $spenden == 0)
      {
        $my_img = imagecreate( 303, 23 );
      }
    elseif($text != 0)
      {
        $my_img = imagecreate( 325, 90 );
      }
    else
      {
        $my_img = imagecreate( 325, 75 );
      }
    #Hintergrundfarbe des Bildes (Angepasst an das Wiki
    #$background = imagecolorallocate( $my_img, 0, 10, 20 );
    $background = imagecolorallocatealpha( $my_img, 0, 0, 0,254 );
    #Test Farbe
    $text_colour = imagecolorallocate( $my_img, 106, 187, 207 );
    #Farbe der Balken, welche noch nicht gefüllt sind
    $text_colour2 = imagecolorallocate( $my_img, 69, 117, 129 );

    if($spenden == 1)
      {
        if($lang == "de")
          {
            #Erste Text-Zeile schreiben
            imagestring( $my_img, 2, 0, 0, 'Aktuelle Spenden: $'.$ziel.' von '.$test["data"]["next_goal"]["goal"].' ('.$test["data"]["next_goal"]["percentage"].'%)',$text_colour);
          }
        else
          {
            imagestring( $my_img, 2, 0, 0, 'Pledges: $'.$ziel.' out of '.$test["data"]["next_goal"]["goal"].' ('.$test["data"]["next_goal"]["percentage"].'%)',$text_colour);
          }
      }

    if($alpha == 1)
      {
        if($alpha == 1 && $balken == 1 && $tage == 1 && $spenden == 1)
          {
            $x_alpha = 45;
          }
        elseif($alpha == 1 && $balken == 1 && $tage == 0 && $spenden == 1)
          {
            $x_alpha = 45;
          }
        elseif($alpha == 1 && $balken == 1 && $tage == 0 && $spenden == 1)
          {
            $x_alpha = 45;
          }
        elseif($alpha == 1 && $balken == 0 && $tage == 0 && $spenden == 1)
          {
            $x_alpha = 15;
          }
        elseif($alpha == 1 && $balken == 0 && $tage == 1 && $spenden == 1)
          {
            $x_alpha = 15;
          }
        elseif($alpha == 1 && $balken == 1 && $tage == 0 && $spenden == 0)
          {
            $x_alpha = 30;
          }
        elseif($alpha == 1 && $balken == 1 && $tage == 1 && $spenden == 0)
          {
            $x_alpha = 30;
          }
        elseif($alpha == 1 && $spenden == 0)
          {
            $x_alpha = 0;
          }
        else
          {
            $x_alpha = 45;
          }
        if($lang == "de")
          {
            if($test["data"]["alpha_slots_left"] < 100){
              #Zweite Text-Zeile schreiben
              imagestring( $my_img, 2, 0, $x_alpha, 'Keine Alphaslots mehr vorhanden!',$text_colour);
            }else{
              #Zweite Text-Zeile schreiben
              imagestring( $my_img, 2, 0, $x_alpha, 'Es sind noch '.$test["data"]["alpha_slots_left"].' Alphaslots frei.',$text_colour);
            }
          }
        else
          {
            #Zweite Text-Zeile schreiben
            imagestring( $my_img, 2, 0, $x_alpha, $test["data"]["alpha_slots_left"].' Alphaslots left.',$text_colour);
          }
      }
    if($balken == 1)
      {
        if($balken == 1 && $spenden == 0)
          {
            $hoehe_unten = 25;
            $hoehe_oben = 0;
          }
        else
          {
            $hoehe_unten = 40;
            $hoehe_oben = 15;
          }

        #Aktuelle Prozentzahl der Spenden in $ziel_funds speichern
        $ziel_funds = $test["data"]["next_goal"]["percentage"];
        #Prozentzahl mal 3 nehmen (Wird für die Länge des Balkens benötigt
        $ziel_funds = $ziel_funds * 3;

        #Schleife die den Spendenbalken erstellt
        for($i=0;$i <=300;$i = $i + 5)
          {
            #Hier werden die gefüllten Balken dargestellt
            if($ziel_funds >= $i)
              {
                #Die Balken bestehen aus 3 Vertikalen Linien (3px dick) deshalb 3 Linien nebeneinander
                imageline ($my_img , $i , $hoehe_oben , $i , $hoehe_unten , $text_colour );
                imageline ($my_img , $i+1 , $hoehe_oben , $i+1 , $hoehe_unten , $text_colour );
                imageline ($my_img , $i+2 , $hoehe_oben , $i+2 , $hoehe_unten , $text_colour );

              }
            #Ist der Balken nicht gefüllt wird dieser mit einer anderen farbe dargestellt
            else
              {
                imageline ($my_img , $i , $hoehe_oben , $i , $hoehe_unten , $text_colour2 );
                imageline ($my_img , $i+1 , $hoehe_oben , $i+1 , $hoehe_unten , $text_colour2 );
                imageline ($my_img , $i+2 , $hoehe_oben , $i+2 , $hoehe_unten , $text_colour2 );
              }
          }
        #Ende der Obersten 3 Zeilen (Erste Textzeile, Spendenbalken, Alphaslots)
      }

if($tage == 1)
  {
    if($tage == 1 && $balken == 1 && $alpha == 1 && $spenden == 1)
      {
        $x_tage = 60;
      }
    if($tage == 1 && $spenden == 0 && $balken == 0 && $alpha == 0)
      {
        $x_tage = 0;
      }
    elseif($tage == 1 && $balken == 1 && $alpha == 0 && $spenden == 0)
      {
        $x_tage = 25;
      }
    elseif($tage == 1 && $balken == 0 && $alpha == 1 && $spenden == 0)
      {
        $x_tage = 15;
      }
    elseif($tage == 1 && $balken == 0 && $alpha == 0 && $spenden == 1)
      {
        $x_tage = 15;
      }
    elseif($tage == 1 && $balken == 1 && $alpha == 0 && $spenden == 1)
      {
        $x_tage = 45;
      }
    elseif($tage == 1 && $balken == 0 && $alpha == 1 && $spenden == 1)
      {
        $x_tage = 30;
      }
      elseif($alpha == 1 && $balken == 1 && $tage == 1 && $spenden == 0)
        {
          $x_tage = 45;
        }
    else
      {
        $x_tage = 60;
      }
    if($lang == "de")
      {
        #Textzeile schreiben, wie lange es noch ca. dauert
        #imagestring( $my_img, 2, 0, $x_tage, 'Noch ca. '.$zaehler.' Tage, bis zur 21 Millionen-Marke.',$text_colour);
        #imagestring( $my_img, 2, 0, $x_tage, '21 Millionen $-Marke erreicht!',$text_colour);
      }
    else
      {
        #Textzeile schreiben, wie lange es noch ca. dauert
        #imagestring( $my_img, 2, 0, $x_tage, 'circa '.$zaehler.' Days, until we reach 21 millions.',$text_colour);
        #imagestring( $my_img, 2, 0, $x_tage, 'Game is fully crowdfunded',$text_colour);
      }
  }
if($copy == 1)
  {
    #Copy <3 wiki & Fox
    imagestring( $my_img, 1,280, 65, 'by FoXFTW',$text_colour);
  }
if(isset($_GET["text"])){
  if($_GET["text"] != null && $spenden == 1 && $alpha == 1 && $balken == 1 && $tage == 1)
    {
      #Eigener Text
      imagestring( $my_img, 2,0, 75, $text,$text_colour);
    }
}
    #Start der Bilderstellung
    header( "Content-type: image/png" );
    #Bild als PNG schreiben
    imagepng( $my_img );
    #Textfarbe
    imagecolordeallocate($my_img, $text_colour );
    #Hintergrundfarbe
    imagecolordeallocate($my_img, $background  );
    #Bild wurde geschrieben, als $my_img frei geben
    imagedestroy( $my_img );
  }

#Liefert die Abfrage bei RSI success = 0 zurück ist ein Fehler aufgetreten
else
  {
    #Wir erstellen das Bild mit 325px breite und 70px höhe
    $my_img = imagecreate( 350, 70 );
    #siehe oben
    $background = imagecolorallocate( $my_img, 0, 10, 20 );
    $text_colour = imagecolorallocate( $my_img, 106, 187, 207 );
    #Textzeile schreiben
    imagestring( $my_img, 5, 40, 28, 'Ein Fehler ist aufgetreten.',$text_colour);
    #imagestring( $my_img, 5, 40, 28, '21 Millionen Dollar Ziel erreicht!',$text_colour);
    #siehe oben
    header( "Content-type: image/png" );
    imagepng( $my_img );
    imagecolordeallocate( $text_color );
    imagecolordeallocate( $background );
    imagedestroy( $my_img );
  }
?>
