<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MathAddViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('a', 'float', 'First value', true);
        $this->registerArgument('b', 'float', 'Value to add', true);
    }

    public function render(): int|float
    {
        return $this->arguments['a'] + $this->arguments['b'];
    }
}