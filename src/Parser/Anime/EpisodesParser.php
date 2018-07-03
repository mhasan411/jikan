<?php

namespace Jikan\Parser\Anime;

use Jikan\Model\EpisodeListItem;
use Jikan\Model\Episodes;
use Jikan\Parser\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class EpisodseParser
 *
 * @package Jikan\Parser
 */
class EpisodesParser implements ParserInterface
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * EpisodesParser constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * @return EpisodeListItem[]
     */
    public function getEpisodes(): array
    {
        $episodes = $this->crawler
            ->filterXPath('//table[contains(@class, \'js-watch-episode-list ascend\')]/tr[1]');

        if (!$episodes->count()) {
            return [];
        }

        return $episodes->nextAll()
            ->each(
                function (Crawler $crawler) {
                    return (new EpisodeListItemParser($crawler))->getModel();
                }
            );
    }

    /**
     * @return int
     */
    public function getEpisodesLastPage(): int
    {
        $episodesLastPage = $this->crawler
            ->filterXPath('//div[contains(@class, \'pagination\')]')
            ->children();

        if ($episodesLastPage->getNode(1)->tagName === 'span') {
            $episodesLastPage = $episodesLastPage
                ->filterXPath('//a')
                ->last();

            preg_match('~(\d+) - (\d+)~', $episodesLastPage->text(), $matches);
            return ceil((int) $matches[2] / 100);
        }

        $episodesLastPage = $this->crawler
            ->filterXPath('//div[contains(@class, \'pagination\')]/a')
            ->last();
        
        if (!$episodesLastPage->count()) {
            return 1;
        }

        preg_match('~(\d+) - (\d+)~', $episodesLastPage->text(), $matches);
        return ceil((int) $matches[2] / 100);
    }

    /**
     * Return the model
     */
    public function getModel(): Episodes
    {
        return Episodes::fromParser($this);
    }
}
