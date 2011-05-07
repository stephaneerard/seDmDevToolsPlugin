<?php
/**
 *
 * @author serard
 *
 */
class seDevLoadPhpDataTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();

		$this->addOption('files', 'f', sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Give files to load');
		$this->addOption('rebuild', 'b', sfCommandOption::PARAMETER_NONE, 'Rebuilds all');
		$this->addOption('reload', 'd', sfCommandOption::PARAMETER_NONE, 'Reloads data');
		$this->addOption('with-dump', 'l', sfCommandOption::PARAMETER_NONE, 'Use data dump');

		$this->namespace        = 'se';
		$this->name             = 'load-php-data';
		$this->briefDescription = 'Loads data from php files';
		$this->detailedDescription = <<<EOF
The [ItSsGenerateStayAdminUser|INFO] task does load data from php files
Call it with:

  [php symfony seDevGenerateStayAdminUser|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->withDatabase();

		if($options['rebuild'])
		{
			$this->runTask('se:rebuild-db', array(), array('env' => $options['env']));
		}

		if($options['reload'] || $options['with-dump'])
		{
			$this->runTask('se:reload-data', array(), array('env' => $options['env'], 'with-dump' => $options['with-dump']));
		}
		elseif($options['rebuild'])
		{
			$this->runTask('dm:data', array(), array('env' => $options['env']));
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


		foreach($____files as $php)
		{
			$this->logSection('php', 'loading ' . $php);
			require $php;
		}
	}

	public function logPhp($msg, $line)
	{
		$this->logSection($line, $msg);
	}
}
