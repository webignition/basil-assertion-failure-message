<?php

declare(strict_types=1);

namespace webignition\BasilAssertionFailureMessage;

use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\StatementInterface;

class AssertionFailureMessage
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
}
