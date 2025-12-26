<?php declare(strict_types=1);

namespace Calculator\BilloDiContainer;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class BilloDiContainer
{
    /** @var array<string, string|callable> */
    private array $bindings = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /**
     * Bind an abstract/contract to a concrete class or factory.
     */
    public function bind(string $contract, string|callable $concrete, bool $singleton = false): self
    {
        $this->bindings[$contract] = $concrete;
        $this->singletons[$contract] = $singleton;
        return $this;
    }

    public function bindAll(array $bindings): self
    {
        foreach ($bindings as $contract => $concrete) {
            $this->bind($contract, $concrete);
        }

        return $this;
    }

    /**
     * Shortcut for singleton binding.
     */
    public function singleton(string $contract, string|callable $concrete): void
    {
        $this->bind($contract, $concrete, true);
    }

    /**
     * Register an already-built instance.
     */
    public function set(string $contract, mixed $instance): void
    {
        $this->instances[$contract] = $instance;
        $this->singletons[$contract] = true;
    }

    /**
     * Resolve an instance.
     *
     * Usage examples:
     *   $container->instance(Foo::class);
     *   $container->instance(Bar::class, ['dsn' => '...']); // named args by param name
     *   $container->instance(Baz::class, [123, 'x']);       // positional args
     */
    public function instance(string $contract, array ...$args): object
    {
        // Merge variadic arrays into a single argument bag.
        // Supports:
        //  - named: ['dsn' => '...']
        //  - positional: [0 => '...', 1 => '...']
        $provided = [];
        foreach ($args as $a) {
            // later arrays override earlier keys
            $provided = $a + $provided; // keep numeric order if you pass positional as [0=>...]
            foreach ($a as $k => $v) {
                $provided[$k] = $v;
            }
        }

        // Return cached singleton instance if available
        if (($this->singletons[$contract] ?? false) && array_key_exists($contract, $this->instances)) {
            $inst = $this->instances[$contract];
            if (!is_object($inst)) {
                throw new RuntimeException("Stored instance for {$contract} is not an object.");
            }
            return $inst;
        }

        // Figure out what to build
        $concrete = $this->bindings[$contract] ?? $contract;

        $object = $this->build($concrete, $provided);

        // Cache if singleton
        if ($this->singletons[$contract] ?? false) {
            $this->instances[$contract] = $object;
        }

        return $object;
    }

    /**
     * @param string|callable $concrete
     * @param array<string|int, mixed> $provided
     */
    private function build(string|callable $concrete, array $provided): object
    {
        // Factory/closure binding
        if (is_callable($concrete)) {
            $obj = $concrete($this, $provided);
            if (!is_object($obj)) {
                throw new RuntimeException("Factory did not return an object.");
            }
            return $obj;
        }

        // Alias binding to another contract/class
        if (isset($this->bindings[$concrete]) && $this->bindings[$concrete] !== $concrete) {
            return $this->instance($concrete, $provided);
        }

        if (!class_exists($concrete)) {
            throw new RuntimeException("Cannot resolve '{$concrete}': class does not exist.");
        }

        $ref = new ReflectionClass($concrete);
        if (!$ref->isInstantiable()) {
            throw new RuntimeException("Cannot resolve '{$concrete}': not instantiable.");
        }

        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            /** @var object */
            return $ref->newInstance();
        }

        $params = $ctor->getParameters();
        $resolvedArgs = [];

        $positionalIndex = 0;

        foreach ($params as $param) {
            $name = $param->getName();

            // 1) Named param provided
            if (array_key_exists($name, $provided)) {
                $resolvedArgs[] = $provided[$name];
                continue;
            }

            // 2) Positional param provided (0,1,2...)
            if (array_key_exists($positionalIndex, $provided)) {
                $resolvedArgs[] = $provided[$positionalIndex];
                $positionalIndex++;
                continue;
            }

            // 3) Class-typed param -> resolve recursively
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dep = $type->getName();
                $resolvedArgs[] = $this->instance($dep);
                continue;
            }

            // 4) Default value
            if ($param->isDefaultValueAvailable()) {
                $resolvedArgs[] = $param->getDefaultValue();
                continue;
            }

            // 5) Nullable -> null
            if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
                $resolvedArgs[] = null;
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter \${$name} for {$ref->getName()}::__construct()"
            );
        }

        /** @var object */
        return $ref->newInstanceArgs($resolvedArgs);
    }
}
