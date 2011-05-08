<?php
class seDmDoctrineFixtureTopCache extends dmMicroCache
{
	public function add($key, $value)
	{
		$this->setCache($key, $value);
		return $this;
	}
	
	public function get($key, $default = null)
	{
		return $this->getCache($key, $default);
	}
	
	public function clear($key = null)
	{
		$this->clearCache($key);
		return $this;
	}
}