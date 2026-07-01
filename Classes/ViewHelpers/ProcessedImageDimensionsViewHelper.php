<?php
namespace IndizDigitalGmbh\SteppingStoneSitePackage\ViewHelpers;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ProcessedImageDimensionsViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('image', 'object', 'FAL image', true);
        $this->registerArgument('maxWidth', 'int', '', false, 0);
        $this->registerArgument('maxHeight', 'int', '', false, 0);
        $this->registerArgument('as', 'string', 'Variable name for the result', false, 'dimensions');
    }

    public function render()
    {
        $image = $this->arguments['image'];
        if ($image instanceof FileReference) {
            $image = $image->getOriginalResource();
        }

        $imageService = GeneralUtility::makeInstance(ImageService::class);
        $processedImage = $imageService->applyProcessingInstructions($image, [
            'maxWidth' => $this->arguments['maxWidth'],
            'maxHeight' => $this->arguments['maxHeight'],
        ]);

        $dimensions = [
            'width' => $processedImage->getProperty('width'),
            'height' => $processedImage->getProperty('height'),
        ];

        $variableProvider = $this->renderingContext->getVariableProvider();
        $variableProvider->add($this->arguments['as'], $dimensions);
        $output = $this->renderChildren();
        $variableProvider->remove($this->arguments['as']);

        return $output;
    }
}
