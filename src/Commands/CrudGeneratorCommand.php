<?php

namespace Aslnbxrz\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrudGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generator
    {name : Class (singular) for example User} {path : Class (singular) for example User Api} {table : Class (singular) for example users} {version=v1 : Version (singular) for example v1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CRUD operations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return string
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $path = $this->argument('path');
        $dbname = $this->argument('table');
        $version = $this->argument('version');

        if ($path == '/') {
            $namespace = '';
        } else {
            $namespace = str_replace('/', '\\', $path);
        }
        $this->model($name, $dbname);
        $this->controller($name, $path);
        $this->route($name, $version, $namespace);
    }

    /**
     * @param string $name
     * @param string $tableName
     * @return void
     */
    protected function model(string $name, string $tableName): void
    {
        $attributes = Schema::getColumnListing($tableName);
        $fields = '';
        $rules = '';
        $columns = '';
        $i = 0;
        $count = count($attributes);
        foreach ($attributes as $attribute) {
            if ($attribute != 'id') {
                $i++;
                if ($i == $count) {
                    $fields .= "'{$attribute}'";
                } else {
                    $fields .= "'{$attribute}', ";
                }
                $type = Schema::getColumnType($tableName, $attribute);
                $rules .= match ($type) {
                    'text' => "\n\t\t\t'{$attribute}' => 'string|nullable',",
                    'bigint' => "\n\t\t\t'{$attribute}' => 'integer|nullable',",
                    default => "\n\t\t\t'{$attribute}' => '{$type}|nullable',",
                };
                $columns .= match ($type) {
                    'text', 'datetime' => "\n * @property string {$attribute}",
                    'bigint' => "\n * @property integer {$attribute}",
                    default => "\n * @property {$type} {$attribute}",
                };
            }
        }

        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{fillable}}',
                '{{table}}',
                '{{rules}}',
                '{{columns}}'
            ],
            [
                $name,
                $fields,
                $tableName,
                $rules,
                $columns
            ],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/Models/{$name}.php"), $modelTemplate);
        $this->message(" Model ", " [app/Models/{$name}.php] created successfully.");
    }

    protected function getStub($type): bool|string
    {
        return file_get_contents(resource_path("Stubs/$type.stub"));
    }

    protected function controller($name, $path)
    {
        $initialDir = "Http/Controllers/$path";
        if ($path == '/') {
            $path = "Http/Controllers/{$name}Controller.php";
            $namespace = '';
        } else {
            $namespace = '\\' . str_replace('/', '\\', $path);
            $path = "Http/Controllers/{$path}/{$name}Controller.php";
        }

        if (!File::isDirectory($initialDir)) {
            File::makeDirectory(app_path($initialDir), 0777, true, true);
        }

        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{namespace}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $namespace
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path($path), $controllerTemplate);
        $this->message(" Controller ", "[app/$path] created successfully");
    }

    protected function route($name, $version, $namespace)
    {
        $routeTemplate = str_replace(
            [
                '{{name}}',
                '{{version}}',
                '{{namespace}}',
                '{{routeName}}',
                '{{model_name}}',
            ],
            [
                $name,
                $version,
                $namespace,
                Str::lower(Str::plural(Str::replace('_', '-', $name))),
                Str::snake($name)
            ],
            $this->getStub('Route')
        );

        File::append(base_path('routes/api.php'), $routeTemplate);
        $this->message(" Routes ", "$name added successfully.");
    }

    public function message($model, $message, $type = "INFO", $fg = "white", $bg = "blue", $withLines = true)
    {
        $this->line("  <bg=$bg;fg=$fg> $type </> <bg=green;fg=white>$model</> $message");
    }
}
