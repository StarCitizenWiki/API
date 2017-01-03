<?php
$wikiurl = 'https://v3.star-citizen.wiki/';
$apiurl = 'https://robertsspaceindustries.com/api/starmap/';

$starmapurl = 'https://robertsspaceindustries.com/starmap';
$getTempalte = '?location=%s&system=%s';

$systems = scandir('/var/www/vhtdocs/star-citizen.wiki/scripts/resources/data');
$systemTemplate = '{{Sternensystem
|Kurzbeschreibung=%s
|Systemgröße=%s
|System_Alter=%s
|Sterne=%s
|Sterntyp=%s
|Sterngröße=%s
|Kontrolle=%s
|Bevölkerungsgröße=%s
|Wirtschaft=%s
|Gefahrenlage=%s
|Landezonen=%s
|Raumstationen=%s
|Städte=%s
|Sonstige_Daten=%s}}
{{Quellen}}';
unset($systems[0]);
unset($systems[1]);

if(isset($_POST['refetch']) && $_POST['refetch'] == 1){
    $starmap = array(
        'Ayr\'ka' => 5,
        'Bacchus' => 3,
        'Baker' => 4,
        'Banshee' => 4,
        'Branaugh' => 3,
        'Bremen' => 4,
        'Caliban' => 5,
        'Cano' => 4,
        'Castra' => 2,
        'Cathcart' => 0,
        'Centauri' => 5,
        'Charon' => 5,
        'Chronos' => 3,
        'Corel' => 6,
        'Croshaw' => 4,
        'Davien' => 4,
        'Eealus' => 6,
        'Ellis' => 12,
        'El\'sin' => 0,
        'Elysium' => 5,
        'Ferron' => 4,
        'Fora' => 5,
        'Garron' => 4,
        'Geddon' => 1,
        'Genesis' => 3,
        'Gliese' => 6,
        'Goss' => 3,
        'Gurzil' => 0,
        'Hades' => 4,
        'Hadrian' => 3,
        'Hadur' => 4,
        'Helios' => 4,
        'Horus' => 3,
        'Idris' => 5,
        'Indra' => 2,
        'Kabal' => 3,
        'Kallis' => 9,
        'Kayfa' => 4,
        'Kellog' => 6,
        'Khabari' => 0,
        'Kiel' => 6,
        'Kilian' => 14,
        'Kins' => 5,
        'Leir' => 3,
        'Magnus' => 3,
        'Markahil' => 0,
        'Min' => 1,
        'Nemo' => 3,
        'Nexus' => 5,
        'Nul' => 5,
        'Nyx' => 3,
        'Oberon' => 7,
        'Odin' => 3,
        'Oretani' => 6,
        'Orion' => 4,
        'Osiris' => 2,
        'Oso' => 6,
        'Oya' => 4,
        'Pallas' => 5,
        'Pyro' => 6,
        'Rhetor' => 5,
        'Rihlah' => 6,
        'Sol' => 9,
        'Stanton' => 4,
        'Tal' => 7,
        'Tamsa' => 2,
        'Tanga' => 3,
        'Taranis' => 4,
        'Tayac' => 3,
        'Terra' => 4,
        'Tiber' => 2,
        'Tohil' => 4,
        'Trise' => 1,
        'Tyrol' => 7,
        'Vagabond' => 0,
        'Vanguard' => 0,
        'Vector' => 0,
        'Vega' => 4,
        'Vendetta' => 0,
        'Veritas' => 0,
        'Vermilion' => 0,
        'Vesper' => 0,
        'Viking' => 0,
        'Virgil' => 3,
        'Virgo' => 0,
        'Virtus' => 4,
        'Volt' => 0,
        'Voodoo' => 0,
        'Vulture' => 0,
        'Yulin' => 6,
        'Krell' => 0,
        'Ophos' => 0,
        'UDS-2943-01-22' => 0,
    );

    $missing = '';
    $loaded = '';
    foreach ($starmap as $key => $value) {
        $systemkey = strtoupper($key);
        $url = $apiurl.'star-systems/'.$systemkey;

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $decode = json_decode($response,true);
        if($decode['data']['rowcount'] == 0){
            $missing .= 'System '.$key.' existiert nicht<br>';
        }else{
            $loaded .= 'System '.$key.' geladen<br>';
            $systemdata = fopen("/var/www/vhtdocs/star-citizen.wiki/scripts/resources/data/".$systemkey.".json", "w");
            fwrite($systemdata, $response);
            fclose($systemdata);
        }
    }
}

if(isset($_POST['system']) && is_numeric($_POST['system'])){
    $system = $_POST['system'];
}else{
    $system = 0;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Starmap Data Parser</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" charset="utf-8">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2-rc.1/css/select2.min.css" rel="stylesheet" />
        <link rel="shortcut icon" href="https://robertsspaceindustries.com/rsi/static/js/starmap/sourceimages/factions/UEE.png" type="image/png" />
        <link rel="icon" href="https://robertsspaceindustries.com/rsi/static/js/starmap/sourceimages/factions/UEE.png" type="image/png" />

        <style media="screen">
            body{
                overflow-y: scroll;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow,
            .select2-container .select2-selection--single{
                height: 34px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered{
                line-height: 34px;
            }

            .select2.select2-container.select2-container--default{
                width: 100% !important;
            }

            .sidebar{
                height: 100vh;
                position: fixed;
                border-right: 1px solid #eee;
                <?php if(isset($_POST['refetch']) && $_POST['refetch'] == 1){ echo 'overflow-y: scroll;'; } ?>
            }

            .tooltip-inner{
                text-align: left;
            }

            .affiliationicon{
                max-width: 14px;
                margin-top: -2px;
                margin-right: 3px;
            }

            .affiliationlink:hover{
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
        	<div class="row">
        		<div class="col-md-2 sidebar">
                    <div class="col-md-12">
                        <h3>Sternensysteme</h3>
                        <form role="form" method="POST" action="/StarmapData">
                            <div class="form-group col-md-6" style="padding-left: 0;">
                                <select name="system" id="system">
                                    <?php
                                    foreach ($systems as $key => $value) {
                                        echo '<option value="'.$key.'" '.($system == $key?'selected="selected"':'').'>'.str_replace('.json', '', ucfirst(strtolower($value))).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-default col-md-6">Laden</button>
                        </form>
                    </div>

                    <div class="col-md-12">
                        <hr>
                        <button class="btn btn-info" onclick="jQuery('.collapse').collapse('toggle');">Alle ausklappen</button>
                        <hr>
                        <h3>Wartung</h3>
                        <form role="form" method="POST" action="/StarmapData">
                            <button type="submit" class="btn btn-default" value="1" name="refetch">Sternensysteme neu laden</button>
                        </form>
                        <?php
                        if (file_exists('/var/www/vhtdocs/star-citizen.wiki/scripts/resources/data/BAKER.json')) {
                            echo '<p style="margin-top: 10px;">Letzte Aktualisierung: '.date ("H:i d.m.Y", filemtime('/var/www/vhtdocs/star-citizen.wiki/scripts/resources/data/BAKER.json')).'</p>';
                        }
                        ?>
                    </div>
                    <?php
                    if(isset($loaded) && $loaded != ''){
                    ?>
                    <div class="col-md-12">
                        <hr>
                        <h3>Wartungslog</h3>
                        <?php if($missing != ''){ ?>
                        <div class="alert alert-warning">
                            <?php echo $missing; ?>
                        </div>
                        <?php } ?>
                        <div class="alert alert-success">
                            <?php echo $loaded; ?>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
        		</div>
        		<div class="col-md-10 col-md-offset-2" style="padding-bottom: 20px;">
                    <?php
                    if($system > 0){
                        $data = json_decode(file_get_contents('/var/www/vhtdocs/star-citizen.wiki/scripts/resources/data/'.$systems[$_POST['system']]), true)['data']['resultset'];
                        $asteroidbelts = '';
                        $jumppoints = '';
                        $planets    = '';
                        $stars      = '';
                        $sattelites = '';
                        $manmade    = '';
                        $jumppointarray    = array();
                        $planetarray       = array();
                        $stararray         = array();
                        $sattelitearray    = array();
                        $asteroidbeltarray = array();
                        $manmadearray      = array();

                        foreach ($data[0]['celestial_objects'] as $object => $objectdata) {
                            switch ($objectdata['type']) {
                                case 'JUMPPOINT':
                                    $jumppointarray[$objectdata['id']] = $objectdata;
                                    $jumppoints .= $objectdata['designation'].'<br>';
                                    break;

                                case 'PLANET':
                                    $planetarray[$objectdata['id']] = $objectdata;
                                    $planets .= '<a href="'.$wikiurl.'Sternenkarte/'.$data[0]['name'].'/'.$objectdata['designation'].'" target="_blank">'.$objectdata['designation'].(!empty($objectdata['name'])?' - '.$objectdata['name']:'').'</a><br>';
                                    //  https://robertsspaceindustries.com/api/starmap/celestial-objects/STANTON.PLANETS.STANTONIICRUSADER
                                    break;

                                case 'STAR':
                                    $stararray[$objectdata['id']] = $objectdata;
                                    $stars .= $objectdata['designation'].'<br>';
                                    break;

                                case 'SATELLITE':
                                    $sattelitearray[$objectdata['id']] = $objectdata;
                                    if(array_key_exists($objectdata['parent_id'], $planetarray)){
                                        $planetarray[$objectdata['parent_id']]['moons'][] = $objectdata['id'];
                                    }
                                    if(array_key_exists($objectdata['parent_id'], $stararray)){
                                        $stararray[$objectdata['parent_id']]['moons'][] = $objectdata['id'];
                                    }
                                    $sattelites .= $objectdata['designation'].'<br>';
                                    break;

                                case 'ASTEROID_BELT':
                                    $asteroidbeltarray[$objectdata['id']] = $objectdata;
                                    if(array_key_exists($objectdata['parent_id'], $planetarray)){
                                        $planetarray[$objectdata['parent_id']]['belts'][] = $objectdata['id'];
                                    }
                                    if(array_key_exists($objectdata['parent_id'], $stararray)){
                                        $stararray[$objectdata['parent_id']]['belts'][] = $objectdata['id'];
                                    }
                                    $asteroidbelts .= $objectdata['designation'].'<br>';
                                    break;

                                case 'MANMADE':
                                    $manmadearray[$objectdata['id']] = $objectdata;
                                    if(array_key_exists($objectdata['parent_id'], $planetarray)){
                                        $planetarray[$objectdata['parent_id']]['manmade'][] = $objectdata['id'];
                                    }
                                    if(array_key_exists($objectdata['parent_id'], $stararray)){
                                        $stararray[$objectdata['parent_id']]['manmade'][] = $objectdata['id'];
                                    }
                                    $manmade .= $objectdata['designation'].'<br>';
                                    break;

                                default:
                                    break;
                            }
                        }
                        $system = $data[0];
?>
                        <h3>Schnellübersicht</h3>
                        <table class="table table-striped">
                            <tr>
                                <th>Sterne</th>
                                <th>Asteroidengürtel</th>
                                <th>Planeten</th>
                                <th>Raumstationen</th>
                                <th>Monde</th>
                                <th>Jumppoints</th>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; padding: 0 10px;"><?php echo $stars; ?></td>
                                <td style="vertical-align: top; padding: 0 10px;"><?php echo $asteroidbelts; ?></td>
                                <td style="vertical-align: top; padding: 0 10px;"><?php echo $planets; ?></td>
                                <td style="vertical-align: top; padding: 0 10px;"><?php echo $manmade; ?></td>
                                <td style="vertical-align: top; padding: 0 10px;"><?php echo $sattelites; ?></td>
                                <td style="vertical-align: top; padding: 0 10px;"><?php echo $jumppoints; ?></td>
                            </tr>
                        </table>



                        <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#systemContainer" aria-expanded="false" aria-controls="system">
                          Sternensystem
                        </button>

                        <div class="collapse in" id="systemContainer">
                        <h4 style="display: inline-block; padding-right: 5px;"><?php echo $system['name']; ?></h4>
                        <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $system['id']; ?><br/>Letzte Änderung:<br/> <?php echo timeger($system['time_modified']); ?>"></i>
                        <div class="row">
                            <div class="col-md-2">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Code:</th>
                                        <td><?php echo $system['code']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Typ:</th>
                                        <td><?php echo formattype($system['type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Position:</th>
                                        <td>
                                            X: <?php echo $system['position_x']; ?><br>
                                            Y: <?php echo $system['position_y']; ?><br>
                                            Z: <?php echo $system['position_z']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th rowspan="<?php echo count($system['affiliation']); ?>">Kontrolle:</th>
                                        <?php
                                        foreach ($system['affiliation'] as $key => $value) {
                                            echo '
                                                <td style="color:'.$value['color'].';">
                                                    <a class="affiliationlink" href="https://robertsspaceindustries.com/rsi/static/js/starmap/sourceimages/factions/'.$value['code'].'.png" target="_blank">
                                                    <img class="affiliationicon" src="https://robertsspaceindustries.com/rsi/static/js/starmap/sourceimages/factions/'.$value['code'].'.png" />
                                                    </a>
                                                    '.$value['name'].'
                                                </td>';
                                        }
                                        ?>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-2">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Größe:</th>
                                        <td><?php echo round($system['aggregated_size'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Bewohner:</th>
                                        <td><?php echo $system['aggregated_population']; ?> Mrd.</td>
                                    </tr>
                                    <tr>
                                        <th>Wirtschaft:</th>
                                        <td><?php echo $system['aggregated_economy']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Gefahr:</th>
                                        <td><?php echo $system['aggregated_danger']; ?></td>
                                    </tr>

                                </table>
                            </div>
                            <div class="col-md-2">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Frost Linie:</th>
                                        <td><?php echo round($system['frost_line'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Zone (inner):</th>
                                        <td><?php echo round($system['habitable_zone_inner'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Zone (outer):</th>
                                        <td><?php echo round($system['habitable_zone_outer'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Infourl:</th>
                                        <td><?php echo $system['info_url']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Beschreibung:</th>
                                        <td><?php echo $system['description']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Starmap URL:</th>
                                        <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $system['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $system['code'], $system['code']).'</a>'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <?php
                            if(!empty($system['thumbnail']['source'])){
                            ?>
                                <div class="col-md-2">
                                    <table class="table table-striped">
                                        <tr>
                                            <td>
                                                <a href="<?php echo $system['thumbnail']['source']; ?>" target="_blank">
                                                    <?php
                                                    if(!empty($system['thumbnail']['images'])){
                                                    ?>
                                                    <img class="img-responsive" src="<?php echo $system['thumbnail']['images']['product_thumb_large']; ?>" alt="product_thumb_large" />
                                                    <?php }else{ ?>
                                                    <img class="img-responsive" src="<?php echo $system['thumbnail']['source']; ?>" alt="product_thumb_large" />
                                                    <?php } ?>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                        </div>
                        <hr>


                        <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#sternContainer" aria-expanded="false" aria-controls="system">
                          Sterne
                        </button>
                        <a name="Sterne"></a>
                        <div class="collapse" id="sternContainer">
                        <!-- <h4>Sterne</h4> -->
                        <div class="row">
                            <?php
                            foreach ($stararray as $key => $star) {
                            ?>
                            <div class="col-md-4">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Name:</th>
                                        <td>
                                            <?php echo $star['designation']?>
                                            <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $star['id']; ?><br/>Letzte Änderung:<br/><?php echo timeger($star['time_modified']); ?>"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Alter:</th>
                                        <td><?php echo round($star['age'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Größe:</th>
                                        <td><?php echo round($star['size'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Typ:</th>
                                        <td><?php echo formattype($star['type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Subtyp:</th>
                                        <td><?php echo $star['subtype']['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Code:</th>
                                        <td><?php echo $star['code']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Raumstationen:</th>
                                        <td>
                                            <?php
                                            if(!empty($star['manmade'])){
                                                foreach ($star['manmade'] as $key => $value) {
                                                    echo $manmadearray[$value]['designation'].'<br>';
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Monde:</th>
                                        <td>
                                        <?php
                                        if(!empty($star['moons'])){
                                            foreach ($star['moons'] as $key => $value) {
                                                echo $sattelitearray[$value]['designation'].'<br>';
                                            }
                                        }
                                        ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Asteroiden:</th>
                                        <td>
                                        <?php
                                        if(!empty($star['belts'])){
                                            foreach ($star['belts'] as $key => $value) {
                                                echo $asteroidbeltarray[$value]['designation'].'<br>';
                                            }
                                        }
                                        ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Starmap URL:</th>
                                        <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $star['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $star['code'], $system['code']).'</a>'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                        </div>
                        <hr>

                        <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#jumppointContainer" aria-expanded="false" aria-controls="system">
                          Jumppoints
                        </button>
                        <a name="Jumppoints"></a>
                        <div class="collapse" id="jumppointContainer">
                        <!-- <h4>Jumppoints</h4> -->
                        <div class="row">
                            <?php
                            foreach ($jumppointarray as $key => $jumppoint) {
                            ?>
                            <div class="col-md-4" style="margin-bottom: 20px;">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Ziel:</th>
                                        <td>
                                            <?php echo $jumppoint['designation']; ?>
                                            <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $jumppoint['id']; ?><br/>Letzte Änderung:<br/><?php echo timeger($jumppoint['time_modified']); ?>"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Entfernung:</th>
                                        <td><?php echo round($jumppoint['distance'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lat:</th>
                                        <td><?php echo round($jumppoint['latitude'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lon:</th>
                                        <td><?php echo round($jumppoint['longitude'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Typ:</th>
                                        <td><?php echo formattype($jumppoint['type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Starmap URL:</th>
                                        <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $jumppoint['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $jumppoint['code'], $system['code']).'</a>'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                        </div>
                        <hr>


                        <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#planetContainer" aria-expanded="false" aria-controls="system">
                          Planeten
                        </button>
                        <a name="Planeten"></a>
                        <div class="collapse" id="planetContainer">
                        <!-- <h4>Planeten</h4> -->
                        <?php
                        foreach ($planetarray as $key => $planet) {
                        ?>
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-3">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Nummer:</th>
                                        <td>
                                            <?php echo $planet['designation']; ?>
                                            <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $planet['id']; ?><br/>Letzte Änderung:<br/><?php echo timeger($planet['time_modified']); ?>"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td><?php echo $planet['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Orbitale Umlaufzeit:</th>
                                        <td><?php echo (isset($planet['orbit_period'])?round($planet['orbit_period'],3):'-'); ?> <span data-toggle="tooltip" data-placement="top" title="Standard Earth Days" style="border-bottom: 1px dotted #000;">SED</span></td>
                                    </tr>
                                    <tr>
                                        <th>Alter:</th>
                                        <td><?php echo round($planet['age'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lat:</th>
                                        <td><?php echo round($planet['latitude'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lon:</th>
                                        <td><?php echo round($planet['longitude'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th rowspan="<?php echo count($system['affiliation']); ?>">Kontrolle:</th>
                                        <?php
                                        foreach ($system['affiliation'] as $key => $value) {
                                        ?>
                                        <td style="color:'.$value['color'].';">
                                            <a class="affiliationlink" href="https://robertsspaceindustries.com/rsi/static/js/starmap/sourceimages/factions/<?php echo $value['code']; ?>.png" target="_blank">
                                                <img class="affiliationicon" src="https://robertsspaceindustries.com/rsi/static/js/starmap/sourceimages/factions/<?php echo $value['code']; ?>.png" />
                                            </a>
                                            <?php echo $value['name']; ?>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-2">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Bewohnbar:</th>
                                        <td><?php echo ($planet['habitable']==1?'Ja':'Nein'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Bevölkerung:</th>
                                        <td><?php echo $planet['sensor_population']; ?> Mrd.</td>
                                    </tr>
                                    <tr>
                                        <th>Wirtschaft:</th>
                                        <td><?php echo $planet['sensor_economy']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Gefahr:</th>
                                        <td><?php echo $planet['sensor_danger']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Größe: <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="Die Größenangaben der Planeten in km sind in Relation zu anderen Planeten im Sol-System korrekt"></i></th>
                                        <td><?php echo round($planet['size'],2); ?> km</td>
                                    </tr>
                                    <tr>
                                        <th>Typ:</th>
                                        <td><?php echo formattype($planet['type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Subtyp:</th>
                                        <td><?php echo $planet['subtype']['name']; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-4">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Beschreibung:</th>
                                        <td><?php echo $planet['description']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Raumstationen:</th>
                                        <td>
                                            <?php
                                            if(!empty($planet['manmade'])){
                                                foreach ($planet['manmade'] as $key => $value) {
                                                    echo $manmadearray[$value]['designation'].'<br>';
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Monde:</th>
                                        <td>
                                            <?php
                                            if(!empty($planet['moons'])){
                                                foreach ($planet['moons'] as $key => $value) {
                                                    echo $sattelitearray[$value]['designation'].'<br>';
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Asteroiden:</th>
                                        <td>
                                        <?php
                                        if(!empty($planet['belts'])){
                                            foreach ($planet['belts'] as $key => $value) {
                                                echo $asteroidbeltarray[$value]['designation'].'<br>';
                                            }
                                        }
                                        ?>
                                        </td>
                                    </tr>
                                    <?php if(!empty($planet['texture'])){ ?>
                                    <tr>
                                        <th>Textur Datei:</th>
                                        <td>
                                            <a href="<?php echo $planet['texture']['source']; ?>" target="_blank">Textur</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <?php
                                    if(!empty($planet['thumbnail'])){
                                    ?>
                                        <tr>
                                            <th>Bild:</th>
                                            <td><a href="<?php echo $planet['thumbnail']['source']; ?>" target="_blank">Link</a></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <th>Starmap URL:</th>
                                        <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $planet['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $planet['code'], $system['code']).'</a>'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php } ?>
                        </div>
                        <hr>

                        <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#raumstationContainer" aria-expanded="false" aria-controls="system">
                          Raumstationen
                        </button>
                        <a name="Raumstationen"></a>
                        <div class="collapse" id="raumstationContainer">
                        <!-- <h4>Raumstationen</h4> -->
                        <?php
                        foreach ($manmadearray as $key => $station) {
                        ?>
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-3">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Name:</th>
                                        <td>
                                            <?php echo $station['designation']; ?>
                                            <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $station['id']; ?><br/>Letzte Änderung:<br/><?php echo timeger($station['time_modified']); ?>"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Größe:</th>
                                        <td><?php echo round($station['size'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Entfernung:</th>
                                        <td><?php echo round($station['distance'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lat:</th>
                                        <td><?php echo round($station['latitude'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lon:</th>
                                        <td><?php echo round($station['longitude'],2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Typ:</th>
                                        <td><?php echo formattype($station['type']); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-2">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Bewohnbar:</th>
                                        <td><?php echo ($station['habitable']==1?'Ja':'Nein'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Bevölkerung:</th>
                                        <td><?php echo $station['sensor_population']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Wirtschaft:</th>
                                        <td><?php echo $station['sensor_economy']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Gefahr:</th>
                                        <td><?php echo $station['sensor_danger']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Typ:</th>
                                        <td><?php echo formattype($station['type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Subtyp:</th>
                                        <td><?php echo $station['subtype']['name']; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-4">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Beschreibung:</th>
                                        <td><?php echo $station['description']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Parent:</th>
                                        <td>
                                            <?php
                                            if(!empty($station['parent_id'])){
                                                if(array_key_exists($station['parent_id'], $planetarray)){
                                                    echo $planetarray[$station['parent_id']]['designation'];
                                                }
                                                if(array_key_exists($station['parent_id'], $stararray)){
                                                    echo $stararray[$station['parent_id']]['designation'];
                                                }
                                                if(array_key_exists($station['parent_id'], $sattelitearray)){
                                                    echo $sattelitearray[$station['parent_id']]['designation'];
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php if(!empty($station['model'])){ ?>
                                    <tr>
                                        <th>3D Datei:</th>
                                        <td>
                                            <a href="<?php echo $station['model']['source']; ?>" target="_blank">DAE Datei</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <th>Starmap URL:</th>
                                        <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $station['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $station['code'], $system['code']).'</a>'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                    <hr>


                    <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#mondContainer" aria-expanded="false" aria-controls="system">
                      Monde
                    </button>
                    <a name="Monde"></a>
                    <div class="collapse" id="mondContainer">
                    <!-- <h4>Monde</h4> -->
                    <?php
                    foreach ($sattelitearray as $key => $sattelite) {
                    ?>
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <table class="table table-striped">
                                <tr>
                                    <th>Name:</th>
                                    <td>
                                        <?php echo $sattelite['designation']; ?>
                                        <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $sattelite['id']; ?><br/>Letzte Änderung:<br/><?php echo timeger($sattelite['time_modified']); ?>"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Größe:</th>
                                    <td><?php echo $sattelite['size']; ?></td>
                                </tr>
                                <tr>
                                    <th>Entfernung:</th>
                                    <td><?php echo $sattelite['distance']; ?></td>
                                </tr>
                                <tr>
                                    <th>Lat:</th>
                                    <td><?php echo $sattelite['latitude']; ?></td>
                                </tr>
                                <tr>
                                    <th>Lon:</th>
                                    <td><?php echo $sattelite['longitude']; ?></td>
                                </tr>
                                <tr>
                                    <th>Typ:</th>
                                    <td><?php echo formattype($sattelite['type']); ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-2">
                            <table class="table table-striped">
                                <tr>
                                    <th>Bewohnbar:</th>
                                    <td><?php echo ($sattelite['habitable']==1?'Ja':'Nein'); ?></td>
                                </tr>
                                <tr>
                                    <th>Bevölkerung:</th>
                                    <td><?php echo $sattelite['sensor_population']; ?></td>
                                </tr>
                                <tr>
                                    <th>Wirtschaft:</th>
                                    <td><?php echo $sattelite['sensor_economy']; ?></td>
                                </tr>
                                <tr>
                                    <th>Gefahr:</th>
                                    <td><?php echo $sattelite['sensor_danger']; ?></td>
                                </tr>
                                <tr>
                                    <th>Typ:</th>
                                    <td><?php echo formattype($sattelite['type']); ?></td>
                                </tr>
                                <tr>
                                    <th>Subtyp:</th>
                                    <td><?php echo $sattelite['subtype']['name']; ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-4">
                            <table class="table table-striped">
                                <tr>
                                    <th>Beschreibung:</th>
                                    <td><?php echo $sattelite['description']; ?></td>
                                </tr>
                                <tr>
                                    <th>Parent:</th>
                                    <td>
                                        <?php
                                        if(!empty($sattelite['parent_id'])){
                                            if(array_key_exists($sattelite['parent_id'], $planetarray)){
                                                echo $planetarray[$sattelite['parent_id']]['designation'].
                                                     ($planetarray[$sattelite['parent_id']]['name']!=''?' - '.$planetarray[$sattelite['parent_id']]['name']:'');
                                            }
                                            if(array_key_exists($sattelite['parent_id'], $stararray)){
                                                echo $stararray[$sattelite['parent_id']]['designation'].
                                                     ($sattelite[$sattelite['parent_id']]['name']!=''?' - '.$sattelite[$sattelite['parent_id']]['name']:'');
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php if(!empty($sattelite['texture'])){ ?>
                                <tr>
                                    <th>Textur Datei:</th>
                                    <td>
                                        <a href="<?php echo $sattelite['texture']['source']; ?>" target="_blank">Textur</a>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <th>Starmap URL:</th>
                                    <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $sattelite['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $sattelite['code'], $system['code']).'</a>'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php } ?>
                </div>
                <hr>

                <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#asteroidContainer" aria-expanded="false" aria-controls="system">
                  Asteroidengürtel
                </button>
                <a name="Asteroidengürtel"></a>
                <div class="collapse" id="asteroidContainer">
                <!-- <h4>Asteroidengürtel</h4> -->
                <?php
                foreach ($asteroidbeltarray as $key => $asteroid) {
                ?>
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-3">
                        <table class="table table-striped">
                            <tr>
                                <th>Name:</th>
                                <td>
                                    <?php echo $asteroid['designation']; ?>
                                    <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" data-placement="top" title="ID: <?php echo $asteroid['id']; ?><br/>Letzte Änderung:<br/><?php echo timeger($asteroid['time_modified']); ?>"></i>
                                </td>
                            </tr>
                            <tr>
                                <th>Größe:</th>
                                <td><?php echo $asteroid['size']; ?></td>
                            </tr>
                            <tr>
                                <th>Entfernung:</th>
                                <td><?php echo $asteroid['distance']; ?></td>
                            </tr>
                            <tr>
                                <th>Lat:</th>
                                <td><?php echo $asteroid['latitude']; ?></td>
                            </tr>
                            <tr>
                                <th>Lon:</th>
                                <td><?php echo $asteroid['longitude']; ?></td>
                            </tr>
                            <tr>
                                <th>Typ:</th>
                                <td><?php echo formattype($asteroid['type']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-2">
                        <table class="table table-striped">
                            <tr>
                                <th>Bewohnbar:</th>
                                <td><?php echo ($asteroid['habitable']==1?'Ja':'Nein'); ?></td>
                            </tr>
                            <tr>
                                <th>Bevölkerung:</th>
                                <td><?php echo $asteroid['sensor_population']; ?></td>
                            </tr>
                            <tr>
                                <th>Wirtschaft:</th>
                                <td><?php echo $asteroid['sensor_economy']; ?></td>
                            </tr>
                            <tr>
                                <th>Gefahr:</th>
                                <td><?php echo $asteroid['sensor_danger']; ?></td>
                            </tr>
                            <tr>
                                <th>Typ:</th>
                                <td><?php echo formattype($asteroid['type']); ?></td>
                            </tr>
                            <tr>
                                <th>Subtyp:</th>
                                <td><?php echo $asteroid['subtype']['name']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-4">
                        <table class="table table-striped">
                            <tr>
                                <th>Beschreibung:</th>
                                <td><?php echo $asteroid['description']; ?></td>
                            </tr>
                            <tr>
                                <th>Parent:</th>
                                <td>
                                    <?php
                                    if(!empty($asteroid['parent_id'])){
                                        if(array_key_exists($asteroid['parent_id'], $planetarray)){
                                            echo $planetarray[$asteroid['parent_id']]['designation'].
                                                 ($planetarray[$asteroid['parent_id']]['name']!=''?' - '.$planetarray[$asteroid['parent_id']]['name']:'');
                                        }
                                        if(array_key_exists($asteroid['parent_id'], $stararray)){
                                            echo $stararray[$asteroid['parent_id']]['designation'];
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Starmap URL:</th>
                                <td><?php echo '<a href="'.$starmapurl.sprintf($getTempalte, $asteroid['code'], $system['code']).'">'.$starmapurl.sprintf($getTempalte, $asteroid['code'], $system['code']).'</a>'; ?></td>
                            </tr>

                        </table>
                    </div>
                </div>
            <?php } ?>
            </div>
            <hr>

                <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#wikiText" aria-expanded="false" aria-controls="system">
                  Wiki Quelltext
                </button>
                <a name="Asteroidengürtel"></a>
                <div class="collapse" id="wikiText">
                    <?php
                        echo '<pre>';
                        echo sprintf(
                            $systemTemplate,
                            $data[0]['description'],
                            round($data[0]['aggregated_size'],2),
                            isset(array_values($stararray)[0]['age']) && !empty(array_values($stararray)[0]['age']) ? round(array_values($stararray)[0]['age'],2) : '-',
                            count($stararray),
                            isset(array_values($stararray)[0]['subtype']['name']) && !empty(array_values($stararray)[0]['subtype']['name']) ? str_replace(array('-','Main','Sequence','Star','Dwarf',' '), '', array_values($stararray)[0]['subtype']['name']) : '-',
                            isset(array_values($stararray)[0]['size']) && !empty(array_values($stararray)[0]['size']) ? round(array_values($stararray)[0]['size'],2) : '-',
                            $data[0]['affiliation'][0]['name'],
                            isset($data[0]['aggregated_population'])?str_replace('.', ',', $data[0]['aggregated_population']):'',
                            isset($data[0]['aggregated_economy'])?str_replace('.', ',', $data[0]['aggregated_economy']):'',
                            isset($data[0]['aggregated_danger'])?str_replace('.', ',', $data[0]['aggregated_danger']):'',
                            '',
                            '',
                            '',
                            ''

                            );
                        echo '</pre>';
                    ?>
                </div>

            <hr>


                <button class="btn btn-primary" type="button" data-toggle="collapse" style="margin-bottom: 10px;"  data-target="#rawContainer" aria-expanded="false" aria-controls="system">
                  Rohdaten
                </button>
                <div class="collapse" id="rawContainer">
                    <!-- <h4>Rohdaten</h4> -->
                    <?php
                        echo '<pre>';
                        print_r($data);
                        echo '</pre>';
                    }
                    ?>
        		</div>
        	</div>
        </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2-rc.1/js/select2.min.js"></script>
        <script type="text/javascript">
            jQuery('select').select2();

            jQuery('[data-toggle="tooltip"]').tooltip({});

        </script>
    </body>
</html>
<?php
function timeger($time){
    $timestamp = strtotime($time);
    return date('H:i d.m.Y', $timestamp);
}

function formattype($type){
    return ucwords(strtolower(str_replace('_', ' ', $type)));
}
