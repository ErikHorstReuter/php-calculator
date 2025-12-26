<?php declare(strict_types=1);
namespace Calculator\IO;
interface OutputWriterInterface
{
    public function write(string $text): void;

    public function writeLine(string $text = ''): void;
}