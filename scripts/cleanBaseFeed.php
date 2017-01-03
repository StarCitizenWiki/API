<?php
/**
 * @Author: Hannes
 * @Date:   2015-04-05 15:03:23
 * @Last Modified by:   Hannes
 * @Last Modified time: 2015-04-13 20:53:05
 */
if( is_file( basedir . '/resources/output/starcitizenbase_rss.xml' ) && time() - ( 60 * 60 * 2 ) <= filemtime( basedir . '/resources/output/starcitizenbase_rss.xml' ) ){
  header('Content-Type: application/xml; charset=utf-8');
  echo file_get_contents( basedir . '/resources/output/starcitizenbase_rss.xml' );
}else{
	header('Content-Type: application/xml; charset=utf-8');
	require_once basedir . '/inc/classes/RSSFeed.class.php';
	$rss = new RSSFeed();
	$rss -> load('http://starcitizenbase.de/feed/');
	$xml = json_encode( $rss->getRSS() );
	$xml = json_decode( $xml, true );
	$buildDate = date( 'D, d M Y H:i:s ' ) . '+0000';
	$rssOutput = '<?xml version="1.0" encoding="utf-8"?>
	<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
			<title>Reparsed Starcitizenbase Blog</title>
			<description>Reparsed Version</description>
			<link>http://www.starcitizenbase.de/</link>
			<lastBuildDate>' . $buildDate . '</lastBuildDate>
			<generator>Star Citizen Wiki</generator>
			<atom:link rel="self" type="application/rss+xml" href="http://scripts.star-citizen.wiki/cleanBaseFeed"/>
			<language>de-de</language>
			<managingEditor>info@star-citizen.wiki (Star Citizen Wiki)</managingEditor>';
	for( $i=0;$i<5;$i++ ){
		$content = trim( str_replace( '&nbsp;', '', preg_replace('/\s\s+/', ' ', strip_tags( $xml['rss']['channel']['item:' . $i]['description'], '<br><ul><li>' ) ) ) );
		if( strlen( $content ) > 150 ){
			$content = substr( $content, 0, 100 ) . '...';
		}
		$rssOutput .= '
			<item>
				<title>' . htmlspecialchars( $xml['rss']['channel']['item:' . $i]['title'] ) . '</title>
				<link>' . $xml['rss']['channel']['item:' . $i]['link'] . '</link>
				<guid>' . $xml['rss']['channel']['item:' . $i]['link'] . '</guid>
				<pubDate>' . $xml['rss']['channel']['item:' . $i]['pubDate'] . '</pubDate>
				<description><![CDATA[' . htmlspecialchars($content) . ']]></description>
			</item>';
	}
	$rssOutput .= '
		</channel>
	</rss>';
  $rssFile = fopen( basedir . '/resources/output/starcitizenbase_rss.xml', 'w+' );
  fwrite( $rssFile, $rssOutput );
  fclose( $rssFile );
  echo $rssOutput;
}
?>
