<?php

namespace ServiceLayer\Console\Commands\Concerns;

use Illuminate\Support\Str;

trait CreatesRepositories
{
    private function createRepository(): void
    {
        $this->createRepositoryClass();
        $this->createRepositoryInterface();
    }

    private function createRepositoryClass(): void
    {
        $repositoryPath = $this->getSourceFilePath('Repositories', 'Repository');
        $this->makeDirectory(dirname($repositoryPath));

        $contents = $this->getSourceFile('repositories/repository.stub', [
            'NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Repositories"
            ),
            'CLASS_NAME' => $this->getSingularClassName(
                $this->argument('name') . 'Repository'
            ),
            'MODEL_INTERFACE_NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Models"
            ),
            'MODEL_INTERFACE_NAME' => $this->getSingularClassName($this->argument('name')) . 'Interface',

            'INTERFACE_NAMESPACE' => $this->getClassNamespace(
                    name: $this->argument('name'),
                    prefix: "App\\Repositories"
            ),
            'INTERFACE_NAME' => $this->getSingularClassName($this->argument('name')) . 'RepositoryInterface',
        ]);

        if (!$this->files->exists($repositoryPath)) {
            $this->files->put($repositoryPath, $contents);
            $this->info("File: {$repositoryPath} created");
        }

        $baseRepositoryPath = $this->getSourceFilePath(dir: 'Repositories', name: 'Repository', withPsr4: false);

        if(!file_exists($baseRepositoryPath)) {
            $contents = $this->getSourceFile('repositories/base-repository.stub', [
                'NAMESPACE' => $this->getClassNamespace(
                    name: "App\\Repositories",
                ),
            ]);

            $this->files->put($baseRepositoryPath, $contents);
        }
    }

    private function createRepositoryInterface(): void
    {
        $repositoryInterfacePath = $this->getSourceFilePath('Repositories', 'RepositoryInterface');
        $this->makeDirectory(dirname($repositoryInterfacePath));

        $contents = $this->getSourceFile('repositories/repository-interface.stub', [
            'CLASS_NAME' => $this->getSingularClassName(
                $this->argument('name') . 'RepositoryInterface'
            ),
            'NAMESPACE' => $this->getClassNamespace(
                name: $this->argument('name'),
                prefix: "App\\Repositories"
            ),
            'INTERFACE_NAMESPACE' => $this->getClassNamespace(
                    name: 'Repositories',
                    prefix: "App"
                ) . "\\RepositoryInterface",
            'INTERFACE_NAME' => 'RepositoryInterface'
        ]);

        if (!$this->files->exists($repositoryInterfacePath)) {
            $this->files->put($repositoryInterfacePath, $contents);
            $this->info("File: {$repositoryInterfacePath} created");
        }

        $baseRepositoryInterfacePath = $this->getSourceFilePath(dir: 'Repositories', suffix: 'Interface', name: 'Repository', withPsr4: false);

        if(!file_exists($baseRepositoryInterfacePath)) {
            $contents = $this->getSourceFile('repositories/base-repository-interface.stub', [
                'NAMESPACE' => $this->getClassNamespace(
                    name: "App\\Repositories",
                ),
            ]);

            $this->files->put($baseRepositoryInterfacePath, $contents);
        }
    }
}
