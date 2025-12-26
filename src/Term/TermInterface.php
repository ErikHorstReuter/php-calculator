<?php declare(strict_types=1);

namespace Calculator\Term;

use ArrayAccess;
use Countable;
use Iterator;

interface TermInterface extends ArrayAccess, Countable, Iterator {}
