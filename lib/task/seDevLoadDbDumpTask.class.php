<?php
/**
 *
 * @author serard
 *
 */
class seDevLoadDbDumpTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();

		$this->addOption('file', 'f', sfCommandOption::PARAMETER_OPTIONAL, 'File to dump to', false);

		$this->namespace        = 'se';
		$this->name             = 'load-db-dump';
		$this->briefDescription = 'Loads a db dump file';
		$this->detailedDescription = <<<EOF
The [ItSsGenerateStayAdminUser|INFO] task does loads a db dump file
Call it with:

  [php symfony seDevGenerateDbDump|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->withDatabase();

		$this->logSection('se', 'loading data using db dump');
		if(!$options['file'])
		{
			$file = sprintf('data/dump/data_%s.sql', $options['env']);
		}
		else
		{
			$file = $options['file'];
		}

		if(!file_exists($file))
		{
			throw new RuntimeException(sprintf('file %s does not exist', $file));
		}

		$user = $this->withDatabase()->getDatabase('doctrine')->getParameter('username');
		$password = $this->withDatabase()->getDatabase('doctrine')->getParameter('password');
		list($host, $db, $null) = explode(';', $this->withDatabase()->getDatabase('doctrine')->getParameter('dsn'));
		$db = explode('=', $db);
		$db = $db[1];

		`cat $file| mysql -u $user -p$password -D $db`;
	}
}
