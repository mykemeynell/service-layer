<?php

namespace ServiceLayer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use ServiceLayer\Console\Commands\Concerns\CreatesModels;
use ServiceLayer\Console\Commands\Concerns\CreatesRepositories;
use ServiceLayer\Console\Commands\Concerns\CreatesServices;

class MakeLayerCommand extends Command
{
    use CreatesModels, CreatesRepositories, CreatesServices;

    /**
     * Filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:layer {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service layer';

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->createModel();
        $this->createRepository();
        $this->createService();

        $name = $this->argument('name');
        $layerName = strtolower(Str::snake(Str::singular($name), '-'));
        $serviceLayerArray = [
            $layerName => [
                'model' => [
                    $this->getClassNamespace($name, '\\App\\Models', '\\') . $this->getSingularClassName($name, 'Interface::class'),
                    $this->getClassNamespace($name, '\\App\\Models', '\\') . $this->getSingularClassName($name, '::class')
                ],
                'repository' => [
                    $this->getClassNamespace($name, '\\App\\Repositories', '\\') . $this->getSingularClassName($name, 'RepositoryInterface::class'),
                    $this->getClassNamespace($name, '\\App\\Repositories', '\\') . $this->getSingularClassName($name, 'Repository::class')
                ],
                'service' => [
                    $this->getClassNamespace($name, '\\App\\Services', '\\') . $this->getSingularClassName($name, 'ServiceInterface::class'),
                    $this->getClassNamespace($name, '\\App\\Services', '\\') . $this->getSingularClassName($name, 'Service::class')
                ],
            ]
        ];

        $this->info("Remember to add the following entry to your service-layer.php config:");
        $this->newLine();

        $this->line(
            collect($serviceLayerArray)
                ->map(function ($serviceLayer, $layerName) {
                    return sprintf("'%s' => [\n%s\n],", $layerName,
                        collect($serviceLayer)
                            ->map(function ($layerClasses, $layerType) {
                                return sprintf("\t'%s' => [%s],", $layerType, implode(', ', $layerClasses));
                            })->join("\n")
                    );
                })->join('')
        );

        $this->newLine();
        $this->info("Done.");
        return Command::SUCCESS;
    }



    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @param string|null $suffix
     * @return string
     */
    public function getSingularClassName($name, ?string $suffix = null): string
    {
        return ucwords(Pluralizer::singular($name)) . $suffix;
    }

    public function getClassNamespace(string $name, ?string $prefix = null, ?string $suffix = null): string
    {
        $name = ucwords(Pluralizer::plural($name));
        $prefix = !blank($prefix) ? "{$prefix}\\" : null;

        return "{$prefix}{$name}" . (!blank($suffix) ? $suffix : null);
    }

    /**
     * Return the stub file path
     *
     * @param string $stubName
     * @return string
     */
    public function getStubPath(string $stubName): string
    {
        return __DIR__ . "/../../../stubs/{$stubName}";
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables(): array
    {
        return [
            'NAMESPACE'         => 'App\\',
            'CLASS_NAME'        => $this->getSingularClassName(
                $this->argument('name')
            ),
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @param string $stubName
     * @param array $stubVariables
     * @return string|array|bool
     */
    public function getSourceFile(string $stubName, array $stubVariables = []): string|array|bool
    {
        return $this->getStubContents(
            $this->getStubPath($stubName), array_merge($this->getStubVariables(), $stubVariables)
        );
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     *
     * @return array|false|string|string[]
     */
    public function getStubContents($stub, array $stubVariables = []): array|false|string
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('%'.$search.'%' , $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get the full path of generate class
     *
     * @param string $dir
     * @param string|null $suffix
     * @return string
     */
    public function getSourceFilePath(string $dir, ?string $suffix = null, ?string $name = null, bool $withPsr4 = true): string
    {
        $name = !blank($name) ? $name : $this->argument('name');

        $append = $withPsr4 ? $this->getPsr4Path($dir) : $dir;

        return base_path("app/" . $append) . '/' . $this->getSingularClassName($name) . $suffix . '.php';
    }

    /**
     * Get PSR4 path.
     *
     * @param string $dir
     * @return string
     */
    public function getPsr4Path(string $dir): string
    {
        $psr4 = ucwords(Pluralizer::plural($this->argument('name')));

        return "{$dir}/{$psr4}";
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     *
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
