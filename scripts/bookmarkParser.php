<?php

$regex = '/HREF="(https?\:\/\/\w*\.com\/\w*\-\w*\/\w*(\-\w*)?\/([0-9]{3,9}).*)"\WADD_DATE="([0-9]{10})".*">(.*)<\/A>/';

if(isset($_POST['search'])){
    if(isset($_POST['bookmarks']) && !empty($_POST['bookmarks'])){
        preg_match_all($regex, $_POST['bookmarks'], $matches);

        echo '<table><tr><th>URL</th><th>ID</th><th>Date</th><th>Name</th></tr>';
        foreach ($matches[1] as $key => $value) {
            echo '<tr><td>'.$value.'</td><td>'.$matches[3][$key].'</td><td>'.date("d.m.Y",$matches[4][$key]).'</td><td>'.$matches[5][$key].'</td></tr>';
        }
        echo '</table>';
    }

}
?>
<form class="" action="/parseBookmarks" method="post">
    <textarea name="bookmarks" rows="8" cols="40"></textarea>
    <input type="hidden" name="search" value="">
    <button type="submit" name="button">verarbeiten</button>
</form>
