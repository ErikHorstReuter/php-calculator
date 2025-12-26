<?php declare(strict_types=1);
namespace Calculator\IO;
interface InputReaderInterface
{
    public function readLine(?string $prompt = null): string;

    public function readInt(?string $prompt = null, ?int $default = null): int;

    public function readFloat(?string $prompt = null, ?float $default = null): float;

    public function readYesNo(string $prompt = "Ja/Nein? ", bool $default = true): bool;
}