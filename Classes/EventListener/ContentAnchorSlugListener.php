<?php

declare(strict_types=1);

namespace IndizDigitalGmbh\SteppingStoneSitePackage\EventListener;

use IndizDigitalGmbh\SteppingStoneSitePackage\Utility\SlugUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Event\AfterLinkIsGeneratedEvent;
use TYPO3\CMS\Frontend\Typolink\LinkResult;

/**
 * Ersetzt Content-Anker wie #c406 durch den Slug der Überschrift (#contact-form),
 * passend zu den Ankern aus Layouts/Default.html.
 */
#[AsEventListener(identifier: 'stepping-stone-site-package/content-anchor-slug')]
final class ContentAnchorSlugListener
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
    ) {}

    public function __invoke(AfterLinkIsGeneratedEvent $event): void
    {
        $linkResult = $event->getLinkResult();
        if ($linkResult->getType() !== LinkService::TYPE_PAGE) {
            return;
        }

        $url = $linkResult->getUrl();
        if (!preg_match('/#c(\d+)$/', $url, $matches)) {
            return;
        }

        $slug = SlugUtility::slugify($this->getHeader((int)$matches[1]));
        if ($slug === '') {
            return;
        }

        $newUrl = substr($url, 0, -strlen($matches[0])) . '#' . $slug;

        $attributes = $linkResult->getAttributes();
        unset($attributes['href']);

        $event->setLinkResult(
            (new LinkResult($linkResult->getType(), $newUrl))
                ->withTarget($linkResult->getTarget())
                ->withLinkConfiguration($linkResult->getLinkConfiguration())
                ->withLinkText((string)$linkResult->getLinkText())
                ->withAttributes($attributes)
        );
    }

    private function getHeader(int $uid): string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $row = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        if ($row === false) {
            return '';
        }

        // Übersetzte Überschrift der aktuellen Sprache verwenden
        $row = GeneralUtility::makeInstance(PageRepository::class)
            ->getLanguageOverlay('tt_content', $row, $this->context->getAspect('language'));

        return (string)($row['header'] ?? '');
    }
}