<?php

/**
 * Test: Kdyby\Money\Extension.
 *
 * @testCase Kdyby\Money\ExtensionTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Money
 */

namespace KdybyTests\DoctrineMoney;

use Doctrine\DBAL\Types\Type;
use Kdyby;
use Nette;
use Tester;
use Tester\Assert;



require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ExtensionTest extends \KdybyTests\IntegrationTestCase
{

	public function testRegisterTypes()
	{
		//TODO: due to switching to Nettrine\ORM, DB types has to be registered in config
		$container = $this->createContainer();
		/** @var \Doctrine\DBAL\Connection $connection */
		try {
		$connection = $container->getByType('Doctrine\DBAL\Connection');
		$connection->connect(); // initializes the types
		dump(\Doctrine\DBAL\Types\Type::getTypesMap());
		} catch (\Throwable $e) {
			throw $e;
			dump($e->getTrace()[0]);
			die();
		}

		Assert::true(Type::getType('money') instanceof Kdyby\DoctrineMoney\Types\Money);
	}

}

\run(new ExtensionTest());
