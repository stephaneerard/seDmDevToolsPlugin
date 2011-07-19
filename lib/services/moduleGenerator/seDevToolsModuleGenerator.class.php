<?php
class seDevToolsModuleGenerator extends dmMicroCache
{

	/**
	 * @var sfServiceContainer
	 */
	protected $container;

	/**
	 * @var dmContext
	 */
	protected $context;
	
	protected $dispatcher;
	
	protected $formatter;
	
	protected $options;

	protected $plugin_admin_skeleton_dir;
	
	protected $plugin_front_skeleton_dir;
	
	protected $app_admin_skeleton_dir;
	
	protected $app_front_skeleton_dir;
	
	protected $plugin_uc_module_name;

	/**
	 * @param sfServiceContainer $container
	 */
	public function __construct(
																sfServiceContainer $container, 
																dmContext $context, 
																$dispatcher, 
																$plugin_admin_skeleton_dir, 
																$plugin_front_skeleton_dir,
																$app_admin_skeleton_dir, 
																$app_front_skeleton_dir
																)
	{
		$this->container = $container;
		$this->context = $context;
		$this->dispatcher = $dispatcher;
		
		$this->plugin_admin_skeleton_dir = realpath($plugin_admin_skeleton_dir);
		$this->plugin_front_skeleton_dir = realpath($plugin_front_skeleton_dir);
		$this->app_admin_skeleton_dir = realpath($app_admin_skeleton_dir);
		$this->app_front_skeleton_dir = realpath($app_front_skeleton_dir);
	}

	public function execute($options)
	{
		$this->formatter = $options['formatter'];
		unset($options['formatter']);

		$this->plugin_admin_action_name = $options['plugin-admin-action'];
		$this->plugin_front_action_name = $options['plugin-front-action'];
		
		unset($options['plugin-admin-action'], $options['plugin-front-action']);
		
		$this->options = $options;
		
		$this->generateApplicationsModules();
		$this->generatePluginsModules();
	}

	public function isPluginExcluded($plugin)
	{
		return in_array($plugin, $this->options['exclude-plugins']) || !in_array($plugin, $this->options['only-plugins']);
	}

	public function generateApplicationsModules()
	{
		foreach(array('admin', 'front') as $app)
		{

		}
	}

	public function  generatePluginsModules()
	{
		foreach($this->context->getConfiguration()->getPlugins() as $plugin)
		{
			if($this->isPluginExcluded($plugin)) continue;
			$config = $this->getPluginModulesConfig($plugin);
			if($config)
			{
				$modules = $this->findNonModelModules($config);
				foreach($modules as $moduleKey => $moduleConfig)
				{
					foreach(array('admin', 'front') as $app)
					{
						if(dmArray::get($moduleConfig[0], $app, false))
						{
							$this->generatePluginModule($moduleKey, $moduleConfig[0], $this->context->getConfiguration()->getPluginConfiguration($plugin), $app);
						}
					}
				}
			}
		}
	}


	protected function getPluginModuleDir($plugin, $module, $app)
	{
		return sprintf('%s/modules/%s', $plugin->getRootDir(), $module . ($app === 'admin' ? 'Admin' : ''));
	}


	protected function generatePluginModule($moduleKey, $moduleConfig, $plugin, $app)
	{
		$moduleDir = $this->getPluginModuleDir($plugin, $moduleKey, $app);
		$moduleName = $moduleKey . ($app === 'admin' ? 'Admin' : '');
		if(file_exists($moduleDir) && $this->dontRegeneratePluginModule($moduleName)) return;
		$this->logSection('module-generator', sprintf('%s %s %s', $plugin->getName(), $moduleKey, $moduleDir));
		
		if(!$this->options['dry'])
		{
			$this->createModuleDirUsingSkeleton($moduleDir, $this->getSkeletonDir('plugin', $app), $this->getPluginModuleTokens($moduleKey, $plugin, $app), $moduleName, $plugin, $app);
		}
	}
	
	protected function getPluginModuleTokens($moduleKey, $plugin, $app)
	{
		return array(
			'PLUGIN_NAME' 		=> $plugin->getName(),
			'MODULE_NAME' 		=> $moduleKey,
			'AUTHOR_NAME' 		=> '',
			'UC_MODULE_NAME' 	=> $app === 'admin' ? $this->plugin_admin_action_name : $this->plugin_front_action_name
		);
	}
	
	protected function createModuleDirUsingSkeleton($moduleDir, $skeletonDir, $tokens, $moduleName, $plugin, $app)
	{
		$fs = $this->container->get('filesystem');
		
		if(file_exists($moduleDir)) $fs->deleteDir($moduleDir);
		$files = sfFinder::type('files')->mindepth(1)->in($skeletonDir);
		foreach($files as $file)
		{
			$destFile = dmOs::join($moduleDir, str_replace($skeletonDir, '', $file));
			
			$fs->mkdir(dirname($file));
			
			$fs->copy($file, $destFile);
			$fs->replaceTokens($destFile, '##', '##', $this->getPluginModuleTokens($moduleName, $plugin, $app));
			
		}
	}
	
	protected function getSkeletonDir($kind, $app)
	{
		return $this->{sprintf('%s_%s_skeleton_dir', $kind, $app)};
	}
	
	protected function dontRegeneratePluginModule($moduleKey)
	{
		return !dmArray::get($this->options['regenerate-all'], $moduleKey, false);
	}

	protected function findNonModelModules($config)
	{
		$modules = array();

		foreach($config as $typeName => $typeConfig)
		{
			foreach($typeConfig as $spaceName => $spaceConfig)
			{
				foreach($spaceConfig as $moduleKey => $moduleConfig)
				{
					if(!dmArray::get($moduleConfig, 'model', false))
					{
						$modules[$moduleKey] = array($moduleConfig);
					}
				}
			}
		}

		return $modules;
	}

	protected function getPluginModulesConfig($plugin)
	{
		$modulesConfigPath = $this->getModulesConfigFilePath($plugin);
		if(!file_exists($modulesConfigPath)) return false;

		return sfYaml::load($modulesConfigPath);
	}

	protected function getModulesConfigFilePath($plugin)
	{
		return sprintf('%s/config/dm/modules.yml', $this->context->getConfiguration()->getPluginConfiguration($plugin)->getRootDir());
	}

	public function logSection($section, $message, $size = null, $style = 'INFO')
	{
		$this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($section, $message, $size, $style))));
	}
}