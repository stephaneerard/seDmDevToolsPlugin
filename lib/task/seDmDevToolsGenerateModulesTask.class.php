<?php

class seDmDevToolsGenerateModulesTask extends dmContextTask
{
	protected function configure()
	{
		parent::configure();
		
		$this->addOption('exclude-plugins', 'e', sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Except defined plugins');
		$this->addOption('only-plugins', 'o', sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Only defined plugins');
		$this->addOption('delete', 'd', sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Delete & force regeneration of modules ($plugin|$module');
		$this->addOption('regenerate-all', 'r', sfCommandOption::PARAMETER_NONE, 'Regenerates all found modules (BE CAREFULL)');
		
		$this->addOption('plugin-admin-action', null, sfCommandOption::PARAMETER_OPTIONAL, 'The plugin module action extending class', 'dmAdminBaseActions');
		$this->addOption('plugin-front-action', null, sfCommandOption::PARAMETER_OPTIONAL, 'The plugin module action extending class', 'myFrontModuleActions');
		
		$this->namespace        = 'se';
		$this->name             = 'generate-modules';
		$this->briefDescription = 'Generates non-model modules declared in modules.yml files';
		$this->detailedDescription = <<<EOF
The [seDmDevToolsGenerateModules|INFO] task does reads modules.yml and generate unexisting modules
Call it with:

  [php symfony seDmDevToolsGenerateModules|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array())
	{
		$this->getContext()->get('dev_module_generator')->execute(array_merge(array('formatter' => $this->formatter, ), $options));
	}
}
