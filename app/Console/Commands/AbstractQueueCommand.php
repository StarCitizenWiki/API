<?php declare(strict_types=1);
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Checks if output is present before calling parent commands
 */
abstract class AbstractQueueCommand extends Command
{
    /**
     * @var ProgressBar
     */
    protected $bar;

    /**
     * @inheritDoc
     */
    public function info($string, $verbosity = null): void
    {
        if ($this->output === null) {
            return;
        }

        parent::info($string, $verbosity);
    }

    /**
     * @inheritDoc
     */
    public function error($string, $verbosity = null): void
    {
        if ($this->output === null) {
            return;
        }

        parent::error($string, $verbosity);
    }

    /**
     * Creates a progressbar if output is not null
     *
     * @param int $size Progressbar size
     */
    public function createProgressBar(int $size): void
    {
        if ($this->output === null) {
            return;
        }

        $this->bar = $this->output->createProgressBar($size);
    }

    /**
     * Advances the bar
     */
    public function advanceBar(): void
    {
        if ($this->output === null || $this->bar === null) {
            return;
        }

        $this->bar->advance();
    }

    /**
     * Finishes the bar
     */
    public function finishBar(): void
    {
        if ($this->output === null || $this->bar === null) {
            return;
        }

        $this->bar->finish();
    }
}