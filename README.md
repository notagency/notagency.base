## Оглавление

* [Описание](#Описание)
* [Компоненты](#Компоненты)
* [Установка](#Установка)
* [Библиотека componentsBase](#Библиотека-componentsbase)
* [Шаблон сайта при начальных условиях](#Шаблон-сайта-при-начальных-условиях)

## Описание
Модуль содержит набор часто используемых компонентов на различных проектах. Преимущества данных компонентов по сравнению со стандартными:

1. Компоненты реализуются на основе [библиотеки componentsBase](#markdown-header-componentsbase) в которой поддерживаются исключения (exception).

2. Компоненты реализуются через наследование: любой метод компонента может быть переопределен в другом компоненте.

## Компоненты

* materials.list - для вывода списков (элементы, секции инфоблока)
* materials.detail - на основе materials.list с ограничением в 1 элемент
* catalog.list - на основе materials.list + данные каталога (цены, кол-во товара и т.д.)
* catalog.detail - на основе materials.detail тоже самое что и catalog.list с ограничением в 1 элемент

## Установка

Модуль распространяется через [composer](https://getcomposer.org/doc/00-intro.md) и опубликован на [packagist.org](https://packagist.org/packages/notagency/notagency.base).

В корне сайта, где установлен битрикс, необходимо выполнить:

```bash
composer require notagency/notagency.base
```

Модуль должен появится в списке *Marketplace → Установленные решения*.
Далее следует стандартная процедура установки marketplace-модуля.

## Библиотека componentsBase

Основная библиотека для всех компонентов реализованная на основе *CBitrixComponent*. 
Библиотека поддерживает исключения. 

В методе [executeBase](https://github.com/notagency/notagency.base/blob/master/lib/componentsbase.php#L47-L70) устанавливается порядок выполнения методов компонента, отнаследованного от *componentsBase*:

```php4

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
	if ($this->startCache()) {
		//метод для переопределения
		//основной метод в котором выполняется вся логика компонента
		$this->executeMain();

		//если нужно кеширование шаблона...
		if ($this->cacheTemplate) {
			//подключает шаблон компонента
			//публичный метод
			$this->showResult();
		}

		//алиас для стандартного метода endResultCache()
		//публичный метод
		$this->writeCache();
	}

	//если не нужно кеширование шаблона
	if (!$this->cacheTemplate) {
		$this->showResult();
	}

	//метод для переопределения
	//выполняет эпилог компонента, данные не кешируются
	$this->executeEpilog();

	//останавливает выполнение скрипта, если аякс-запрос
	$this->stopAjax();
}
```

## Шаблон сайта при начальных условиях
В папке [boilerplate/templates](https://github.com/notagency/notagency.base/tree/master/boilerplate/templates/sitename) размещен шаблон с наиболее частыми начальными условиями при создании шаблона сайта. 

Например, в header есть код подключения меню с шаблоном top, а в footer есть подключение включаемых областей для вывода копирайта.

Для установки необходимо вручную скопировать шаблон в папку *local/templates*
