<?php

namespace Hyperion\Model;

class ClassTreeMapperPathModel
{
    private string $namespace;
    private string $basePath;

    public function __construct(string $namespace, string $basePath)
    {
        $this->namespace = $namespace;
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}