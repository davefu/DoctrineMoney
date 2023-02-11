<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineMoney\DI;

use Davefu\KdybyContributteBridge\DI\Helper\MappingHelper;
use Kdyby;
use Kdyby\Events\DI\EventsExtension;
use Nette\PhpGenerator as Code;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MoneyExtension extends Nette\DI\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'cache' => 'filesystem',
		'currencies' => array(),
		'rates' => array(
			'static' => array(),
		),
	);



	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('moneyHydrationListener'))
			->setClass('Kdyby\DoctrineMoney\Mapping\MoneyObjectHydrationListener', array(
				Kdyby\DoctrineCache\DI\Helpers::processCache($this, $config['cache'], 'money'),
			))
			->addTag(EventsExtension::TAG_SUBSCRIBER);

		// @deprecated
		$builder->addDefinition($this->prefix('rates'))
			->setClass('Kdyby\Money\Exchange\StaticExchanger', array($config['rates']['static']));
	}



	public function afterCompile(Code\ClassType $class)
	{
		$config = $this->getConfig($this->defaults);

		// @deprecated
		if (!empty($config['currencies'])) {
			$init = $class->addMethod('_kdyby_initialize_currencies');
			$init->setVisibility('protected');

			foreach ($config['currencies'] as $code => $details) {
				$details = Nette\DI\Config\Helpers::merge($details, array('number' => NULL, 'name' => NULL, 'decimals' => 0, 'countries' => array()));
				$init->addBody('?(?, ?);', array(new Code\PhpLiteral('Kdyby\Money\CurrencyTable::registerRecord'), strtoupper($code), $details));
			}

			$class->methods['initialize']->addBody('$this->_kdyby_initialize_currencies();');
		}
	}



	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('money', new MoneyExtension());
		};
	}
	
	public function beforeCompile(): void {
		MappingHelper::of($this)
			->addXml('Kdyby\Money', __DIR__ . '/metadata');
	}
}
