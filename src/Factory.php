<?php

declare(strict_types=1);

namespace webignition\BasilAssertionFailureMessage;

use webignition\BasilModels\Action\Factory\Factory as ActionModelFactory;
use webignition\BasilModels\Action\Factory\MalformedDataException as MalformedActionDataException;
use webignition\BasilModels\Assertion\Factory\Factory as AssertionModelFactory;
use webignition\BasilModels\Assertion\Factory\MalformedDataException as MalformedAssertionDataException;

class Factory
{
    private $actionModelFactory;
    private $assertionModelFactory;

    public function __construct(ActionModelFactory $actionModelFactory, AssertionModelFactory $assertionModelFactory)
    {
        $this->actionModelFactory = $actionModelFactory;
        $this->assertionModelFactory = $assertionModelFactory;
    }

    public static function createFactory(): Factory
    {
        return new Factory(
            new ActionModelFactory(),
            new AssertionModelFactory()
        );
    }

    /**
     * @param string $json
     *
     * @return AssertionFailureMessage
     *
     * @throws FailureMessageException
     */
    public function fromJson(string $json): AssertionFailureMessage
    {
        $data = json_decode($json, true);
        if (null === $data) {
            throw FailureMessageException::createMalformedJsonException($json);
        }

        try {
            $assertion = $this->assertionModelFactory->createFromArray($data['assertion'] ?? []);
        } catch (MalformedAssertionDataException $malformedDataException) {
            throw FailureMessageException::createMalformedAssertionException($json, $malformedDataException);
        }

        $derivationSource = null;

        $derivationSourceData = $data['derived_from'] ?? null;
        if (is_array($derivationSourceData)) {
            $derivationSourceType = $derivationSourceData['statement_type'] ?? null;

            if (null === $derivationSourceType) {
                throw FailureMessageException::createDerivationSourceTypeMissingException($json);
            }

            $statementData = $derivationSourceData['statement'] ?? [];

            try {
                if ('action' === $derivationSourceType) {
                    $derivationSource = $this->actionModelFactory->createFromArray($statementData);
                } elseif ('assertion' === $derivationSourceType) {
                    $derivationSource = $this->assertionModelFactory->createFromArray($statementData);
                } else {
                    throw FailureMessageException::createDerivationSourceTypeInvalidException($json);
                }
            } catch (MalformedActionDataException $malformedActionDataException) {
                throw FailureMessageException::createMalformedActionException($json, $malformedActionDataException);
            } catch (MalformedAssertionDataException $malformedAssertionDataException) {
                throw FailureMessageException::createMalformedAssertionException(
                    $json,
                    $malformedAssertionDataException
                );
            }
        }

        return new AssertionFailureMessage($assertion, $derivationSource);
    }
}
