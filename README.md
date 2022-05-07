# dependencies-resolver

Примеры

```
$source = [
    'a' => ['b','c','d','d'],
    'b' => ['d','e'],
    'c' => ['f','e'],
    'e' => ['g'],
];

$resolver =  new \DependenciesResolver\DependenciesResolver();

$resolver->tree($source);

$resolver->manyInRelations($source);

$resolver->loops($source);
```

**Получение дерева зависимостей**

_В случае, если была обнаружена петля, то вернет_ `null`

`$resolver->tree($source)`

**Получение зависимостей, у которых более чем одна входящая**

`$resolver->manyInRelations($source)`

**Получение списка петель**

_Если петель не было в массиве, вернет_ `null`

`$resolver->loops($source)`
