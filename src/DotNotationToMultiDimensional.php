<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use InvalidArgumentException;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Property\DotNotationFormat;

use function ctype_digit;
use function explode;
use function preg_split;
use function sprintf;

class DotNotationToMultiDimensional
{
    private const SEP = '.';

    private DotNotationFormat $dotNotationFormat;

    /** @var array<string, mixed> */
    private array $dottedArray;

    /**
     * Dot notation formatted array keys with value to a multidimensional array
     *
     * Array key format examples:
     * DotNotationFormat::JAVASCRIPT = manufacturer.employeeList[1].employeeName
     * DotNotationFormat::EXPLODE = manufacturer.employeeList.1.employeeName
     *
     * @param array<string, mixed> $dottedArray
     */
    public function __construct(array $dottedArray, DotNotationFormat $dotNotationFormat = DotNotationFormat::JAVASCRIPT)
    {
        if (empty($dottedArray)) {
            throw new InvalidArgumentException('Dotted array can not be empty');
        }

        $this->dottedArray       = $dottedArray;
        $this->dotNotationFormat = $dotNotationFormat;
    }

    /**
     * @throws InvalidKeyException
     *
     * @return array<int|string, mixed>
     */
    public function convert(): array
    {
        $resultArray = [];

        foreach ($this->dottedArray as $dottedKey => $value) {
            if ($this->dotNotationFormat === DotNotationFormat::JAVASCRIPT) {
                $keyList = preg_split(
                    pattern: '/\.|\[(\d+)]/',
                    subject: $dottedKey,
                    flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
                );
            } else {
                $keyList = explode(self::SEP, $dottedKey);
            }

            if ($keyList === false) {
                throw new InvalidKeyException(sprintf('Unable to split supplied array key: "%s"', $dottedKey));
            }

            $this->setValueInResultArray($resultArray, $keyList, $value);
        }

        return $resultArray;
    }

    /**
     * @param string[]                 $keyList
     * @param array<int|string, mixed> $resultArray
     */
    private function setValueInResultArray(array &$resultArray, array $keyList, mixed $value): void
    {
        foreach ($keyList as $key) {
            // create reference to positions in our array
            $key         = ctype_digit($key) ? (int) $key : $key;
            $resultArray = &$resultArray[$key]; /** @phpstan-ignore-line */
        }

        // we have looped through all array keys and $resultArray reference is now pointing to the final position within the array
        $resultArray = $value;
    }
}
