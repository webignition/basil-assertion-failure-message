<?php

declare(strict_types=1);

namespace webignition\BasilAssertionFailureMessage;

use Exception;
use Throwable;
use webignition\BasilModels\Action\Factory\MalformedDataException as MalformedActionDataException;
use webignition\BasilModels\Assertion\Factory\MalformedDataException as MalformedAssertionDataException;

class FailureMessageException extends Exception
{
    public const CODE_MALFORMED_JSON = 1;
    public const CODE_MALFORMED_ASSERTION = 2;
    public const CODE_MALFORMED_ACTION = 2;
    public const CODE_DERIVATION_SOURCE_TYPE_MISSING = 4;
    public const CODE_DERIVATION_SOURCE_TYPE_INVALID = 5;

    private const MESSAGE_MALFORMED_JSON = 'Malformed JSON';
    private const MESSAGE_MALFORMED_ASSERTION = 'Malformed assertion';
    private const MESSAGE_MALFORMED_ACTION = 'Malformed action';
    private const MESSAGE_DERIVATION_SOURCE_TYPE_MISSING = 'Derivation source type missing';
    private const MESSAGE_DERIVATION_SOURCE_TYPE_INVALID = 'Derivation source type invalid';

    private $failureMessage;

    public function __construct(
        string $exceptionMessage,
        int $code,
        string $failureMessage,
        ?Throwable $previous = null
    ) {
        $this->failureMessage = $failureMessage;

        parent::__construct($exceptionMessage, $code, $previous);
    }

    public function getFailureMessage(): string
    {
        return $this->failureMessage;
    }

    public static function createMalformedJsonException(string $failureMessage): self
    {
        return new FailureMessageException(
            self::MESSAGE_MALFORMED_JSON,
            self::CODE_MALFORMED_JSON,
            $failureMessage
        );
    }

    public static function createMalformedActionException(
        string $failureMessage,
        MalformedActionDataException $malformedDataException
    ): self {
        return new FailureMessageException(
            self::MESSAGE_MALFORMED_ACTION,
            self::CODE_MALFORMED_ACTION,
            $failureMessage,
            $malformedDataException
        );
    }

    public static function createMalformedAssertionException(
        string $failureMessage,
        MalformedAssertionDataException $malformedDataException
    ): self {
        return new FailureMessageException(
            self::MESSAGE_MALFORMED_ASSERTION,
            self::CODE_MALFORMED_ASSERTION,
            $failureMessage,
            $malformedDataException
        );
    }

    public static function createDerivationSourceTypeMissingException(string $failureMessage): self
    {
        return new FailureMessageException(
            self::MESSAGE_DERIVATION_SOURCE_TYPE_MISSING,
            self::CODE_DERIVATION_SOURCE_TYPE_MISSING,
            $failureMessage
        );
    }

    public static function createDerivationSourceTypeInvalidException(string $failureMessage): self
    {
        return new FailureMessageException(
            self::MESSAGE_DERIVATION_SOURCE_TYPE_INVALID,
            self::CODE_DERIVATION_SOURCE_TYPE_INVALID,
            $failureMessage
        );
    }
}
