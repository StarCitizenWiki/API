<?php

use App\Support\Preprocessors\RemoveAppComponentsPreprocessor;
use Spatie\MarkdownResponse\Postprocessors\CollapseBlankLinesPostprocessor;
use Spatie\MarkdownResponse\Postprocessors\RemoveHtmlTagsPostprocessor;
use Spatie\MarkdownResponse\Preprocessors\RemoveHeaderPreprocessor;
use Spatie\MarkdownResponse\Preprocessors\RemoveNavigationPreprocessor;
use Spatie\MarkdownResponse\Preprocessors\RemoveScriptsAndStylesPreprocessor;

return [
    'preprocessors' => [
        RemoveScriptsAndStylesPreprocessor::class,
        RemoveNavigationPreprocessor::class,
        RemoveHeaderPreprocessor::class,
        RemoveAppComponentsPreprocessor::class,
    ],

    'postrocessors' => [
        RemoveHtmlTagsPostprocessor::class,
        CollapseBlankLinesPostprocessor::class
    ]
];
