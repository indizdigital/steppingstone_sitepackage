<?php
namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CookieViewHelper extends AbstractViewHelper {
    public function initializeArguments(): void {
        $this->registerArgument('name', 'string', 'Cookie name', true);
    }
    public function render(): string {
        return $_COOKIE[$this->arguments['name']] ?? '';
    }
}