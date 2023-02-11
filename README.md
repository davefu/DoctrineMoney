Kdyby/DoctrineMoney
======

[![Build Status](https://travis-ci.org/Kdyby/DoctrineMoney.svg?branch=master)](https://travis-ci.org/Kdyby/DoctrineMoney)
[![Downloads this Month](https://img.shields.io/packagist/dm/kdyby/doctrine-money.svg)](https://packagist.org/packages/kdyby/doctrine-money)
[![Latest stable](https://img.shields.io/packagist/v/kdyby/doctrine-money.svg)](https://packagist.org/packages/kdyby/doctrine-money)
[![Join the chat at https://gitter.im/Kdyby/Help](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Kdyby/Help)


Requirements
------------

Kdyby/DoctrineMoney requires PHP 7.1 or higher.

- [Kdyby/Money](https://github.com/kdyby/money)
- [Nettrine/ORM](https://github.com/contributte/doctrine-orm)
- [Nette Framework](https://github.com/nette/nette)


Installation
------------

The best way to install Kdyby/DoctrineMoney is using  [Composer](http://getcomposer.org/):

```js
"require": {
	"kdyby/doctrine-money": "@dev",
	"kdyby/money": "@dev"
}
```

and now run the update

```sh
$ composer update
```


Documentation
------------

###Nettrine/ORM extension proxies
Please use proxies for Doctrine related extensions (Nettrine packages) from [Davefu/Kdyby-Contributte-Bridge](https://github.com/davefu/Kdyby-Contributte-Bridge) package.
The package provides few helper classes to simplify mapping configuration of this extension. The proxy package also provides cooperation of Kdyby/Events and Nettrine/ORM (Nettrine/DBAL respectively), if you use this package for event functionality.

###Custom database types has to be registered in main project config file
In older version of this package, that relied on Kdyby/Doctrine package, DB types were registered automatically (via proper interfaces in DI extension).
Doctrine dependency was changed to Nettrine/ORM package, that does not provide this feature, so types have to be registered in config of your project.

```yaml
extensions:
  nettrine.dbal: Davefu\KdybyContributteBridge\DI\DbalExtensionProxy
	nettrine.orm: Davefu\KdybyContributteBridge\DI\OrmExtensionProxy

nettrine.dbal:
	connection:
		types:
			money:
				class: Kdyby\DoctrineMoney\Types\Money
				commented: true

		typesMapping:
			money: integer
```


-----

Homepage [http://www.kdyby.org](http://www.kdyby.org) and repository [http://github.com/Kdyby/DoctrineMoney](http://github.com/Kdyby/DoctrineMoney).
