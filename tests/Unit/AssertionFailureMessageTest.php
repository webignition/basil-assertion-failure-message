<?php

declare(strict_types=1);

namespace webignition\BasilAssertionFailureMessage\Tests\Unit;

use webignition\BasilAssertionFailureMessage\AssertionFailureMessage;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\StatementInterface;

class AssertionFailureMessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(AssertionInterface $assertion, ?StatementInterface $derivationSource = null)
    {
        $assertionFailureMessage = new AssertionFailureMessage($assertion, $derivationSource);

        $this->assertSame($assertion, $assertionFailureMessage->getAssertion());
        $this->assertSame($derivationSource, $assertionFailureMessage->getDerivationSource());
    }

    public function createDataProvider(): array
    {
        return [
            'no derivation source' => [
                'assertion' => new Assertion(
                    '$".selector" exists',
                    '$".selector"',
                    'exists'
                ),
                'derivationSource' => null,
            ],
            'assertion derivation source' => [
                'assertion' => new Assertion(
                    '$".selector" exists',
                    '$".selector"',
                    'exists'
                ),
                'derivationSource' => new ComparisonAssertion(
                    '$".selector" is "value"',
                    '$".selector"',
                    'is',
                    '"value"'
                ),
            ],
            'action derivation source' => [
                'assertion' => new Assertion(
                    '$".selector" exists',
                    '$".selector"',
                    'exists'
                ),
                'derivationSource' => new InteractionAction(
                    'click $".selector"',
                    'click',
                    '$".selector"',
                    '$".selector"'
                ),
            ],
        ];
    }
}
