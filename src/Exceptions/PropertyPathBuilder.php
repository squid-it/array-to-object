<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Exceptions;

class PropertyPathBuilder
{
    /**
     * @param array<string, int|null> $pathData
     */
    public static function build(array $pathData, string $propertyName): string
    {
        if (empty($pathData) === true) {
            return $propertyName;
        }

        $finalPath = [];

        foreach ($pathData as $path => $arrayIndex) {
            if ($arrayIndex === null) {
                $finalPath[] = $path;

                continue;
            }

            $finalPath[] = $path . '[' . $arrayIndex . ']';
        }

        $finalPath[] = $propertyName;

        return implode('.', $finalPath);
    }
}
