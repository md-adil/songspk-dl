<?php
require(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;

class App {
	
	public $baseUrl = 'https://songs.pk';
	protected $page = 0;
	protected $album = 0;
	protected $client;
	protected $out;
	protected $in;

	public function __construct()
	{
		$this->client = new Client();
		
		$this->out = new ConsoleOutput();
		
		$this->out->setFormatter(new OutputFormatter(true, [
			'downloading' => new OutputFormatterStyle('magenta', 'white')
		]));
		
		$this->in = new ArgvInput(null, new InputDefinition([
			new InputArgument('uri', InputArgument::OPTIONAL),
			new InputOption('page', null, InputOption::VALUE_REQUIRED)
		]));
		
	}


	function getListContent($link) {
		try {
			$contents = new Crawler(file_get_contents($link));
			dump('Getting List - ' . $link);
			$this->parseList($contents);
		} catch (Exception $e) {
			dump("Error: " . $e->getMessage());
		}
	}

	function parseList(Crawler $crawler) {
		dump('Parsing List');
		$crawler->filter('figure')->each(function($album) {
			$link = $album->filter('a');
			if($link->count()) {
				$this->getItemContent($this->baseUrl . $link->attr('href'));
			}
		});
	}

	function getItemContent(string $link) {
		dump('Getting Item - ' . $link);
		try {
			$crawler = new Crawler(file_get_contents($link));
			$this->parseItem($crawler);
		} catch (Exception $e) {
			dump('Error: ' . $e->getMessage());
		}
	}

	function parseItem(Crawler $crawler) {
		dump('Prasing');
		$d = $crawler->filter('.page-zip-wrap');
		if($d->count()) {
			$this->downloadAlbum($d);
		} else {
			$this->downloadSongs($crawler);
		}
	}

	function downloadAlbum(Crawler $album) {
		dump('Getting Download Content');
		$links = $album->filter('a[download]');
		$count = $links->count();
		if(!$count) {
			return;
		}
		if($count > 1) {
			$this->downloadFile($links->eq(1)->attr('href'), $links->eq(1)->text() . '.zip' );
		} else {
			$this->downloadFile($links->attr('href'), $links->text() . '.zip');
		}
	}

	function downloadSongs(Crawler $album) {
	
	}

	function downloadFile(string $link, $name) {
		$this->album++;
		dump('Downloading Started');
		$name = preg_replace("/\s+/", '', $name);
		$name = sprintf("%d-%d-%s", $this->page, $this->album, $name);
		$this->client->request('GET', $link, [
			'verify' => false,
			'sink' => __DIR__ . '/downloads/' . $name,
			'progress' => function($size, $sizeFar, $ul, $ulFar) {
				dump(sprintf("%s %s %s %s", $size, $sizeFar, $ul, $ulFar));
			}
		]);
	}

	protected function makeLink($page)
	{
		$link = $this->baseUrl;
		$uri = $this->in->getArgument('uri');
		if($uri) {
			$link = $link .= '/' . trim($uri, '/');
			$link .= '?page=' . $page;
		}
		return $link;
	}

	protected function makePage()
	{
		
	}

	function run() {
		// dump($this->in->getArguments());
		// $this->out->writeln("<downloading>Hey there</>");
		dump($this->makeLink(1));
		return;
		for($page = 3; $page <= 100; $page++) {
			$this->page = $page;
			$this->album = 0;
			// https://songs.pk/browse/bollywood-albums?page=3
			$this->getListContent('https://songs.pk/browse/bollywood-albums?page=' . $page);
		}
	}
}
try {
	$app = new App();
	$app->run();
} catch (Exception $e) {
	echo $e->getMessage();
}

