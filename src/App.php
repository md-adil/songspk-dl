<?php
namespace Adil\SongsPk;

use GuzzleHttp\Client;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;

use Exception;

class App
{
    public $baseUrl = 'https://songs.pk';
    protected $page = 0;
    protected $album = 0;
    protected $client;
    protected $out;
    protected $in;
    protected $progress;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false
        ]);
        
        $this->out = new ConsoleOutput();
        
        $this->out->setFormatter(new OutputFormatter(true, [
            'dl' => new OutputFormatterStyle('magenta', 'white'),
            'warning' => new OutputFormatterStyle('black', 'yellow'),
        ]));
        
        $this->in = new ArgvInput(null, new InputDefinition([
            new InputArgument('uri', InputArgument::OPTIONAL),
            new InputOption('page', null, InputOption::VALUE_REQUIRED)
        ]));

        $this->setProgress();
    }

    protected function setProgress()
    {
        $progress = new ProgressBar($this->out);
        $progress->setFormat("<dl>Downloading</> %t% %d% %p%% [%bar%]\n");
        $progress->setBarWidth(25);
        $this->progress = $progress;
    }

    public function info($m)
    {
        $message = implode(' ', func_get_args());
        $this->out->writeln("<info>{$message}</>");
    }

    public function error($m)
    {
        $message = implode(' ', func_get_args());
        $this->out->writeln("<error>{$message}</>");
    }

    public function getListContent($link)
    {
        try {
            $res = $this->client->get($link);
            $contents = new Crawler((string)$res->getBody());
            $this->info("Getting List -", $link);
            $this->parseList($contents);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function parseList(Crawler $crawler)
    {
        $this->info("Parsing List");
        $crawler->filter('figure')->each(function ($album) {
            $link = $album->filter('a');
            if ($link->count()) {
                $this->getItemContent($this->baseUrl . $link->attr('href'));
            }
        });
    }

    protected function getItemContent(string $link)
    {
        $this->info("Getting Item:", $link);
        try {
            $res = $this->client->get($link);
            $crawler = new Crawler((string)$res->getBody());
            $this->parseItem($crawler);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function parseItem(Crawler $crawler)
    {
        $this->info("Parsing");
        $d = $crawler->filter('.page-zip-wrap');
        if ($d->count()) {
            $this->downloadAlbum($crawler, $d);
        } else {
            $this->downloadSongs($crawler);
        }
    }

    protected function downloadAlbum(Crawler $album, Crawler $zip)
    {
        $this->info('Getting Download Content');
        $links = $zip->filter('a[download]');
        $count = $links->count();
        
        $name = $this->createSlug($album->filterXPath('//title')->text());

        if (!$count) {
            return;
        }
        if ($count > 1) {
            $this->downloadFile($links->eq(1)->attr('href'), $name . '.zip');
        } else {
            $this->downloadFile($links->attr('href'), $name . '.zip');
        }
    }

    public static function createSlug($str, $delimiter = '-')
    {
        // Little bit cleanup.
        $str = str_replace('songs.pk', '', strtolower($str));
        $str = str_replace('download songs', '', $str);

        $slug = trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter);
        return $slug;
    }

    public function downloadSongs(Crawler $album)
    {
        $this->out->writeln("<warning>Not zip found in this album. Skipping.</>");
    }

    protected function startProgress()
    {
        $this->progress->setMessage('0B', "t");
        $this->progress->setMessage('0B', "d");
        $this->progress->setMessage(0, "p");
        $this->progress->start();
    }

    public function downloadFile(string $link, $name)
    {
        $this->album++;

        $dir = getcwd();
        $filename = sprintf("%s/%d-%d-%s", $dir, $this->page, $this->album, $name);
        
        if (is_file($filename)) {
            $this->out->writeln("<warning>File already exists. Skipping.</>");
            return;
        }

        $this->startProgress();
        $this->client->request('GET', $link, [
            'sink' => $filename,
            'progress' => function ($tl, $dl, $ul, $ulFar) {
                if (!$tl) {
                    return;
                }
                $this->progress->setProgress((int)(($dl * 25) / $tl));
                $this->progress->setMessage($this->humanFilesize($tl), 't');
                $this->progress->setMessage($this->humanFilesize($dl), 'd');
                $this->progress->setMessage(round(($dl * 100) / $tl, 2), 'p');
            }
        ]);
        $this->progress->finish();
    }

    protected function makeLink($page)
    {
        $link = $this->baseUrl;
        $uri = $this->in->getArgument('uri');
        if ($uri) {
            $link = $link .= '/' . trim($uri, '/');
            $link .= '?page=' . $page;
        }
        return $link;
    }

    protected function humanFilesize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    protected function getPages()
    {
        $page = $this->in->getOption('page');
        if (!$page) {
            return [1];
        }
        if (strpos($page, '-') !== false) {
            return call_user_func_array('range', explode('-', $page));
        }
        if (strpos($page, ',')) {
            return explode(',', $page);
        }
        return $page;
    }

    public function run()
    {
        foreach ($this->getPages() as $page) {
            $this->album = 0;
            $this->page = $page;
            $this->getListContent($this->makeLink($page));
        }
    }
}
