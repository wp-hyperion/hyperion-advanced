<?php

namespace Hyperion\Enum;

/**
 * Enum représentant les type de dépendance possible.
 * Tag : Ensemble de classe qui ont été tagués avec ce label ci
 * Alias : Module présent qui sera la dépendance.
 */
enum PartType
{
    case Tag;
    case Alias;
}