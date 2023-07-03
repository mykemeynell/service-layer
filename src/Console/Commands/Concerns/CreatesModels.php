<?php

namespace ServiceLayer\Console\Commands\Concerns;

use Illuminate\Support\Str;

trait CreatesModels
{
    private function createModel(): void
    {
        $modelInterfacePath = $this->getSourceFilePath('Models', 'Interface');
        $this->makeDirectory(dirname($modelInterfacePath));

        $contents = $this->getSourceFile('models/model-interface.stub', [
            'CLASS_NAME' => $this->getSingularClassName(
                $this->argument('name') . 'Interface'
            ),
            'NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Models"
            ),
            'INTERFACE_NAMESPACE' => $this->getClassNamespace(
                    name: 'Models',
                    prefix: "App"
                ) . "\\ModelInterface",
            'INTERFACE_NAME' => 'ModelInterface'
        ]);

        if (!$this->files->exists($modelInterfacePath)) {
            $this->files->put($modelInterfacePath, $contents);
            $this->info("File: {$modelInterfacePath} created");
        }

        $baseModelInterfacePath = $this->getSourceFilePath(dir: 'Models', suffix: 'Interface', name: 'Model', withPsr4: false);

        if(!file_exists($baseModelInterfacePath)) {
            $contents = $this->getSourceFile('models/base-model-interface.stub', [
                'NAMESPACE' => $this->getClassNamespace(
                    name: "App\\Models",
                ),
            ]);

            $this->files->put($baseModelInterfacePath, $contents);
        }

        $modelPath = $this->getSourceFilePath('Models');

        if(!$this->files->exists($modelPath)) {
            $modelName = str_replace('.php', '', Str::afterLast($modelPath, 'Models/'));
            $this->call('make:model', ['name' => $modelName]);

            $modelInterfaceNamespace = $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Models"
            );

            $modelInterfaceName = $this->getSingularClassName(
                name: $this->argument('name'),
                suffix: 'Interface'
            );

            $baseModel = config('service-layer.base_model', \Illuminate\Database\Eloquent\Model::class);

            $this->files->replaceInFile('extends Model', "extends Model implements {$modelInterfaceName}", $modelPath);
            $this->files->replaceInFile('use Illuminate\Database\Eloquent\Model;', "use {$baseModel} as Model;\nuse {$modelInterfaceNamespace}\\{$modelInterfaceName};", $modelPath);
        }
    }
}
