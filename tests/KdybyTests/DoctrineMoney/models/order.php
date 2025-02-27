<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyTests\DoctrineMoney;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Money\Currency;
use Kdyby\Money\Money;



/**
 * @ORM\Entity()
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *    "order" = "OrderEntity",
 *    "specific" = "SpecificOrderEntity",
 * })
 */
class OrderEntity
{

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	public $id;

	/**
	 * @ORM\Column(type="money", options={"currency":"obscureNamedCurrencyField"})
	 * @var Money
	 */
	private $money;

	/**
	 * @ORM\ManyToOne(targetEntity="\Kdyby\Money\Currency", cascade={"persist"})
	 * @var Currency
	 */
	public $obscureNamedCurrencyField;



	public function __construct($money = 0, $currency = NULL)
	{
		$this->money = $money;

		if ($currency !== NULL) {
			$this->obscureNamedCurrencyField = $currency instanceof Currency
				? $currency : new Currency($currency, 100, 'Testing currency');
		}
	}

	public function getId(): int {
		return $this->id;
	}

	public function getObscureNamedCurrencyField(): Currency {
		return $this->obscureNamedCurrencyField;
	}

	public function getMoney(): Money
	{
		return $this->money;
	}

	public function setObscureNamedCurrencyField(Currency $obscureNamedCurrencyField): void {
		$this->obscureNamedCurrencyField = $obscureNamedCurrencyField;
	}


	public function setMoney(Money $money): void {
		$this->money = $money;
	}
}

/**
 * @ORM\Entity()
 */
class SpecificOrderEntity extends OrderEntity
{

}
