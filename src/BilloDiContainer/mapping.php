<?php declare(strict_types=1);

namespace Calculator\BilloDiContainer;


return new BilloDiContainer()->bindAll([
    \Calculator\IO\IOInterface::class => \Calculator\IO\Console::class,
    \Calculator\Calculator\CalculatorInterface::class => \Calculator\Calculator\Calculator::class
]);
