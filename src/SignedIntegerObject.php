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

final class SignedIntegerObject extends AbstractCBORObject
{
    private const MAJOR_TYPE = 0b001;

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
        if ($value >= 0) {
            throw new \InvalidArgumentException('The value must be a negative integer.');
        }

        $computed_value = $value - 1;

        switch (true) {
            case $computed_value < 24:
                $ai = $computed_value;
                $data = null;
                break;
            case $computed_value < (int)base_convert('FF', 16, 10):
                $ai = 24;
                $data = \Safe\hex2bin(str_pad(dechex($computed_value), 2, '0', STR_PAD_LEFT));
                break;
            case $computed_value < (int)base_convert('FFFF', 16, 10):
                $ai = 25;
                $data = \Safe\hex2bin(str_pad(dechex($computed_value), 4, '0', STR_PAD_LEFT));
                break;
            case $computed_value < (int)base_convert('FFFFFFFF', 16, 10):
                $ai = 26;
                $data = \Safe\hex2bin(str_pad(dechex($computed_value), 8, '0', STR_PAD_LEFT));
                break;
            default:
                throw new \InvalidArgumentException('Out of range. Please use NegativeBigIntegerTag tag with ByteStringObject object instead.');
        }

        return new self($ai, $data);
    }

    public function getValue(): string
    {
        return $this->getNormalizedData();
    }

    public function getNormalizedData(bool $ignoreTags = false): string
    {
        if (null === $this->data) {
            return (string) (-1 - $this->additionalInformation);
        }

        return (string) (-1 - bindec($this->data));
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
