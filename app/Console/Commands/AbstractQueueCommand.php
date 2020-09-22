<?php

declare(strict_types=1);

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
