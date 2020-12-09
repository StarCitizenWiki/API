<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Parser\CommLink;

use App\Services\Parser\CommLink\Image;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class ImageTest extends TestCase
{
    /**
     * @covers \App\Services\Parser\CommLink\Image::getImageIds
     * @covers \App\Services\Parser\CommLink\Image::extractImages
     * @covers \App\Services\Parser\CommLink\Image::extractImgTags
     * @covers \App\Services\Parser\CommLink\Image::extractCFeatureTemplateImages
     * @covers \App\Services\Parser\CommLink\Image::extractPostBackground
     * @covers \App\Services\Parser\CommLink\Image::extractSourceAttrs
     * @covers \App\Services\Parser\CommLink\Image::extractCssBackgrounds
     * @covers \App\Services\Parser\CommLink\Image::extractMediaImages
     * @covers \App\Services\Parser\CommLink\Image::extractRsiImages
     *
     * @covers \App\Services\Parser\CommLink\Image::cleanImgSource
     * @covers \App\Services\Parser\CommLink\Image::getDirHash
     */
    public function testParseImage(): void
    {
        $content = <<<HTML
<html>
<head>
<style>
#test {
    background-image: url(https://robertsspaceindustries.com/media/abcdefghijklm/source/Bartender.jpg);
}
#test2 {
    background-image: url("https://robertsspaceindustries.com/media/abcdefghijkln/source/Bartender2.jpg");
}
#test3 {
    background-image: url('https://robertsspaceindustries.com/media/abcdefghijkla/source/Bartender3.jpg');
}
#test4 {
    background-image: url(https://media.robertsspaceindustries.com/abcdefghisklm/source.jpg);
}
#test5 {
    background-image: url(/media/abcdefshijkln/source/Bartender5.jpg);
}
</style>
</head>
<body>
<div id="post">
<img src="https://robertsspaceindustries.com/media/ek9lhmput3njfr/source/GLoc-Bartender.jpg" />
<img src="https://media.robertsspaceindustries.com/media/1234567898765/source.jpg" />
<img src="/media/1234567898765/source/1.jpg" />
</div>

</body>
</html>
HTML;

        $crawler = new Crawler();
        $crawler->addHtmlContent($content);

        $image = new Image($crawler);

        $ids = $image->getImageIds();

        self::assertCount(8, $ids);
    }

    /**
     * @covers \App\Services\Parser\CommLink\Image::getImageIds
     * @covers \App\Services\Parser\CommLink\Image::extractImages
     * @covers \App\Services\Parser\CommLink\Image::extractPostBackground
     */
    public function testParsePostBackground(): void
    {
        $content = <<<HTML
<html>
<head>
</head>
<body>
<div id="post-background" style="background-image: url(https://media.robertsspaceindustries.com/media/1234567898765/source.jpg)">
</div>

</body>
</html>
HTML;

        $crawler = new Crawler();
        $crawler->addHtmlContent($content);

        $image = new Image($crawler);

        $ids = $image->getImageIds();

        self::assertCount(1, $ids);
    }

    /**
     * @covers \App\Services\Parser\CommLink\Image::getImageIds
     * @covers \App\Services\Parser\CommLink\Image::extractImages
     * @covers \App\Services\Parser\CommLink\Image::extractSourceAttrs
     */
    public function testParseSourceAttrs(): void
    {
        $content = <<<HTML
<html>
<head>
</head>
<body>
<script>
{
    source: 'https://media.robertsspaceindustries.com/media/abcabcabcabcabc/source.jpg'
}
</script>
</body>
</html>
HTML;

        $crawler = new Crawler();
        $crawler->addHtmlContent($content);

        $image = new Image($crawler);

        $ids = $image->getImageIds();

        self::assertCount(1, $ids);
    }

    /**
     * @covers \App\Services\Parser\CommLink\Image::getImageIds
     * @covers \App\Services\Parser\CommLink\Image::extractImages
     * @covers \App\Services\Parser\CommLink\Image::isSpecialPage
     */
    public function testParseSpecialPage(): void
    {
        $content = <<<HTML
<html>
<head>
</head>
<body>
<div id="layout-system">
</div>

<template>
'https://robertsspaceindustries.com/media/ek4lhmput3njfr/source/GLoc-Bartender.jpg'
'https://media.robertsspaceindustries.com/media/1254567898765/source.jpg'
</template>
</body>
</html>
HTML;

        $crawler = new Crawler();
        $crawler->addHtmlContent($content);

        $image = new Image($crawler);

        $ids = $image->getImageIds();

        self::assertCount(2, $ids);
    }
}
