# Описание #
Модуль содержит набор часто используемых компонентов на различных проектах. Преимущества данных компонентов по сравнению со стандартными:

1. Компоненты реализуются на основе библиотеки *componentsBase* в которой поддерживаются исключения (exception).

2. Компоненты реализуются через наследование: любой метод компонента может быть переопределен в другом компоненте.

# Компоненты #

* materials.list - для вывода списков (элементы, секции инфоблока)
* materials.detail - на основе materials.list только с ограничением в 1 элемент
* catalog.list - на основе materials.list только с дополнительным выбором информации модуля каталога (цены, кол-во товара и т.д.)

# Установка #

Модуль распространяется через composer.
В корне сайта, где установлен битрикс, необходимо выполнить следующие команды:

```
#!bash

composer config repositories.notagency vcs git@bitbucket.org:notagency/notagency.base.git
composer require notagency/base

```

Модуль должен появится в списке Marketplace->Установленные решения.
Далее следует стандартная процедура установки marketplace-модуля.

# Библиотека componentsBase #

Основная библиотека для всех компонентов реализованная на основе *CBitrixComponent*. 
Библиотека поддерживает исключения. 

В методе *executeBase* устанавливается порядок выполнения методов компонента на основе componentsBase:

```
#!php

final protected function componentsBase()
{
//подключает необходимые модули указанные в массиве атрибута класса $needModules
//публичный метод
	$this->includeModules();

//проверка параметров компонента, указанных в массиве атрибута класса $checkParams
//приватный метод
	$this->checkParams();

//перезапуск буфера вывода, если аякс-запрос
//приватный метод
	$this->startAjax();

//метод для переопределения
//выполняет пролог компонента, данные не кешируются
	$this->executeProlog();

//начинаем кеширование
	if ($this->startCache())
	{

//метод для переопределения
//основной метод в котором выполняется вся логика компонента
		$this->executeMain();

//если нужно кеширование шаблона...
		if ($this->cacheTemplate)
		{
//подключает шаблон компонента
//публичный метод
			$this->showResult();
		}

//алиас для стандартного метода endResultCache()
//публичный метод
		$this->writeCache();
	}

//если не нужно кеширование шаблона
	if (!$this->cacheTemplate)
	{
		$this->showResult();
	}

//метод для переопределения
//выполняет эпилог компонента, данные не кешируются
	$this->executeEpilog();

//останавливает выполнение скрипта, если аякс-запрос
	$this->stopAjax();
}

```