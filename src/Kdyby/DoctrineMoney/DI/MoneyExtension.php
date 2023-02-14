<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\DoctrineMoney\DI;

use Davefu\KdybyContributteBridge\Cache\Helpers as CacheHelpers;
use Davefu\KdybyContributteBridge\DI\Helper\MappingHelper;
use Kdyby\Events\DI\EventsExtension;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator as Code;
use Nette\Schema\Expect;
use Nette\Schema\Helpers as SchemaHelpers;
use Nette\Schema\Schema;
use stdClass;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property-read stdClass $config
 */
class MoneyExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema {
		return Expect::structure([
			'cache' => Expect::string('filesystem'),
			'currencies' => Expect::array(),
			'rates' => Expect::structure([
				'static' => Expect::array(),
			]),
		]);
	}

	public function loadConfiguration()
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('moneyHydrationListener'))
			->setFactory('Kdyby\DoctrineMoney\Mapping\MoneyObjectHydrationListener', [
				CacheHelpers::processCache($this, $config->cache, 'money'),
			])
			->addTag(EventsExtension::TAG_SUBSCRIBER);

		// @deprecated
		$builder->addDefinition($this->prefix('rates'))
			->setFactory('Kdyby\Money\Exchange\StaticExchanger', [$config->rates->static]);
	}



	public function afterCompile(Code\ClassType $class)
	{
		$config = $this->config;

		// @deprecated
		if (!empty($config->currencies)) {
			$init = $class->addMethod('_kdyby_initialize_currencies');
			$init->setVisibility('protected');

			foreach ($config->currencies as $code => $details) {
				$details = SchemaHelpers::merge($details, array('number' => NULL, 'name' => NULL, 'decimals' => 0, 'countries' => array()));
				$init->addBody('?(?, ?);', array(new Code\PhpLiteral('Kdyby\Money\CurrencyTable::registerRecord'), strtoupper($code), $details));
			}

			$class->methods['initialize']->addBody('$this->_kdyby_initialize_currencies();');
		}
	}



	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('money', new MoneyExtension());
		};
	}
	
	public function beforeCompile(): void {
		MappingHelper::of($this)
			->addXml('Kdyby\Money', __DIR__ . '/metadata');
	}
}
