<?php

abstract class myDoctrineRecord extends dmDoctrineRecord
{

	public static $logging = false;

	public function log($message)
	{
		$this->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array($message)));
	}

	public function logging()
	{
		return $this->option('logging') || myDoctrineRecord::$logging;
	}

	public function get($fieldName, $load = true)
	{
		if($this->logging())
		{
			$this->log(sprintf('method: get, field: %s (load: %s)', $fieldName, $load ? 'true' : 'false'));
		}
		return parent::get($fieldName, $load);
	}

	public function set($fieldName, $value, $load = true)
	{
		if($this->logging())
		{
			$this->log(sprintf('method: set, field: %s (load: %s)', $fieldName, $load ? 'true' : 'false'));
		}
		return parent::set($fieldName, $value, $load);
	}
}
