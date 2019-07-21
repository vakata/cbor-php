<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace CBOR;

final class UnsignedIntegerObject extends AbstractCBORObject
{
    private const MAJOR_TYPE = 0b000;

    /**
     * @var string|null
     */
    private $data;

    public function __construct(int $additionalInformation, ?string $data)
    {
        parent::__construct(self::MAJOR_TYPE, $additionalInformation);
        $this->data = $data;
    }

    public static function createObjectForValue(int $additionalInformation, ?string $data): self
    {
        return new self($additionalInformation, $data);
    }

    public static function createFromGmpValue($value): self
    {
        $value = (int)$value;
        if ($value < 0) {
            throw new \InvalidArgumentException('The value must be a positive integer.');
        }

        switch (true) {
            case $value < 24:
                $ai = $value;
                $data = null;
                break;
            case $value < (int)base_convert('FF', 16, 10):
                $ai = 24;
                $data = \Safe\hex2bin(str_pad(dechex($value), 2, '0', STR_PAD_LEFT));
                break;
            case $value < (int)base_convert('FFFF', 16, 10):
                $ai = 25;
                $data = \Safe\hex2bin(str_pad(dechex($value), 4, '0', STR_PAD_LEFT));
                break;
            case $value < (int)base_convert('FFFFFFFF', 16, 10):
                $ai = 26;
                $data = \Safe\hex2bin(str_pad(dechex($value), 8, '0', STR_PAD_LEFT));
                break;
            default:
                throw new \InvalidArgumentException('Out of range. Please use PositiveBigIntegerTag tag with ByteStringObject object instead.');
        }

        return new self($ai, $data);
    }

    public function getMajorType(): int
    {
        return self::MAJOR_TYPE;
    }

    public function getAdditionalInformation(): int
    {
        return $this->additionalInformation;
    }

    public function getValue(): string
    {
        return $this->getNormalizedData();
    }

    public function getNormalizedData(bool $ignoreTags = false): string
    {
        if (null === $this->data) {
            return \strval($this->additionalInformation);
        }

        return base_convert(bin2hex($this->data), 16, 10);
    }

    public function __toString(): string
    {
        $result = parent::__toString();
        if (null !== $this->data) {
            $result .= $this->data;
        }

        return $result;
    }
}
