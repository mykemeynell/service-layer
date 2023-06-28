<?php

namespace ServiceLayer\Console\Commands\Concerns;

use Illuminate\Support\Str;

trait CreatesServices
{
    private function createService(): void
    {
        $this->createServiceClass();
        $this->createServiceInterface();
    }

    private function createServiceClass(): void
    {
        $servicePath = $this->getSourceFilePath('Services', 'Service');
        $this->makeDirectory(dirname($servicePath));

        $contents = $this->getSourceFile('services/service.stub', [
            'NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Services"
            ),
            'CLASS_NAME' => $this->getSingularClassName(
                $this->argument('name') . 'Service'
            ),
            'REPOSITORY_INTERFACE_NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Repositories"
            ),
            'REPOSITORY_INTERFACE_NAME' => $this->getSingularClassName($this->argument('name'), 'RepositoryInterface'),

            'INTERFACE_NAMESPACE' => $this->getClassNamespace(
                    name: $this->argument('name'),
                    prefix: "App\\Services"
            ),
            'INTERFACE_NAME' => $this->getSingularClassName($this->argument('name')) . 'ServiceInterface',
        ]);

        if (!$this->files->exists($servicePath)) {
            $this->files->put($servicePath, $contents);
            $this->info("File: {$servicePath} created");
        }

        $baseServicePath = $this->getSourceFilePath(dir: 'Services', name: 'Service', withPsr4: false);

        if(!file_exists($baseServicePath)) {
            $contents = $this->getSourceFile('services/base-service.stub', [
                'NAMESPACE' => $this->getClassNamespace(
                    name: "App\\Services",
                ),
            ]);

            $this->files->put($baseServicePath, $contents);
        }
    }

    private function createServiceInterface(): void
    {
        $serviceInterfacePath = $this->getSourceFilePath('Services', 'ServiceInterface');
        $this->makeDirectory(dirname($serviceInterfacePath));

        $contents = $this->getSourceFile('services/service-interface.stub', [
            'CLASS_NAME' => $this->getSingularClassName(
                $this->argument('name') . 'ServiceInterface'
            ),
            'NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Services"
            ),
            'INTERFACE_NAMESPACE' => $this->getClassNamespace(
                    name: 'Services',
                    prefix: "App"
                ) . "\\ServiceInterface",
            'INTERFACE_NAME' => 'ServiceInterface'
        ]);

        if (!$this->files->exists($serviceInterfacePath)) {
            $this->files->put($serviceInterfacePath, $contents);
            $this->info("File: {$serviceInterfacePath} created");
        }

        $baseServiceInterfacePath = $this->getSourceFilePath(dir: 'Services', suffix: 'Interface', name: 'Service', withPsr4: false);

        if(!file_exists($baseServiceInterfacePath)) {
            $contents = $this->getSourceFile('services/base-service-interface.stub', [
                'NAMESPACE' => $this->getClassNamespace(
                    name: "App\\Services",
                ),
            ]);

            $this->files->put($baseServiceInterfacePath, $contents);
        }
    }
}
