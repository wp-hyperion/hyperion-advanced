<?php

namespace Hyperion\Toolbox;

use Composer\Autoload\ClassLoader;

class Helper
{
    private static ?ClassLoader $autoloader = null;

    public static function apcuEnabled(): bool
    {
        return function_exists('apcu_enabled') && apcu_enabled();
    }

    public static function setAutoloader(string $path): void
    {
        self::$autoloader = include $path;
    }

    public static function realPathForNamespace(string $namespace) : ?string
    {
        if (is_null(self::$autoloader)) {
            throw new \Exception("Autoloader non chargé");
        }

        $psr4Prefixes = self::$autoloader->getPrefixesPsr4();
        $rootNamespaceLength = strlen($namespace) - strpos(strrev($namespace), '\\');
        $rootNamespace = substr($namespace, 0, $rootNamespaceLength - 1);
        if (array_key_exists($rootNamespace."\\", $psr4Prefixes)) {
            return $psr4Prefixes[$rootNamespace."\\"][0];
        }

        return null;
    }

    public static function createToken(?int $length = null): string
    {
        $rawToken = sha1(random_int(1, 90000));
        return $length ? substr($rawToken, 1, $length) : $rawToken;
    }

    /**
     * Renvoie l'arborescence des fichiers à partir du basePath
     * @todo: Probablement optimisable en forkant le process à chaque nouveau répertoire !
     *
     * @param string $basePath
     * @param string $subdirectories
     * @return array
     */
    public static function dirToArray(
        string $basePath,
        string $subdirectories = "",
        string $fileType = null
    ): array {
        $result = array();
        $cdir = scandir($basePath);
        foreach ($cdir as $value) {
            if (in_array($value, array(".", ".."))) {
                continue;
            }

            $basePath_ = $basePath . DIRECTORY_SEPARATOR . $value;
            $value_ = empty($subdirectories) ? $value : $subdirectories . "/$value";
            if ($fileType && pathinfo($value_, PATHINFO_EXTENSION) !== $fileType) {
                continue;
            }
            $result[] = is_dir($basePath_) ? self::dirToArray($basePath_, $value_) : $value_;
        }

        return $result;
    }
}
