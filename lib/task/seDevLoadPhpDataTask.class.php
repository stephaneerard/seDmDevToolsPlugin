<?php
/**
 *
 * @author serard
 *
 */
class seDevLoadPhpDataTask extends dmContextTask
{

	protected $cache;

	protected function configure()
	{
		parent::configure();

		$this->addOption('clean', 'c', sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Run a clean');
		$this->addOption('files', 'f', sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Give files to load');
		$this->addOption('rebuild', 'b', sfCommandOption::PARAMETER_NONE, 'Rebuilds all');
		$this->addOption('rebuild-db', 'r', sfCommandOption::PARAMETER_NONE, 'Rebuilds db');
		$this->addOption('truncate-tables', 'a', sfCommandOption::PARAMETER_NONE, 'Truncates tables');
		$this->addOption('reload', 'd', sfCommandOption::PARAMETER_NONE, 'Reloads data');
		$this->addOption('with-dump', 'l', sfCommandOption::PARAMETER_NONE, 'Use data dump');

		$this->addOption('global-transaction', 'g', sfCommandOption::PARAMETER_NONE, 'Wrap all DB IO into a transaction');

		$this->namespace        = 'se';
		$this->name             = 'load-php-data';
		$this->briefDescription = 'Loads data from php files';
		$this->detailedDescription = <<<EOF
The [{$this->namespace}:{$this->name}|INFO] task does load data from php files
Call it with:

  [php symfony {$this->namespace}:{$this->name}|INFO]
  
  [--files path/to/file1 --files path/to/file2|INFO]
  
  	Will load given files as php files using require
  	
  [--rebuild, --rebuild-db|INFO]
  
    Will rebuild using dm:setup (and rebuild db if --rebuild-db)
    
  [--truncate-tables|INFO]
  
    Will truncate tables using dm:truncate-tables
    
  [--reload, --with-dump|INFO]
  
    Will reload data using se:reload-data (using dump if --with-dump)
  
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->withDatabase();

		if($options['clean'])
		{
			$this->runTask('se:clean');
		}

		if($options['rebuild'] || $options['rebuild-db'])
		{
			$this->runTask('dm:setup', array(), array('env' => $options['env'], 'clear-db' => $options['rebuild-db'], 'no-confirmation' => true, 'dont-load-data' => true));
		}
		elseif($options['truncate-tables'])
		{
			$this->runTask('dm:truncate-tables', array(), array('env' => $options['env']));
		}

		if($options['reload'] || $options['with-dump'])
		{
			$this->runTask('se:reload-data', array(), array('env' => $options['env'], 'with-dump' => $options['with-dump'], 'with-php-fixtures' => false));
		}
		elseif($options['rebuild-db'])
		{
			$this->runTask('dm:data', array(), array('env' => $options['env']));
			$this->runTask('se:generate-db-dump', array(), array('env' => $options['env']));
		}

		if(empty($options['files']))
		{
			$file = dmOs::join(sfConfig::get('sf_root_dir'), 'config', 'dm', 'php-fixtures.yml');
			if(!file_exists($file)) $this->getFilesystem()->touch(array($file));

			$config = sfYaml::load($file);

			if(!is_array($config)){
				return;
			}

			if(!isset($config['php']))
			{
				$this->logBlock($file . ' does not contain any php: key');
			}

			$____files = $config['php'];
		}else{
			$____files = $options['files'];
		}

		$this->cache = new seDmDoctrineFixtureTopCache();

		try{
			$options['global-transaction'] && $this->withDatabase()->getDatabase('doctrine')->getDoctrineConnection()->beginTransaction();
			
			foreach($____files as $php)
			{
				$this->logSection('php', 'loading ' . $php);
				require $php;
			}
			
			$options['global-transaction'] && $this->withDatabase()->getDatabase('doctrine')->getDoctrineConnection()->commit();
		}
		catch(Exception $up)
		{
			$options['global-transaction'] && $this->withDatabase()->getDatabase('doctrine')->getDoctrineConnection()->rollback();
			
			throw $up;
		}
	}

	public function logPhp($msg, $line)
	{
		$this->logSection($line, $msg);
	}

	public function getCache()
	{
		return $this->cache;
	}
}
