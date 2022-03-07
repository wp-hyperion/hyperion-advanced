<?php

namespace Hyperion\Core;

use Hyperion\Model\ClassTreeMapperPathModel;
use Hyperion\Toolbox\Helper;
use ReflectionClass;

/**
 * Cette classe a pour but de créer une arbre des classes d'un namespace donné.
 * L'idée est de trouver très rapidement toutes les classes enfants d'un namespace parent.
 * On peut spécifier plusieurs chemins le wordpress lui-même dans lequel le plugin est embarqué peut avoir besoin
 * d'autoloading sur par exemple les entités (en spécifiant ses propres entités).
 */
class ClassTreeMapper
{
    private const TREE_CACHE_KEY = 'classtreemapper_tree_apcu_cache';
    private array $tree = [];
    /** @var ClassTreeMapperPathModel[] */
    private static array $classTreeMapperPaths = [];

    /**
     * Rajoute un namespace à prendre en compte pour l'arbre
     *
     * @param ClassTreeMapperPathModel $classTreeMapperPathModel
     * @throws \Exception
     */
    public static function addClassNamespace(ClassTreeMapperPathModel $classTreeMapperPathModel): void
    {
        $id = md5(serialize($classTreeMapperPathModel));
        if (array_key_exists($id, self::$classTreeMapperPaths)) {
            throw new \Exception("Ce classTreeMapperPath(".$classTreeMapperPathModel->getNamespace().") a déjà été ajouté.");
        }
        self::$classTreeMapperPaths[$id] = $classTreeMapperPathModel;
    }

    /**
     * Récupère les classes à partir du namespace
     *
     * @param string $namespace
     * @return array
     */
    public function getClassesFromNamespace(string $namespace): array
    {
        if (empty($this->tree)) {
            $this->launchBuild();
        }

        return $this->tree[$namespace] ?: [];
    }

    private function launchBuild() : void
    {
        if (apcu_exists(self::TREE_CACHE_KEY)) {
            $this->tree = apcu_fetch(self::TREE_CACHE_KEY);
            return;
        }


        $trees = [];
        foreach (self::$classTreeMapperPaths as $classTreeMapperPath) {
            $trees[] = $this->buildClassTreeMap($classTreeMapperPath->getNamespace(), $classTreeMapperPath->getBasePath());
        }

        $this->tree = array_merge([], ...$trees);

        apcu_add(self::TREE_CACHE_KEY, $this->tree);
    }

    /**
     * Construction de l'arbre
     */
    private function buildClassTreeMap(string $namespace, string $basePath): array
    {
        $files = Helper::dirToArray($basePath);
        $tree = [$namespace => []];
        array_walk_recursive($files, function ($filepath) use ($namespace, &$tree) {
            $namespacedClass = substr($filepath, 0, strpos($filepath, "."));
            $pieces = explode('/', $namespacedClass);
            $node = &$tree[$namespace];

            foreach ($pieces as $index => $piece) {
                if ($index === array_key_last($pieces)) {
                    $completeNamespace = $namespace . "\\" . str_replace("/", "\\", $namespacedClass);
                    if ($this->isClassInstantiable($completeNamespace)) {
                        $node[] = $completeNamespace;
                    }
                    continue;
                }
                if (!array_key_exists($piece, $node)) {
                    $node[$piece] = [];
                }
                $node = &$node[$piece];
            }
        });

        return $tree;
    }

    /**
     * Renvoie vrai si la classe, représentée par son namespace, est instanciable
     *
     * @param string $namespace
     * @return bool
     */
    private function isClassInstantiable(string $namespace): bool
    {
        try {
            $reflectionClass = new ReflectionClass($namespace);
            return $reflectionClass->isInstantiable();
        } catch (\Exception $exception) {
            return false;
        }
    }
}
