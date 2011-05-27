<?php

/**
 *
 * @author serard
 *
 */
class seDmDoctrineFixturesLoader implements IteratorAggregate
{
	protected $folder;
	protected $files = array();
	protected $logger;
	protected $name;
	protected $topCache;
	
	protected $objects = array();
	
	public function setTopCache($cache)
	{
		$this->topCache = $cache;
		return $this;
	}
	
	public function add($name, $obj, $topCache = true)
	{
		if($topCache)
		{
			$this->topCache->add($name, $obj);
		}
		else
		{
			$this->objects[$name] = $obj;
		}
		return $obj;
	}
	
	public function get($name, $default = null)
	{
		return isset($this->objects[$name]) ? $this->objects[$name] : $this->topCache->get($name, $default);
	}
	
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}

	public function setLogger($callable)
	{
		$this->logger = $callable;
		return $this;
	}

	public function logPhp()
	{
		if($this->logger)
		{
			$args = func_get_args();
			call_user_func_array(array($this->logger, 'logPhp'), $args);
		}
	}

	public function logBlock()
	{
		if($this->logger)
		{
			$args = func_get_args();
			call_user_func_array(array($this->logger, 'logPhp'), $args);
		}
	}

	public function setFolder($folder)
	{
		$this->folder = $folder;
		return $this;
	}

	public function getFolder()
	{
		return $this->folder;
	}

	public function setFiles($files)
	{
		$this->files = (array) $files;
		return $this;
	}

	public function getFiles()
	{
		return $this->files;
	}

	public function addFile($file)
	{
		$this->files[] = $file;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->files);
	}

	public function load()
	{
		foreach($this as $f)
		{
			$path = dmOs::join($this->folder, $f . '.php');
			if(!file_exists($path))
			{
				$this->logBlock('File ' . $path . ' doesnt exist', 'ERROR_LARGE');
				return;
			}
			$this->logPhp($path, $f);
			require $path;

		}
	}

	public function getIterator()
	{
		return new ArrayIterator($this->files);
	}
	
	public function getCache()
	{
		return $this->topCache;
	}
}