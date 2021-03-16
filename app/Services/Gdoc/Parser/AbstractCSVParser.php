<?php

declare(strict_types=1);

namespace App\Services\Gdoc\Parser;

abstract class AbstractCSVParser
{
    /**
     * Stop parsing if true
     *
     * @var bool
     */
    protected bool $parsed = false;

    /**
     * The categories handleable by this parser
     *
     * @var array
     */
    protected array $handleable = [];

    /**
     * Parse the csv file
     */
    abstract public function parse(): void;

    /**
     * String representation of the parsed csv
     *
     * @return string
     */
    abstract public function __toString(): string;
}
