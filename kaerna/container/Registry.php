<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-09-22 20:29
 */

namespace DHP\kaerna\container;

use DHP\kaerna\interfaces\RegistryInterface;

class Registry implements RegistryInterface
{
    private $roots = [];
    private $extensions = [];
    private $pathsToExclude = [];

    public function addRoots(array $roots): RegistryInterface
    {
        $this->roots = array_merge($this->roots, $roots);
        return $this;
    }

    public function addExtensions(array $extensions): RegistryInterface
    {
        $this->extensions = array_merge($this->extensions, $extensions);
        return $this;
    }

    public function addPathsToExclude(array $path): RegistryInterface
    {
        $this->pathsToExclude = array_merge($this->pathsToExclude, $path);
        return $this;
    }

    public function getRegistry(): array
    {
        $return = [];
        $files  = $this->getFiles();
        foreach ($files as $file) {
            $return = array_merge_recursive($return, $this->getClassesInFile($file));
        }

        return $return;
    }

    private function getFiles(): array
    {
        $files          = [];
        $pathsToExclude = $this->pathsToExclude;
        $extensions     = $this->extensions;
        foreach ($this->roots as $root) {
            $dir = new \RecursiveDirectoryIterator(
                $root,
                \FilesystemIterator::FOLLOW_SYMLINKS
            );

            $filter = new \RecursiveCallbackFilterIterator(
                $dir,
                function ($current) use ($pathsToExclude, $extensions) {
                    if ($current->getFilename()[0] === '.') {
                        return false;
                    }
                    if ($current->isDir()) {
                        return !in_array($current->getFilename(), $pathsToExclude);
                    } else {
                        return in_array(
                            strtolower(pathinfo($current->getFilename(), \PATHINFO_EXTENSION)),
                            $extensions
                        );
                    }
                }
            );

            $iterator = new \RecursiveIteratorIterator($filter);
            foreach ($iterator as $info) {
                $files[] = $info->getPathname();
            }
        }

        return $files;
    }

    private function getClassesInFile(string $filePath): ?array
    {
        $return = null;
        if (file_exists($filePath)) {
            $tokens    = token_get_all(file_get_contents($filePath));
            $namespace = '\\';
            $return    = [];
            $interface = null;
            $fqClass   = null;
            $use       = array();
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index])) {
                    continue;
                }
                $orig_token = $tokens[$index];
                $token      = $tokens[$index];
                if (!is_array($token)) {
                    continue;
                }
                switch ($token[0]) {
                    case T_CLASS:
                        $index   += 2; // Skip class keyword and whitespace
                        $fqClass = $namespace . '\\' . $tokens[$index][1];
                        break;
                    case T_NAMESPACE:
                    case T_IMPLEMENTS:
                    case T_USE:
                        $index += 2;
                        $val   = null;
                        while (isset($tokens[$index]) && is_array($tokens[$index])) {
                            $val .= $tokens[$index++][1];
                        }
                        $val = trim($val);
                        switch ($orig_token[0]) {
                            case \T_NAMESPACE:
                                $namespace = $val;
                                break;
                            case \T_IMPLEMENTS:
                                if (isset($use[$val])) {
                                    $interface = $use[$val];
                                } else {
                                    $interface = $namespace . '\\' . $val;
                                }
                                $interface = '\\' . $interface;
                                if (!isset($return[$interface])) {
                                    $return[$interface] = [];
                                }
                                $return[$interface][] = '\\' . $fqClass;
                                $fqClass              = null;
                                break;
                            case \T_USE:
                                $use[$val]                   = $val;
                                $use[$tokens[$index - 1][1]] = $val;
                                break;
                        }
                        break;
                }
            }
            return $return;
        }
    }
}