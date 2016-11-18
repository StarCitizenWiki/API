<?php
if (strpos($match['name'], 'schwarze Version') !== false)
{
    $color = 'black';
}
else
{
    $color = 'blue';
}
if (strpos($match['name'], 'nur Zahl') !== false)
{
    $format = 'int';
}
else
{
    $format = 'whole';
}

if( is_file( basedir . '/resources/output/fundImage' . $format . '_' . $color . '.png' ) && time() - ( 600 ) <= filemtime( basedir . '/resources/output/fundImage' . $format . '_' . $color . '.png' ) )
{
    header('Content-type: image/png');
    echo file_get_contents( basedir . '/resources/output/fundImage' . $format . '_' . $color . '.png' );
}
else
{
    $url = "https://robertsspaceindustries.com/api/stats/getCrowdfundStats";
    $datatopost = array (
        "funds" =>  true,
    );

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $datatopost );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
    $output = curl_exec( $ch );
    curl_close( $ch );

    $funds = json_decode( $output, true );

    if( $funds["success"] == 1 )
    {
        $funds = number_format( substr( $funds["data"]["funds"], 0, -2 ), 0, ',', '.' ) . '$';

        if( $format == 'whole' )
        {
            $im = imagecreatetruecolor( 280, 75 );
        }
        else
        {
            $im = imagecreatetruecolor( 280, 35 );
        }

        imagesavealpha( $im, true );

        $white  = imagecolorallocate( $im, 255, 255, 255 );
        $grey   = imagecolorallocate( $im, 128, 128, 128 );
        $three  = imagecolorallocate( $im, 51, 51, 51 );
        $black  = imagecolorallocate( $im, 0, 0, 0 );
        $blue   = imagecolorallocate( $im, 0, 231, 255 );
        $lightblue = imagecolorallocate( $im, 217, 237, 247 );

        imagefilledrectangle( $im, 0, 0, 150, 25, $black );

        $trans_colour = imagecolorallocatealpha( $im, 0, 0, 0, 127 );

        imagefill( $im, 0, 0, $trans_colour );

        if( $color == 'black' )
        {
            $three  = imagecolorallocate( $im, 51, 51, 51 );
        }
        else
        {
            $three  = imagecolorallocate( $im, 0, 231, 255 );
        }

        $font = basedir . '/resources/fonts/orbitron-light-webfont.ttf';

        if( $format == 'whole' )
        {
            $text = 'Crowdfunding:';
            imagettftext( $im, 25, 0, 0, 30, $three, $font, $text );
            imagettftext( $im, 25, 0, 2, 70, $three, $font, $funds );
        }
        else
        {
            imagettftext( $im, 20, 0, 2, 30, $three, $font, $funds );
        }

        header('Content-type: image/png');
        imagepng( $im, basedir . '/resources/output/fundImage' . $format . '_' . $color . '.png' );
        imagepng( $im );
        imagedestroy( $im );
    }
    else
    {
        header('Content-type: image/png');
        $im = imagecreatetruecolor( 1, 1 );
        imagepng( $im );
        imagedestroy( $im );
    }
}
