<?php

trait ArrayFlatten
{
    function array_flatten($items)
    {
        return array_reduce($items,
            fn($carry, $item) => is_array($item)
                ? [...$carry, ...$this->array_flatten($item)]
                : [...$carry, $item]
            , []
        );
    }
}
