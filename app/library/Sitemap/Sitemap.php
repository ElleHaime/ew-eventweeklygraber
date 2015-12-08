<?php

namespace Library\Sitemap;

class Sitemap
{
	const SM_ENCODING				= 'UTF-8';
	const SM_XML_VERSION			= '1.0';
	const SM_FILENAME_SEP			= '-';
	const SM_SCHEMA					= 'http://www.sitemaps.org/schemas/sitemap/0.9';
	const SM_DEFAULT_NAME			= 'sitemap';
	const SM_EXT 					= '.xml';
	const SM_ITEM_PER_FILE			= 50000;
	const SM_FILESIZE				= 10485760;
	const SM_DEFAULT_PRIORITY 		= 0.5;
	const SM_DEFAULT_CHANGE_FREQ 	= 'weekly';

	
	private $domain;
	private $pathIndex;
	private $pathSitemap;
	private $urlSitemap;
	private $writer;
	private $currentFile			= false;
	private $item					= 0;
	private $sitemapItem			= 0;
	private $siteMapsList			= [];		
	
	
	public function __constrcut($domain, $pathSitemap, $urlSitemap = false, $pathIndex = false)
	{
		$this -> setDomain($domain);
		$this -> setPathSitemap($pathSitemap);
		if ($pathIndex && $urlSitemap) {
			$this -> setPathIndex($pathIndex);
			$this -> setUrlSitemap($urlSitemap);
		}
	}
	
	
	public function addItem($url, $lastMod = false, $changeFreq = self::SM_DEFAULT_CHANGE_FREQ, $priority = self::SM_DEFAULT_PRIORITY) 
	{
		if (!$this -> getCurrentFile() || !$this -> checkCurrentDocumentSize()) {
			$this -> startDocument();
		}
		
		if ($lastMod) {
			$lastMod = date('Y-m-d');
		}
				
		$this -> increaseItem();
		$this -> getWriter() -> startElement('url')
							 -> writeAttribute('loc', $url)
							 -> writeAttribute('lastmod', $lastMod)
							 -> writeAttribute('changefreq', $changeFreq)
							 -> writeAttribute('priority', $priority)
							 -> endElement();
		
		return $this;
	}

	
	private function startDocument()
	{
		$this -> setWriter(new \XMLWriter());

		if ($this -> getCurrentFile()) {
			$this -> endDocument();
		}
		$filename = $this -> getPathSitemap() . '/' . self::SM_DEFAULT_NAME . self::SM_FILENAME_SEP . $this -> getSitemapItem() . self::SM_EXT;  
		$this -> setCurrentFile($filename);

		$this -> getWriter() -> openURI($this -> getCurrentFile())
						     -> startDocument(self::SM_XML_VERSION, self::SM_ENCODING)
						     -> setIndent(true)
						     -> startElement('urlset')
						     -> writeAttribute('xmlns', self::SM_SCHEMA);
	}
	
	
	private function endDocument()
	{
		if (!$this -> getWriter()) {
			$this -> startDocument();
		}
		$this -> getWriter() -> endElement()
							 -> endDocument();
		
		$this -> setItem(0);
		$this -> setCurrentFile(false);
		$this -> increaseSitemapItem();
	}		
	
	
	public function createSitemapIndex()
	{
		$this -> endDocument();
		
		$this -> setWriter(new \XMLWriter());
		$this -> setCurrentFile($this -> getPathIndex(). '/' . self::SM_DEFAULT_NAME . self::SM_EXT);
		
		$this -> getWriter() -> openURI($this -> getCurrentFile())
							 -> startDocument(self::SM_XML_VERSION, self::SM_ENCODING)
						     -> setIndent(true)
						     -> startElement('sitemapindex')
						     -> writeAttribute('xmlns', self::SM_SCHEMA);
		
		for ($index = 0; $index <= $this -> getSitemapItem(); $index++) {
			$this -> getWriter() -> startElement('sitemap')
								 -> writeAttribute('loc', $this -> getUrlSitemap() . '/' . self::SM_DEFAULT_NAME . self::SM_FILENAME_SEP . $index . self::SM_EXT)
								 -> writeAttribute('lastmod', date('Y-m-d'))
								 -> endElement();
		}
		
		$this -> getWriter() -> endElement()
							 -> endDocument();
	}

	
	private function checkCurrentDocumentSize()
	{
		if ((($this -> getItem() % self::SM_ITEM_PER_FILE) == 0) || 
				(filesize($this -> getPathSitemap() . '/' . $this -> getCurrentFile()) >= self::SM_FILESIZE)) 
		{
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
	
	public function getSitemapsList()
	{
		return $this -> siteMapsList;		
	}
}