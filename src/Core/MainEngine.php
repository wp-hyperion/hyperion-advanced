<?php

namespace Hyperion\Core;

use Digilist\DependencyGraph\DependencyGraph;
use Digilist\DependencyGraph\DependencyNode;
use Hyperion\Hyperion;
use Hyperion\Interfaces\EnginePlugable;
use Hyperion\Model\AutoloadedComponent;
use Hyperion\Model\Module;
use League\Container\Container;

/**
 * Cette classe représente l'ensemble des modules qui ont été chargé pour le moteur.
 * C'est elle qui va avoir la responsabilité de les ordonner et de les charger.
 */

class MainEngine
{
    private Container $container;
    /** @var Module[] */
    private array $modules = [];
    /** @var AutoloadedComponent[] */
    private array $autoloadedComponents = [];

    public function __construct()
    {
        $this->container = new Container();
        add_action(Hyperion::LOAD_CONTAINER_EVENT, [$this, 'fullyLoadContainer'], 10, 1);
    }


    /**
     * Enregistre un nouveau module du moteur
     */
    public function addModule(Module $module) : void
    {
        if (array_key_exists($module->getAlias(), $this->modules)) {
            throw new \Exception("Ce module a déjà été défini (".$module->getAlias().")");
        }

        $this->modules[$module->getAlias()] = $module;
    }

    /**
     * Enregistre un nouveau tag regroupant un ensemble de classe
     * nb : On peut avoir le même tag pour plusieurs namespace.
     */
    public function addAutoloadedComponent(AutoloadedComponent $autoloadedComponent) : void
    {
        if (array_key_exists($autoloadedComponent->getAlias(), $this->autoloadedComponents)) {
            throw new \Exception("Ce component a déjà été défini (".$autoloadedComponent->getAlias().")");
        }

        $tag = $autoloadedComponent->getTag();
        if (array_key_exists($tag, $this->modules)) {
            throw new \Exception("Le label du tag ($tag) correspond à l'alias d'un module. Ceci n'est pas permis pour le moment.");
        }

        $this->autoloadedComponents[$autoloadedComponent->getAlias()] = $autoloadedComponent;
    }

    /**
     * Charge l'ensemble des modules dans le container pour mise en service opérationnelle.
     *
     * @throws \Digilist\DependencyGraph\CircularDependencyException
     */
    public function fullyLoadContainer(ClassTreeMapper $classTreeMapper) : void
    {
        array_map(
            function (DependencyNode $dependencyNode) use ($classTreeMapper) {
                $this->addToContainer($dependencyNode->getElement(), $classTreeMapper);
            },
            $this->resolveModulesLoadingOrder()
        );
    }

    /**
     * Permet de populer le container avec un module ou
     * l'ensemble des classes définies dans le namespace de l'autoloadedComponent.
     *
     * @param EnginePlugable $enginePlugable
     * @return void
     */
    private function addToContainer(EnginePlugable $enginePlugable, ClassTreeMapper $classTreeMapper): void
    {
        if ($enginePlugable instanceof Module) {
            $definition = $this->container->add($enginePlugable->getAlias(), $enginePlugable->getSrc(), $enginePlugable->isShared());
        } elseif ($enginePlugable instanceof AutoloadedComponent) {
            $namespaceClasses = $classTreeMapper->getClassesFromNamespace($enginePlugable->getNamespace());

            if (empty($namespaceClasses)) {
                return;
            }

            foreach ($namespaceClasses as $namespace) {
                $definition = $this->container->add($namespace, $namespace, $enginePlugable->isShared());
                $definition->addTag($enginePlugable->getTag());
            }
        }

        foreach ($enginePlugable->getParts() as $part) {
            $definition->addArgument($this->container->get($part->getName()));
        }
    }

    /**
     * Cette fonction va réorganiser l'ensemble des dépendances modulaires afin d'ordonner le chargement
     * afin de s'assurer que les modules dont dépendent d'autres soient chargés en premier.
     *
     * @return DependencyNode[]
     * @throws \Digilist\DependencyGraph\CircularDependencyException
     */
    private function resolveModulesLoadingOrder() : array
    {
        $graph = new DependencyGraph();
        $dependencyNodes = [];

        // Création des dependencyNodes sur les modules
        foreach ($this->modules as $alias => $moduleInstance) {
            $dependencyNodes[ $alias ] = new DependencyNode($moduleInstance, $alias);
        }

        // Création des dependencyNodes sur les autoloadedComponents
        // Un même tag peut regrouper plusieurs namespace d'ou le tableau.
        foreach ($this->autoloadedComponents as $autoloadedComponentInstance) {
            $tag = $autoloadedComponentInstance->getTag();
            $dependencyNodes[ $tag ][] = new DependencyNode($autoloadedComponentInstance, $tag);
        }

        // On rajoute les relations entre eux
        // Pour les modules...
        foreach ($this->modules as $alias => $moduleInstance) {
            foreach ($moduleInstance->getParts() as $part) {
                if (!array_key_exists($part->getName(), $dependencyNodes)) {
                    throw new \Exception("Erreur lors de la mise en place de la relation : " . $part->getName() . " : Element non existant");
                }
                $graph->addDependency($dependencyNodes[$alias], $dependencyNodes[$part->getName()]);
            }
        }

        // Pour les classes en autoload, on prend le parti que pour un même tag on doit avoir les même dépendances.
        // Ainsi on prend la première occurence du tableau sous la clef tag puisque normalement elles sont toutes les memes.
        foreach ($dependencyNodes as $autoloadedComponents) {
            /** @var AutoloadedComponent $autoloadedComponent */
            $firstAutoloadedComponent = current($autoloadedComponents);
            foreach ($firstAutoloadedComponent->getParts() as $part) {
                if (!array_key_exists($part->getName(), $dependencyNodes)) {
                    throw new \Exception("Erreur lors de la mise en place de la relation : " . $part->getName() . " : Element non existant");
                }
                foreach ($autoloadedComponents as $autoloadedComponent) {
                    $graph->addDependency($dependencyNodes[$autoloadedComponent->getAlias()], $dependencyNodes[$part->getName()]);
                }
            }
        }

        return $graph->resolve();
    }
}