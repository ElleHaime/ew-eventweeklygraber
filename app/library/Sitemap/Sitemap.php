<?php

namespace Library\Sitemap;

class Sitemap
{
	const SM_SCHEMA					= 'http://www.sitemaps.org/schemas/sitemap/0.9';
	const SM_ENCODING				= 'UTF-8';
	const SM_XML_VERSION			= '1.0';
	const SM_FILENAME_SEP			= '-';
	const SM_DEFAULT_NAME			= 'sitemap';
	const SM_EXT 					= '.xml';
	const SM_ITEM_PER_FILE			= 50000;
	const SM_FILESIZE				= 10485760;
	const SM_DEFAULT_PRIORITY 		= 0.5;
	const SM_DEFAULT_CHANGE_FREQ 	= 'daily';

	
	private $domain;
	private $pathIndex;
	private $pathSitemap;
	private $urlSitemap;
	private $writer;
	private $currentFile			= false;
	private $urlPrefix				= false;
	private $item					= 0;
	private $sitemapItem			= 0;
	private $siteMapsList			= [];
	private $siteMapsIndex			= '';
	
	
	public function __construct($domain, $pathSitemap)
	{
		$this -> setDomain($domain);
		$this -> setPathSitemap($pathSitemap);
	}
	
	
	public function addItem($url, $lastMod = false, $changeFreq = self::SM_DEFAULT_CHANGE_FREQ, $priority = self::SM_DEFAULT_PRIORITY) 
	{
		if (!$this -> getCurrentFile() || !$this -> checkCurrentDocumentSize()) {
			$this -> startDocument();
		}

		if (!$lastMod) {
			$lastMod = date('Y-m-d');
		}
				
		$this -> increaseItem();
		$this -> getWriter() -> startElement('url');
		$this -> getWriter() -> writeElement('loc', $this -> getDomain() . '/' . $url);
		$this -> getWriter() -> writeElement('lastmod', $lastMod);
		$this -> getWriter() -> writeElement('changefreq', $changeFreq);
		$this -> getWriter() -> writeElement('priority', $priority);
		$this -> getWriter() -> endElement();
		
		return $this;
	}

	
	private function startDocument()
	{
		if ($this -> getCurrentFile()) {
			$this -> endDocument();
		}
		
		$this -> setWriter(new \XMLWriter());

		$filename = $this -> getPathSitemap() . '/' . self::SM_DEFAULT_NAME . self::SM_FILENAME_SEP . $this -> getSitemapItem() . self::SM_EXT;
		$this -> setCurrentFile($filename);
print_r("......." . $this -> getCurrentFile() . "\n\r");
		$this -> getWriter() -> openURI($this -> getCurrentFile());
		$this -> getWriter() -> startDocument(self::SM_XML_VERSION, self::SM_ENCODING);
		$this -> getWriter() -> setIndent(true);
		$this -> getWriter() -> startElement('urlset');
		$this -> getWriter() -> writeAttribute('xmlns', self::SM_SCHEMA);
	}
	
	
	private function endDocument()
	{
		if (!$this -> getWriter()) {
			$this -> startDocument();
		}
		$this -> getWriter() -> endElement();
		$this -> getWriter() -> endDocument();
		
		$this -> setItem(0);
		$this -> addSitemapsList($this -> getCurrentFile());
		$this -> setCurrentFile(false);
		$this -> increaseSitemapItem();
		
		return $this;
	}		
	
	
	public function createSitemapIndex()
	{
		$this -> endDocument();
		
		$this -> setWriter(new \XMLWriter());
		$this -> setCurrentFile($this -> getPathIndex(). '/' . self::SM_DEFAULT_NAME . self::SM_EXT);
		$this -> setSitemapsIndex($this -> getCurrentFile());
		
		$this -> getWriter() -> openURI($this -> getCurrentFile());
		$this -> getWriter() -> startDocument(self::SM_XML_VERSION, self::SM_ENCODING);
	    $this -> getWriter() -> setIndent(true);
	    $this -> getWriter() -> startElement('sitemapindex');
	    $this -> getWriter() -> writeAttribute('xmlns', self::SM_SCHEMA);
		
		for ($index = 0; $index < $this -> getSitemapItem(); $index++) {
			$this -> getWriter() -> startElement('sitemap');
			$this -> getWriter() -> writeElement('loc', $this -> getUrlSitemap() . '/' . self::SM_DEFAULT_NAME . self::SM_FILENAME_SEP . $index . self::SM_EXT);
			$this -> getWriter() -> writeElement('lastmod', date('Y-m-d'));
			$this -> getWriter() -> endElement();
		}
		
		$this -> getWriter() -> endElement();
		$this -> getWriter() -> endDocument();
	}

	
	private function checkCurrentDocumentSize()
	{
		if ((($this -> getItem() % self::SM_ITEM_PER_FILE) == 0) || (filesize($this -> getCurrentFile()) >= self::SM_FILESIZE)) {
			return false;
		}
			
		return true;
	}
	
	
	public function setPriority($priority = self::SM_DEFAULT_PRIORITY) 
	{
		$this -> priority = $priority;
		return $this;
	}
	
	
	private function getPriority()
	{
		return $this -> priority;
	}

	
	public function setDomain($domain)
	{
		$this -> domain = $domain;
		return $this;
	}
	
	
	private function getDomain()
	{
		return $this -> domain;
	}
	
	
	public function setPathIndex($path)
	{
		if (!is_dir($path)) {
			throw new \Exception('Path for sitemap index isn\'t a directory');
			return false;
		}
		$this -> pathIndex = $path;
	
		return $this;
	}
	
	
	private function getPathIndex()
	{
		return $this -> pathIndex;
	}
	
	
	public function setPathSitemap($path)
	{
		if (!is_dir($path)) {
			throw new \Exception('Path for sitemap isn\'t a directory');
			return false;
		}
		$this -> pathSitemap = $path;
	
		return $this;
	}
	
	
	private function getPathSitemap()
	{
		return $this -> pathSitemap;
	}
	
	
	public function setUrlSitemap($url)
	{
		$this -> urlSitemap = $url;
	
		return $this;
	}
	
	
	private function getUrlSitemap()
	{
		return $this -> urlSitemap;
	}
	
	
	public function setWriter(\XMLWriter $writer)
	{
		$this -> writer = $writer;
		return $this;
	}
	
	
	private function getWriter()
	{
		return $this -> writer;
	}
	
	
	public function setCurrentFile($filename)
	{
		$this -> currentFile = $filename;
		return $this;
	}
	
	
	private function getCurrentFile()
	{
		return $this -> currentFile;
	}
	

	public function setItem($item)
	{
		$this -> item = $item;
		return $this;
	}
	
	
	private function getItem()
	{
		return $this -> item;
	}
	
	
	public function getSitemapItem()
	{
		return $this -> sitemapItem;
	}

	
	private function increaseItem()
	{
		$this -> item++;
	}

	
	private function increaseSitemapItem()
	{
		$this -> sitemapItem++;
	}
	
	
	private function addSitemapsList($path)
	{
		$this -> siteMapsList[] = $path;
		return $this;
	}
	
	
	public function getSitemapsList()
	{
		return $this -> siteMapsList;		
	}
	

	private function setSitemapsIndex($path)
	{
		$this -> siteMapsIndex = $path;
		return $this;
	}
	
	
	public function getSitemapsIndex()
	{
		return $this -> siteMapsIndex;
	}

	
	public function setUrlPrefix($prefix)
	{
		if (!empty($prefix)) {
			$this -> urlPrefix = $prefix;
			$this -> setDomain($this -> getDomain() . '/' . $this -> urlPrefix);
		}
		
		return $this;
	}
}