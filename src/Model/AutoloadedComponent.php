<?php

namespace Hyperion\Model;

use Hyperion\Interfaces\EnginePlugable;

/**
 * Les autoloaded components sont les classes qui seront regroupé sous un même tag.
 * Elles appartiennent au même namespace et la liste de ces dernières sera passé en paramètre du constructeur.
 */

class AutoloadedComponent implements EnginePlugable
{
    private string $alias;
    private string $namespace;
    private string $tag;
    /** @var Part[] */
    private array $parts = [];

    public function __construct(string $alias, string $namespace, string $tag)
    {
        $this->alias = $alias;
        $this->namespace = $namespace;
        $this->tag = $tag;
    }

    public function addPart(Part $part)
    {
        $id = md5(serialize($part));
        if(array_key_exists($id, $this->parts)) {
            throw new \Exception("Cette dépendance a déjà été renseignée");
        }

        $this->parts[$id] = $part;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getParts(): array
    {
        return $this->parts;
    }
}