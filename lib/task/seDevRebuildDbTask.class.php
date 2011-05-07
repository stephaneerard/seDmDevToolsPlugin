<?php
/**
 *
 * @author serard
 *
 */
class seDevRebuildDbTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();

		$this->addOption('reload', 'r', sfCommandOption::PARAMETER_NONE, 'Reloads data');
		$this->addOption('with-dump', 'd', sfCommandOption::PARAMETER_NONE, 'Use dump to load');

		$this->namespace        = 'se';
		$this->name             = 'rebuild-db';
		$this->briefDescription = 'Rebuilds the database';
		$this->detailedDescription = <<<EOF
The [ItSsGenerateStayAdminUser|INFO] task does rebuilds the database
Call it with:

  [php symfony seDevRebuildDb|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->withDatabase();

		$this->runTask('dm:setup', array(), array('env' => $options['env'], 'clear-db' => true, 'no-confirmation' => true, 'dont-load-data' => true));
		
		if($options['reload'])
		{
			$this->runTask('se:reload-data', array(), array('env' => $options['env'], 'with-dump' => $options['with-dump']));
		}
	}
}
