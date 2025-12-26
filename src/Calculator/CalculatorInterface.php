<?php declare(strict_types=1);

namespace Calculator\Calculator;


interface CalculatorInterface
{
    public function calculate(string $term): float;
}
