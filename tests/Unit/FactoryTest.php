<?php

declare(strict_types=1);

namespace webignition\BasilAssertionFailureMessage\Tests\Unit;

use webignition\BasilAssertionFailureMessage\AssertionFailureMessage;
use webignition\BasilAssertionFailureMessage\Factory;
use webignition\BasilAssertionFailureMessage\FailureMessageException;
use webignition\BasilModels\Action\Factory\MalformedDataException as MalformedActionDataException;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\Assertion\Factory\UnknownComparisonException;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::createFactory();
    }

    /**
     * @dataProvider fromJsonSuccessDataProvider
     */
    public function testFromJsonSuccess(AssertionFailureMessage $assertionFailureMessage)
    {
        $this->assertEquals(
            $assertionFailureMessage,
            $this->factory->fromJson((string) json_encode($assertionFailureMessage))
        );
    }

    public function fromJsonSuccessDataProvider(): array
    {
        $existsAssertion = new Assertion(
            '$".selector" exists',
            '$".selector"',
            'exists'
        );

        $comparisonAssertion = new ComparisonAssertion(
            '$".selector" is "value"',
            '$".selector"',
            'is',
            '"value"'
        );

        $interactionAction = new InteractionAction(
            'click $".selector"',
            'click',
            '$".selector"',
            '$".selector"'
        );

        return [
            'no derivation source' => [
                'assertionFailureMessage' => new AssertionFailureMessage($existsAssertion),
            ],
            'assertion derivation source' => [
                'assertionFailureMessage' => new AssertionFailureMessage($existsAssertion, $comparisonAssertion),
            ],
            'action derivation source' => [
                'assertionFailureMessage' => new AssertionFailureMessage($existsAssertion, $interactionAction),
            ],
        ];
    }

    /**
     * @dataProvider fromJsonThrowsFailureMessageExceptionDataProvider
     */
    public function testFromJsonThrowsFailureMessageException(
        string $assertionFailureMessageJson,
        FailureMessageException $expectedException
    ) {
        try {
            $this->factory->fromJson($assertionFailureMessageJson);
            $this->fail('FailureMessageException not thrown');
        } catch (FailureMessageException $failureMessageException) {
            $this->assertEquals($expectedException, $failureMessageException);
            $this->assertSame($assertionFailureMessageJson, $failureMessageException->getFailureMessage());
        }
    }

    public function fromJsonThrowsFailureMessageExceptionDataProvider(): array
    {
        $existsAssertionData = [
            'source' => '$".selector" exists',
            'identifier' => '$".selector"',
            'comparison' => 'exists',
        ];

        $invalidComparisonAssertionData = [
            'source' => '$".selector" foo',
            'identifier' => '$".selector"',
            'comparison' => 'foo',
        ];

        $invalidActionData = [
            'source' => 'click $".selector"',
            'type' => 'click',
            'arguments' => '$".selector"',
        ];

        return [
            'malformed json' => [
                'assertionFailureMessageJson' => '{foo}',
                'expectedException' => FailureMessageException::createMalformedJsonException('{foo}'),
            ],
            'malformed assertion in assertion' => [
                'assertionFailureMessageJson' => json_encode([
                    'assertion' => $invalidComparisonAssertionData,
                ]),
                'expectedException' => FailureMessageException::createMalformedAssertionException(
                    (string) json_encode([
                        'assertion' => $invalidComparisonAssertionData,
                    ]),
                    new UnknownComparisonException(
                        $invalidComparisonAssertionData,
                        'foo'
                    )
                ),
            ],
            'derivation source type missing' => [
                'assertionFailureMessageJson' => json_encode([
                    'assertion' => $existsAssertionData,
                    'derived_from' => [],
                ]),
                'expectedException' => FailureMessageException::createDerivationSourceTypeMissingException(
                    (string) json_encode([
                        'assertion' => $existsAssertionData,
                        'derived_from' => [],
                    ])
                ),
            ],
            'derivation source type invalid' => [
                'assertionFailureMessageJson' => json_encode([
                    'assertion' => $existsAssertionData,
                    'derived_from' => [
                        'statement_type' => 'foo'
                    ],
                ]),
                'expectedException' => FailureMessageException::createDerivationSourceTypeInvalidException(
                    (string) json_encode([
                        'assertion' => $existsAssertionData,
                        'derived_from' => [
                            'statement_type' => 'foo'
                        ],
                    ])
                ),
            ],
            'malformed assertion in derivation source' => [
                'assertionFailureMessageJson' => json_encode([
                    'assertion' => $existsAssertionData,
                    'derived_from' => [
                        'statement_type' => 'assertion',
                        'statement' => $invalidComparisonAssertionData,
                    ],
                ]),
                'expectedException' => FailureMessageException::createMalformedAssertionException(
                    (string) json_encode([
                        'assertion' => $existsAssertionData,
                        'derived_from' => [
                            'statement_type' => 'assertion',
                            'statement' => $invalidComparisonAssertionData,
                        ],
                    ]),
                    new UnknownComparisonException(
                        $invalidComparisonAssertionData,
                        'foo'
                    )
                ),
            ],
            'malformed action in derivation source' => [
                'assertionFailureMessageJson' => json_encode([
                    'assertion' => $existsAssertionData,
                    'derived_from' => [
                        'statement_type' => 'action',
                        'statement' => $invalidActionData,
                    ],
                ]),
                'expectedException' => FailureMessageException::createMalformedActionException(
                    (string) json_encode([
                        'assertion' => $existsAssertionData,
                        'derived_from' => [
                            'statement_type' => 'action',
                            'statement' => $invalidActionData,
                        ],
                    ]),
                    new MalformedActionDataException($invalidActionData)
                ),
            ],
        ];
    }
}
