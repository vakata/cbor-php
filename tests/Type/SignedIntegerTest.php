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

namespace CBOR\Test\Type;

use CBOR\SignedIntegerObject;
use CBOR\StringStream;

final class SignedIntegerTest extends BaseTestCase
{
    /**
     * @test
     * @dataProvider getValidValue
     */
    public function createOnValidValue(int $intValue, string $expectedIntValue, int $expectedMajorType, int $expectedAdditionalInformation): void
    {
        $unsignedInteger = SignedIntegerObject::createFromGmpValue(gmp_init($intValue));
        static::assertEquals($expectedIntValue, $unsignedInteger->getValue());
        static::assertEquals($expectedMajorType, $unsignedInteger->getMajorType());
        static::assertEquals($expectedAdditionalInformation, $unsignedInteger->getAdditionalInformation());
    }

    public function getValidValue(): array
    {
        return [
            [
                -12345678,
                '-12345678',
                1,
                26,
            ],
            [
                -255,
                '-255',
                1,
                24,
            ],
            [
                -254,
                '-254',
                1,
                24,
            ],
            [
                -65535,
                '-65535',
                1,
                25,
            ],
            [
                -18,
                '-18',
                1,
                17,
            ],
        ];
    }

    /**
     * @test
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The value must be a negative integer.
     */
    public function ceateOnNegativeValue(): void
    {
        SignedIntegerObject::createFromGmpValue(gmp_init(1));
    }

    /**
     * @test
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Out of range. Please use NegativeBigIntegerTag tag with ByteStringObject object instead.
     */
    public function createOnOutOfRangeValue(): void
    {
        SignedIntegerObject::createFromGmpValue(gmp_init(-4294967297));
    }

    /**
     * @test
     * @dataProvider getDataSet
     */
    public function anUnsignedIntegerCanBeEncodedAndDecoded(string $data, string $expectedNormalizedData): void
    {
        $stream = new StringStream(\Safe\hex2bin($data));
        $object = $this->getDecoder()->decode($stream);
        $object->getNormalizedData();
        static::assertEquals($data, bin2hex((string) $object));
        static::assertEquals($expectedNormalizedData, $object->getNormalizedData());
    }

    public function getDataSet(): array
    {
        return [
            [
                '20',
                '-1',
            ], [
                '29',
                '-10',
            ], [
                '3863',
                '-100',
            ], [
                '3903e7',
                '-1000',
            ], [
                'c349010000000000000000',
                '-18446744073709551617',
            ], [
                '3bffffffffffffffff',
                '-18446744073709551616',
            ],
        ];
    }
}
