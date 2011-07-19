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
		
		$this->addOption('file', 'f', sfCommandOption::PARAMETER_OPTIONAL, 'File to dump to', false);
		$this->addOption('suffix', 's', sfCommandOption::PARAMETER_OPTIONAL, 'Suffix for generated file', false);

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
		if(!$options['file'])
		{
			$file = sprintf('data/dump/data_%s%s.sql', $options['env'], $options['suffix']);
		}
		else
		{
			$file = $options['file'];
		}

		if(file_exists($file))
		{
			$this->getFilesystem()->remove(array($file));
		}
		
		$this->logSection('file', $file);
		

		$user = $this->withDatabase()->getDatabase('doctrine')->getParameter('username');
		$password = $this->withDatabase()->getDatabase('doctrine')->getParameter('password');
		list($host, $db, $null) = explode(';', $this->withDatabase()->getDatabase('doctrine')->getParameter('dsn'));
		$db = explode('=', $db);
		$db = $db[1];

		`mysqldump -u $user -p$password --disable-keys --add-drop-database $db > $file`;

	}
}
