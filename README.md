# Оглавление #

* [Описание](#markdown-header-_1)
* [Компоненты](#markdown-header-_2)
* [Установка](#markdown-header-_3)
* [Библиотека componentsBase](#markdown-header-componentsbase)
* [Шаблон сайта при начальных условиях](#markdown-header-_4)

# Описание #
Модуль содержит набор часто используемых компонентов на различных проектах. Преимущества данных компонентов по сравнению со стандартными:

1. Компоненты реализуются на основе [библиотеки componentsBase](#markdown-header-componentsbase) в которой поддерживаются исключения (exception).

2. Компоненты реализуются через наследование: любой метод компонента может быть переопределен в другом компоненте.

# Компоненты #

* materials.list - для вывода списков (элементы, секции инфоблока)
* materials.detail - на основе materials.list с ограничением в 1 элемент
* catalog.list - на основе materials.list + данные каталога (цены, кол-во товара и т.д.)
* catalog.detail - на основе materials.detail тоже самое что и catalog.list с ограничением в 1 элемент

# Установка #

Модуль распространяется через [composer](https://getcomposer.org/doc/00-intro.md).
В корне сайта, где установлен битрикс, необходимо выполнить следующие команды:

```
#!bash
composer config repositories.notagency vcs git@bitbucket.org:notagency/notagency.base.git
composer require notagency/base
```

Модуль должен появится в списке *Marketplace → Установленные решения*.
Далее следует стандартная процедура установки marketplace-модуля.

# Библиотека componentsBase #

Основная библиотека для всех компонентов реализованная на основе *CBitrixComponent*. 
Библиотека поддерживает исключения. 

В методе [executeBase](https://bitbucket.org/notagency/notagency.base/src/cb212c88ee5361566ab3af6f3c6e0fe75997bfa1/lib/componentsbase.php?at=master&fileviewer=file-view-default#componentsbase.php-47) устанавливается порядок выполнения методов компонента на основе *componentsBase*:

```
#!php4
<?
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
?>
```

# Шаблон сайта при начальных условиях #
В папке boilerplate/templates размещен шаблон с наиболее частыми начальными условиями при создании шаблона сайта. 
Например, в header есть код подключения меню с шаблоном top, а в footer есть подключение включаемых областей для вывода, копирайта.
Для установки необходимо вручную скопировать шаблон в папку local/templates