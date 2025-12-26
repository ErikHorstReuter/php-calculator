<?php declare(strict_types=1);

namespace Calculator\IO;

class Console implements IOInterface
{

    public function write(string $text): void
    {
        echo $text;
        if (function_exists('flush')) {
            flush();
        }
    }

    public function writeLine(string $text = ''): void
    {
        echo $text . PHP_EOL;
    }

    public function readLine(?string $prompt = null): string
    {
        if ($prompt !== null) {
            $this->write($prompt);
        }

        $line = fgets(STDIN);
        if ($line === false) {
            return '';
        }
        return rtrim($line, "\r\n");
    }

    public function readInt(?string $prompt = null, ?int $default = null): int
    {
        while (true) {
            $s = $this->readLine($prompt);
            if ($s === '' && $default !== null) {
                return $default;
            }
            if (preg_match('/^[+-]?\d+$/', $s)) {
                return (int)$s;
            }
            $this->writeLine("Bitte eine ganze Zahl eingeben.");
        }
    }

    public function readFloat(?string $prompt = null, ?float $default = null): float
    {
        while (true) {
            $s = $this->readLine($prompt);
            if ($s === '' && $default !== null) {
                return $default;
            }

            $norm = str_replace(',', '.', $s);
            if (is_numeric($norm)) {
                return (float)$norm;
            }
            self::WriteLine("Bitte eine Zahl eingeben (z.B. 3.14).");
        }
    }

    public function readYesNo(string $prompt = "Ja/Nein? ", bool $default = true): bool
    {
        $suffix = $default ? ": [J/n] " : ": [j/N] ";
        while (true) {
            $s = strtolower(trim($this->readLine($prompt . $suffix)));
            if ($s === '') return $default;
            if (in_array($s, ['j', 'ja', 'y', 'yes'], true)) return true;
            if (in_array($s, ['n', 'nein', 'no'], true)) return false;
            $this->writeLine("Bitte 'j' oder 'n' eingeben.");
        }
    }

}
