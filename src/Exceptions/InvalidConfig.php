<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Exceptions;

use Exception;

final class InvalidConfig extends Exception
{
    public static function invalidAction(string $actionName, mixed $actionClass): self
    {
        $configuredAction = is_string($actionClass) ? $actionClass : get_debug_type($actionClass);

        return new self(sprintf('The configured Radar action [%s] must be an executable class. [%s] given.', $actionName, $configuredAction));
    }
}
