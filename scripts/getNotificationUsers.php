<?php
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://v3.star-citizen.wiki/api.php?action=query&list=allusers&augroup=Notification&format=json'
));
$resp = curl_exec($curl);
curl_close($curl);
$resp = json_decode($resp, true);
$resp = $resp['query']['allusers'];
$userarray = array();
foreach ($resp as $key => $user) {
    $userarray[] = "'".$user['name']."'";
}

echo '$wgUsersNotifiedOnAllChanges = array('.implode(',', $userarray).');';
