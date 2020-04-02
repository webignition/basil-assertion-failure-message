<?php

declare(strict_types=1);

namespace webignition\BasilAssertionFailureMessage;

use JsonSerializable;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\StatementInterface;

class AssertionFailureMessage implements JsonSerializable
{
    private $assertion;
    private $derivationSource;

    public function __construct(AssertionInterface $assertion, ?StatementInterface $derivationSource = null)
    {
        $this->assertion = $assertion;
        $this->derivationSource = $derivationSource;
    }

    public function getAssertion(): AssertionInterface
    {
        return $this->assertion;
    }

    public function getDerivationSource(): ?StatementInterface
    {
        return $this->derivationSource;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'assertion' => $this->assertion,
        ];

        if (null !== $this->derivationSource) {
            $data['derived_from'] = [
                'statement_type' => $this->derivationSource instanceof ActionInterface ? 'action' : 'assertion',
                'statement' => $this->derivationSource,
            ];
        }

        return $data;
    }
}
