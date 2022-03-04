<?php

namespace Hyperion\Model;

use Hyperion\Interfaces\EnginePlugable;

/**
 * Cette classe représente un module que l'on mettra à disposition de tous via le container.
 * Elle peut avoir des dépendances qui seront chargées dans l'ordre. Ces dépendances sont les parts.
 */

class Module implements EnginePlugable
{
    private string $alias;
    private string $src;
    /** @var Part[] */
    private array $parts = [];
    private bool $isShared = true;

    public function __construct(string $alias, string $classNamespace)
    {
        $this->alias = $alias;
        if(!class_exists($classNamespace)) {
            throw new \Exception("La classe indiqué pour le module n'existe pas (".$classNamespace.")");
        }
        $this->src = $classNamespace;
    }

    public function addPart(Part $part)
    {
        $id = md5(serialize($part));
        if(array_key_exists($id, $this->parts)) {
            throw new \Exception("Cette dépendance a déjà été renseignée");
        }

        $this->parts[$id] = $part;
    }

    public function setIsShared(bool $value)
    {
        $this->isShared = $value;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function isShared(): bool
    {
        return $this->isShared;
    }
}