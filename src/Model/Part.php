<?php

namespace Hyperion\Model;

use Hyperion\Enum\PartType;

/**
 * Cette classe représente une dépendance d'un module.
 * Cette dépendance peut soit être de type :
 *  - alias quand il s'agit d'une référence à un autre module
 *  - tag quand il s'agit d'un ensemble de classe qui sont tagué dans l'autoload.
 */

class Part
{
    private string $name;
    private PartType $type;

    public function __construct(string $name, PartType $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): PartType
    {
        return $this->type;
    }
}