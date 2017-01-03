<?php
class WikiBot{

  public function getWikiPage ($page){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://star-citizen.wiki/api.php?format=json&action=query&titles='.$page.'&prop=revisions&rvprop=timestamp|user|comment|content');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    $wiki_decode = json_decode($output, true);
    return $wiki_decode;
  }

  public function getCategory ($category){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://star-citizen.wiki/api.php?action=query&format=json&cmlimit=100&list=categorymembers&cmtitle=Kategorie:'.$category);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    $wiki_decode = json_decode($output, true);
    return $wiki_decode;
  }

  public function doLogin ($username,$password){
    $datatopost = array (
    "lgname" => $username,
    "lgpassword" => $password
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://star-citizen.wiki/api.php?action=login&format=json');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output_pre = curl_exec($ch);
    $output_pre = json_decode($output_pre, true);
    setcookie("{$output_pre["login"]["cookieprefix"]}UserName", $username, time()+3600*24*60, "/");
    setcookie("{$output_pre["login"]["cookieprefix"]}_session",  $output_pre["login"]["sessionid"], time()+3600*24*60, "/");
    #zweiter Aufruf
    $datatopost = array (
      "lgname" => $username,
      "lgpassword" => $password,
      "lgtoken" => $output_pre["login"]["token"]
    );
    $sess = $output_pre["login"]["sessionid"];
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, "{$output_pre["login"]["cookieprefix"]}_session=$sess");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $output = json_decode($output, true);
    curl_close($ch);
    return $output;
  }

  public function getEditToken(){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://star-citizen.wiki/api.php?action=query&prop=info|revisions&intoken=edit&titles=Benutzer%3AWiki_B0t&format=json');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $output = json_decode($output, true);
    curl_close($ch);
    return $output;
  }

  public function doLogout (){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://star-citizen.wiki/api.php?action=logout&format=json');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $output = json_decode($output, true);
  }

  public function replaceWord ($source,$word,$rpl_with){
    $replaced = str_replace($word, $rpl_with, $source);
    return $replaced;
  }

  public static function utf8Decode($dat){
    if (is_string($dat)) {
      return utf8_decode($dat);
    }
    if (is_object($dat)) {
      $ovs= get_object_vars($dat);
      $new=$dat;
      foreach ($ovs as $k =>$v)    {
          $new->$k=WikiBot::utf8Decode($new->$k);
      }
      return $new;
    }

    if (!is_array($dat)) return $dat;
    $ret = array();
    foreach($dat as $i=>$d) $ret[$i] = WikiBot::utf8Decode($d);

    return $ret;
  }

}
?>