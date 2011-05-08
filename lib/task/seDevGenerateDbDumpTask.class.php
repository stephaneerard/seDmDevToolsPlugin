<?php
/**
 *
 * @author serard
 *
 */
class seDevGenerateDbDumpTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();


		$this->namespace        = 'se';
		$this->name             = 'generate-db-dump';
		$this->briefDescription = 'Generates a db dump file';
		$this->detailedDescription = <<<EOF
The [ItSsGenerateStayAdminUser|INFO] task does generates a db dump file using mysqldump utility
Call it with:

  [php symfony seDevGenerateDbDump|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->withDatabase();

		$this->logSection('se', 'generating dump of db');
		$file = sprintf('data/dump/data_%s.sql', $options['env']);

		if(file_exists($file))
		{
			$this->getFilesystem()->remove(array($file));
		}
		

		$user = $this->withDatabase()->getDatabase('doctrine')->getParameter('username');
		$password = $this->withDatabase()->getDatabase('doctrine')->getParameter('password');
		list($host, $db, $null) = explode(';', $this->withDatabase()->getDatabase('doctrine')->getParameter('dsn'));
		$db = explode('=', $db);
		$db = $db[1];

		$file = sprintf('data/dump/data_%s.sql', $options['env']);
		
		`mysqldump -u $user -p$password --disable-keys --add-drop-database $db > $file`;

	}
}
