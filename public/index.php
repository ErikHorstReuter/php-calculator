<?php
declare(strict_types=1);

use Calculator\BilloDiContainer\BilloDiContainer;
use Calculator\Calculator\CalculatorInterface;
use Calculator\IO\IOInterface;


require_once __DIR__ . '/../vendor/autoload.php';
/** @var BilloDiContainer $container */
$container = require_once __DIR__ . '/../src/BilloDiContainer/mapping.php';

/** @var IOInterface $console */
$console = $container->instance(IOInterface::class);
/** @var CalculatorInterface $calculator */
$calculator = $container->instance(CalculatorInterface::class);


$console->writeLine('=== PHP Console - Calculator ===');

do {
    $term = $console->readLine('Bitte gib deine Term jetzt ein: ');

    try {
        $result = $calculator->calculate($term);
        $console->writeLine("Ergebnis: $result");
    } catch (Exception $e) {
        $console->writeLine('Der Term konnte leider nicht berechnet werden.');
    }

    $loop = $console->readYesNo('Möchtest du einen weiteren Term berechnen?');

} while ($loop);

$console->writeLine('Bis zum nächsten Mal!');
