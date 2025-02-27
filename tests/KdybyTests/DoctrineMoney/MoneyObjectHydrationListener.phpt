<?php

/**
 * Test: Kdyby\DoctrineMoney\MoneyObjectHydrationListener.
 *
 * @testCase KdybyTests\DoctrineMoney\MoneyObjectHydrationListenerTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\DoctrineMoney
 */

namespace KdybyTests\DoctrineMoney;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Tools\SchemaTool;
use Kdyby\Money\Currency;
use Kdyby\Money\Money;
use Kdyby\Money\NullCurrency;
use Kdyby;
use Doctrine\ORM\Events as ORMEvents;
use KdybyTests\IntegrationTestCase;
use Nette;
use Tester\Assert;
use Tester;



require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MoneyObjectHydrationListenerTest extends IntegrationTestCase
{

	/**
	 * @var \Nette\DI\Container|\SystemContainer
	 */
	private $container;

	/**
	 * @var \Doctrine\ORM\EntityManagerInterface
	 */
	private $em;

	/**
	 * @var \Kdyby\DoctrineMoney\Mapping\MoneyObjectHydrationListener
	 */
	private $listener;



	protected function setUp()
	{
		parent::setUp();

		$this->container = $this->createContainer('order');

		$this->em = $this->container->getByType('Doctrine\ORM\EntityManagerInterface');
		$this->listener = $this->container->getByType('Kdyby\DoctrineMoney\Mapping\MoneyObjectHydrationListener');
	}



	/**
	 * @dataProvider dataEntityClasses
	 */
	public function testFunctional($className)
	{
		$class = $this->em->getClassMetadata($className);

		// assert that listener was binded to entity
		Assert::same(array(
			ORMEvents::postLoad => array(array('class' => 'Kdyby\\DoctrineMoney\\Mapping\\MoneyObjectHydrationListener', 'method' => 'postLoad')),
			ORMEvents::preFlush => array(array('class' => 'Kdyby\\DoctrineMoney\\Mapping\\MoneyObjectHydrationListener', 'method' => ORMEvents::preFlush)),
		), $class->entityListeners);

		$this->generateDbSchema();
		$currencies = $this->em->getRepository(Kdyby\Money\Currency::class);

		// test money hydration
		$this->em->persist(new $className(1000, 'CZK'));
		$this->em->flush();
		$this->em->clear();

		/** @var OrderEntity $order */
		$order = $this->em->find($className, 1);
		Assert::equal(new Kdyby\Money\Money(1000, $currencies->find('CZK')), $order->getMoney());
	}



	public function dataEntityClasses()
	{
		return array(
			array(OrderEntity::class),
			array(SpecificOrderEntity::class),
		);
	}



	/**
	 * @dataProvider dataHydratedEntities
	 */
	public function testNullCurrencyHydration($expectedMoney, OrderEntity $entity)
	{
		$this->listener->postLoad($entity, new LifecycleEventArgs($entity, $this->em));
		Assert::equal($expectedMoney, $entity->getMoney());
	}



	public function dataHydratedEntities()
	{
		$czk = new Currency('CZK', 100);
		$null = new NullCurrency();

		return array(
			array(new Money(100, $czk), new OrderEntity(100, $czk)),
			array(new Money(0, $czk), new OrderEntity(0, $czk)),
			array(new Money(0, $null), new OrderEntity(0, new NullCurrency())),
			array(new Money(100, $null), new OrderEntity(100, NULL)),
			array(new Money(0, $null), new OrderEntity(0, NULL)),
		);
	}



	public function testRepeatedLoading()
	{
		$this->generateDbSchema();
		$currencies = $this->em->getRepository(Kdyby\Money\Currency::class);

		// test money hydration
		$this->em->persist(new OrderEntity(1000, 'CZK'));
		$this->em->flush();
		$this->em->clear();

		/** @var OrderEntity $order */
		$order = $this->em->find(OrderEntity::class, 1);
		Assert::equal(new Kdyby\Money\Money(1000, $currencies->find('CZK')), $order->getMoney());

		// following loading should not fail
		$order2 = $this->em->createQueryBuilder()
			->select("o")
			->from(OrderEntity::class, "o")
			->where("o.id = :id")->setParameter("id", 1)
			->getQuery()->getSingleResult();
		Assert::same($order, $order2);
	}



	private function generateDbSchema()
	{
		$schema = new SchemaTool($this->em);
		$schema->createSchema($this->em->getMetadataFactory()->getAllMetadata());
	}

}

\run(new MoneyObjectHydrationListenerTest());
