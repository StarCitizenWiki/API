<?php
/**
 * @Author: Hannes
 * @Date:   2014-12-17 15:56:38
 * @Last Modified by:   Hannes
 * @Last Modified time: 2015-04-13 20:37:48
 */
define( 'basedir', '/var/www/vhtdocs/star-citizen.wiki/scripts' );
require_once basedir . '/inc/classes/AltoRouter.class.php';
$routerPath = '';
$Router = new AltoRouter();
$Router->setBasePath( $routerPath );
$Router->map( 'GET', '/', basedir.'/list.php', 'Startseite' );
$Router->map( 'GET', '/fundImage/black', basedir . '/scripts/fundImage.php', 'Spendenstatus Bild auf star-citizen.wiki <span class="label label-default pull-right">schwarze Version</span>' );
$Router->map( 'GET', '/fundImage/black/int', basedir . '/scripts/fundImage.php', 'Spendenstatus Bild <span class="label label-default pull-right">schwarze Version / nur Zahl</span>' );
$Router->map( 'GET', '/fundImage', basedir . '/scripts/fundImage.php', 'Spendenstatus Bild <span class="label label-info pull-right">blaue Version</span>' );
$Router->map( 'GET', '/fundImage/int', basedir . '/scripts/fundImage.php', 'Spendenstatus Bild <span class="label label-info pull-right">blaue Version / nur Zahl</span>' );
$Router->map( 'GET', '/spendenStats', basedir . '/scripts/spendenStats.php', 'alte Version des Spendenstatus' );
$Router->map( 'GET', '/cleanBaseFeed', basedir . '/scripts/cleanBaseFeed.php', 'Aufbereitung des RSS Feeds von Star Citizen Base für das Star Citizen Wiki' );
$Router->map( 'GET', '/getNotificationUsers', basedir . '/scripts/getNotificationUsers.php', 'Liste aller Benachritigungsbenutzers des Wiki' );
$Router->map( 'GET|POST', '/StarmapData', basedir . '/scripts/starmapData.php', 'Starmap Informations Tool' );
$Router->map( 'GET', '/drawObject/[a:action]/[*:path]?', basedir . '/scripts/drawObject.php', 'Tool zur Darstellung von Planeten/Raumstationen <span class="label label-default pull-right">action: collada/planet | path: path to texture/file</span>' );
$Router->map( 'GET|POST', '/parseBookmarks', basedir . '/scripts/bookmarkParser.php', 'internes Bookmapparser-Tool' );
$Router->map( 'GET', '/KopfbildGen', basedir . '/scripts/kopfbild_tool.html', 'Star Citizen Wiki Kopfbild Assistent' );
$Router->map( 'GET', '/getShips', basedir . '/scripts/getShips.php', 'JSON Format der Shipmatrix' );
$Router->map( 'GET|POST', '/getShip/[*:id]', basedir . '/scripts/getShips.php', 'JSON Format eines Schiffes aus der Schipmatrix Beispiel: /getShip/300i' );

/* Match the current request */
$match = $Router->match();
if( $match ){
  require $match['target'];
}else{
  header("HTTP/1.0 404 Not Found");

  // header("Location: https://star-citizen.wiki/StarCitizen_Wiki");
}
?>
