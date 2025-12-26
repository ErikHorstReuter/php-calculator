<?php declare(strict_types=1);

namespace Calculator\TermParser;

use Calculator\Term\TermInterface;


interface TermParserInterface {
    public function parse(string $term): TermInterface;
}
