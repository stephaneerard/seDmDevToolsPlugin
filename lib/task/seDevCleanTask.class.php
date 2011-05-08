<?php
/**
 *
 * @author serard
 *
 */
class seDevCleanTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();

		$this->addOption('sudo-pwd', 'p', sfCommandOption::PARAMETER_OPTIONAL, 'The sudo password', false);
		
		$this->namespace        = 'se';
		$this->name             = 'clean';
		$this->briefDescription = 'Clears cache & restart apache (clear APC cache)';
		$this->detailedDescription = <<<EOF
The [seDevCleanTask|INFO] task does clear the cache & restart apache (clear APC cache)
Call it with:

  [php symfony seDevCleanTask|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->runTask('cache:clear');
		
		if($pwd = $options['sudo-pwd'])
		{
			`echo $pwd | sudo -S apache2ctl restart`;
		}
		else
		{
			`echo \$SUDOPWD | sudo -S apache2ctl restart`;
		}
		
		
	}
}
