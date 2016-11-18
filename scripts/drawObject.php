<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ObjectViewer</title>
        <style media="screen">
            *{
                padding: 0;
                margin: 0;
                overflow: hidden;
            }
            /*canvas{
                cursor: all-scroll;
            }*/
        </style>
    </head>
    <body>
        <div id="container"></div>
        <script src="/static/js/three.min.js"></script>
        <script src="/static/js/Detector.js"></script>
        <script>
        if ( ! Detector.webgl ) Detector.addGetWebGLMessage();
        var container, camera, controls, scene, renderer, dae, loader;
        var counter = 0;
        var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight|| e.clientHeight|| g.clientHeight;
        </script>
<?php
switch ($match['params']['action']) {
    case 'collada':
        echo '<script src="/static/js/Animation.js"></script><script src="/static/js/AnimationHandler.js"></script><script src="/static/js/TrackballControls.js"></script><script src="/static/js/ColladaLoader.js"></script>';
        break;

    case 'planet':
        break;

    default:
        # code...
        break;
}

$whitelist = array('star-citizen.wiki', 'v3.star-citizen.wiki', 'scripts.star-citizten.wiki');
if(isset($match['params']['path'])){
    $url = $match['params']['path'];
    if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
        $parsed_url = parse_url($url);
        if($parsed_url !== false){
            if(!in_array($parsed_url['host'], $whitelist)){
                switch ($match['params']['action']) {
                    case 'collada':
                        if(substr($url, -4, 4) == '.dae'){
                            $handle = curl_init($url);
                            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
                            $response = curl_exec($handle);
                            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                            curl_close($handle);
                            if($httpCode == 404) {
                                echo 'Diese Datei existiert nicht';
                            }else{
                                ?>
                                <script>
                                var colloader = new THREE.ColladaLoader();
                                colloader.options.convertUpAxis = true;
                                colloader.load( <?php echo "'".$url."'"; ?>, function ( collada ) {
                                    dae = collada.scene;
                                    dae.scale.x = dae.scale.y = dae.scale.z = 200;
                                    init();
                                    animate();

                                } );

                                function init() {
                                    loader = new THREE.TextureLoader();
                                    camera = new THREE.PerspectiveCamera( 80, x / y, 1, 1000 );
                                    camera.position.set( 0, 0, 600 );

                                    controls = new THREE.TrackballControls( camera );

                                    controls.rotateSpeed = 10.0;
                                    controls.zoomSpeed = 1.2;
                                    controls.panSpeed = 2;



                                    if(x<600){
                                        controls.noZoom = true;
                                        controls.noPan = true;
                                        controls.noRotate = true;
                                    }else{
                                        controls.noZoom = false;
                                        controls.noPan = false;
                                        controls.noRotate = false;
                                    }

                                    controls.staticMoving = true;
                                    controls.dynamicDampingFactor = 0.3;

                                    controls.keys = [ 65, 83, 68 ];

                                    controls.rotateSpeed = 10.0;
                                    controls.zoomSpeed = 1.2;
                                    controls.panSpeed = 2;

                                    controls.minDistance = 50;
                                    controls.maxDistance = 700;

                                    controls.addEventListener( 'change', render );

                                    // world
                                    scene = new THREE.Scene();
                                    scene.add(dae);


                                    // lights
                                    light = new THREE.DirectionalLight( 0xffffff );
                                    light.position.set( 1, 1, 1 );
                                    scene.add( light );
                                    light = new THREE.DirectionalLight( 0xffffff );
                                    light.position.set( -1, -1, -1 );
                                    scene.add( light );
                                    var light = new THREE.HemisphereLight( 0xffffff, 0x333333, 3 );
                                    scene.add( light );


                                    // renderer
                                    renderer = new THREE.WebGLRenderer( { alpha: true, antialias: true } );
                                    renderer.setClearColor(0xffffff);
                                    renderer.setPixelRatio( window.devicePixelRatio );
                                    renderer.setSize( x, y );

                                    container = document.getElementById( 'container' );
                                    container.appendChild( renderer.domElement );

                                    window.addEventListener( 'resize', onWindowResize, false );

                                    render();
                                }

                                function onWindowResize() {

                                    camera.aspect = x / y;
                                    camera.updateProjectionMatrix();

                                    renderer.setSize( x, y );

                                    controls.handleResize();

                                    render();

                                }

                                function animate() {

                                    requestAnimationFrame( animate );
                                    controls.update();
                                    render();

                                }
                                var clock = new THREE.Clock();

                                function render() {
                                    var timer = Date.now() * 0.00015;
                                    dae.rotation.set( 10*(Math.PI/180), (counter++/4)*(Math.PI/180), 0*(Math.PI/180) );
                                    dae.updateMatrix();
                                    THREE.AnimationHandler.update( clock.getDelta() );
                                    renderer.render( scene, camera );
                                }
                                </script>
                                <?php
                            }
                        }else{
                            echo 'URL muss eine .dae Datei sein';
                        }
                        break;

                    case 'planet':
                        ?>
                        <script>
        				    init();

                			function init() {
                                loader = new THREE.TextureLoader();
                				camera = new THREE.PerspectiveCamera( 60, x / y, 1, 5000 );
                                camera.position.set( 0, 0, 12 );

                				scene = new THREE.Scene();
                                var geometry = new THREE.SphereGeometry( 5, 32, 32 );
                                var material = new THREE.MeshPhongMaterial()
                                loader.crossOrigin = 'anonymous';
                                material.map = loader.load('<?php echo $url; ?>');
                                sphere = new THREE.Mesh( geometry, material );
                                scene.add( sphere );

                                // light = new THREE.DirectionalLight( 0xffffff, 0.2 );
                                // light.position.set( 1, 1, 1 );
                                var light = new THREE.HemisphereLight( 0xffffff, 0xffffff, 1.5 );
                                scene.add( light );

                				renderer = new THREE.WebGLRenderer( { alpha: true, antialias: true } );
                                renderer.setClearColor(0xffffff);
                				renderer.setPixelRatio( window.devicePixelRatio );
                				renderer.setSize( x, y );

                				container = document.getElementById( 'container' );
                				container.appendChild( renderer.domElement );

                				window.addEventListener( 'resize', onWindowResize, false );
                				render();
                			}

                			function onWindowResize() {

                				camera.aspect = x / y;
                				camera.updateProjectionMatrix();

                				renderer.setSize( x, y );

                				controls.handleResize();

                				render();

                			}

                			function animate() {

                				requestAnimationFrame( animate );
                				controls.update();

                			}

                			function render() {
                                requestAnimationFrame(render);

                                sphere.rotation.y += 0.01;
                				renderer.render( scene, camera );
                			}
                		</script>
                        <?php
                        break;

                    default:
                        # code...
                        break;
                }
            }else{
                echo 'Diese URL ist nicht auf der Whitelist';
            }
        }else{
            echo 'URL fehlerhaft';
        }
    } else {
        echo 'Pfad fehlerhaft, kein Objekt geladen';
    }
}
?>
    </body>
</html>
