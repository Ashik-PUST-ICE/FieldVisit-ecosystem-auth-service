<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto {name} {--props=}';

    protected $description = 'Create a new DTO (Data Transfer Object) class with typed properties and defaults';

    public function handle(): void
    {
        $name = $this->argument('name');
        $rawProps = $this->option('props');
        $props = $rawProps ? explode(' ', $rawProps) : [];

        $className = Str::afterLast($name, '/');
        $namespace = 'App\\DTOs';
        $path = app_path('DTOs');

        if (Str::contains($name, '/')) {
            $folders = Str::beforeLast($name, '/');
            $path .= '/'.$folders;
            $namespace .= '\\'.Str::replace('/', '\\', $folders);

            if (! File::exists($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
        }

        $filePath = $path.'/'.$className.'.php';

        if (File::exists($filePath)) {
            $this->error('DTO already exists!');

            return;
        }

        $stub = $this->getStub();

        $constructorParams = [];
        $fromArrayLines = [];

        foreach ($props as $prop) {
            // Parse format: name:type=default
            preg_match('/(?<name>\w+):(?<type>\??[\w\\\]+)(=(?<default>.*))?/', $prop, $matches);

            $pName = $matches['name'];
            $pType = $matches['type'];
            $pDefault = $matches['default'] ?? null;

            $typeHint = $pType;
            $defaultCode = $pDefault !== null
                ? ' = '.(is_numeric($pDefault) || $pDefault === 'null' ? $pDefault : "'".$pDefault."'")
                : '';

            $constructorParams[] = "        public readonly {$typeHint} \${$pName}{$defaultCode},";

            $fromArrayDefault = $pDefault !== null
                ? (is_numeric($pDefault) || $pDefault === 'null' ? $pDefault : "'{$pDefault}'")
                : 'null';

            $fromArrayLines[] = Str::startsWith($pType, '?') || $pDefault !== null
                ? "            \$data['{$pName}'] ?? {$fromArrayDefault},"
                : "            \$data['{$pName}'],";
        }

        $constructor = empty($constructorParams)
            ? ''
            : "    public function __construct(\n".implode("\n", $constructorParams)."\n    ) {}\n";

        $fromArray = empty($fromArrayLines)
            ? "    public static function fromArray(array \$data): self\n    {\n        return new self();\n    }"
            : "    public static function fromArray(array \$data): self\n    {\n        return new self(\n".implode("\n", $fromArrayLines)."\n        );\n    }";

        $stub = str_replace('DummyNamespace', $namespace, $stub);
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('DummyConstructor', $constructor, $stub);
        $stub = str_replace('DummyFromArray', $fromArray, $stub);

        File::put($filePath, $stub);

        $this->info("✅ DTO created: {$filePath}");
    }

    protected function getStub(): string
    {
        return File::get(resource_path('stubs/dto.stub'));
    }
}
