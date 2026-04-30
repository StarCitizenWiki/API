<?php

namespace App\Support\Preprocessors;

use Spatie\MarkdownResponse\Preprocessors\Preprocessor;

class RemoveAppComponentsPreprocessor implements Preprocessor
{

    public function __invoke(string $html): string
    {
        $html =  preg_replace('/<(div|details) data-remove\b[^>]*>.*?<\/\1>/is', '', $html);

        return $html;
    }
}
