<?php
/**
 *
 * @author serard
 *
 */
class seDevReloadDataTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();

		$this->addOption('with-dump', 'l', sfCommandOption::PARAMETER_NONE, 'Use data dump');
		$this->addOption('with-php-fixtures', 'p', sfCommandOption::PARAMETER_NONE, 'Loads php fixtures after data reload');
		

		$this->namespace        = 'se';
		$this->name             = 'reload-data';
		$this->briefDescription = 'Reloads data';
		$this->detailedDescription = <<<EOF
The [ItSsGenerateStayAdminUser|INFO] task does reload data
Call it with:

  [php symfony seDevGenerateStayAdminUser|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->withDatabase();

		$this->logSection('se', 'reloading data');
		
		if($options['with-dump'])
		{
			$this->runTask('se:load-db-dump', array(), array('env' => $options['env']));
		}
		else{
			$this->runTask('dm:truncate-tables', array(), array('env' => $options['env']));
			$this->runTask('dm:data', array(), array('env' => $options['env']));
			$this->runTask('se:generate-db-dump', array(), array('env' => $options['env']));
		}
		
		if($options['with-php-fixtures'])
		{
			$this->runTask('se:load-php-data', array(), array('env' => $options['env']));
		}
	}
}
