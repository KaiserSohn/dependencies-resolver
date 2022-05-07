<?php

namespace DependenciesResolver;

/**
 * Class DependenciesResolver
 * @package DependenciesResolver
 */
class DependenciesResolver
{
    /**
     * @param array $source
     * @param null $currentElement
     * @param null $rootKey
     *
     * @return array
     *
     * @throws LoopException
     */
    private function treeResolver(array $source, $currentElement = null, $rootKey = null): array
    {
        $result = [];

        foreach ($source as $key => $values) {
            if (($currentElement && $currentElement != $key)) {
                continue;
            }

            if (is_array($values)) {
                foreach ($values as $value) {
                    if ($value === $currentElement) {
                        continue;
                    }

                    if ($value === $rootKey) {
                        throw new LoopException('Loop detected');
                    }

                    if (isset($source[$value])) {
                        if ($currentElement) {
                            $result[$value] = $this->treeResolver($source, $value, $rootKey);
                        } else {
                            $result[$key][$value] = $this->treeResolver($source, $value, $key);
                        }
                    } elseif ($currentElement) {
                        $result[] = $value;
                    } else {
                        $result[$key][] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $source
     *
     * @param array $loops
     *
     * @param null $currentElement
     * @param null $rootKey
     */
    private function loopsResolver(array $source, &$loops = [], $currentElement = null, $rootKey = null)
    {
        foreach ($source as $key => $values) {
            if (($currentElement && $currentElement != $key)) {
                continue;
            }

            try {
                if (is_array($values)) {
                    foreach ($values as $value) {
                        if ($value === $rootKey) {
                            throw new LoopException('Loop detected');
                        }

                        if (isset($source[$value])) {
                            if ($currentElement) {
                                $this->loopsResolver($source, $loops, $value, $rootKey);
                            } else {
                                $this->loopsResolver($source, $loops, $value, $key);
                            }
                        }
                    }
                }
            } catch (LoopException $loopException) {
                $loops[] = $key;
            }
        }
    }

    /**
     * @param array $array
     *
     * @return array|null
     */
    public function tree(array $array): ?array
    {
        try {
            return $this->treeResolver($array);
        } catch (LoopException $loopException) {
            return null;
        }
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function manyInRelations(array $array): array
    {
        $result = [];
        $allEntries = [];

        foreach ($array as $key => $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $allEntries[] = $value;
                }
            }
        }
        $needElements = array_filter(array_count_values($allEntries), function ($count) {
            return $count > 1;
        });

        foreach ($array as $key => $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (isset($needElements[$value])) {
                        $result[$value][] = $key;
                    }
                }
            }
        }

        return array_map(function ($item) {
            return array_unique($item);
        }, $result);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function loops(array $array): ?array
    {
        $this->loopsResolver($array, $loops);

        return $loops;
    }
}
