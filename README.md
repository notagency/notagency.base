## Оглавление

* [Описание](#Описание)
* [Компоненты](#Компоненты)
* [Установка](#Установка)

## Описание
Базовые компоненты на основе модуля [notagency.base](https://github.com/notagency/notagency.base-module)

## Компоненты
* materials.list - для вывода списков (элементы, секции инфоблока)
* materials.detail - на основе materials.list с ограничением в 1 элемент
* catalog.list - на основе materials.list + данные каталога (цены, кол-во товара и т.д.)
* catalog.detail - на основе materials.detail тоже самое что и catalog.list с ограничением в 1 элемент
* form.result.new - для вывода веб-форм на основе модуля веб-формы

## Установка
Модуль распространяется через [composer](https://getcomposer.org/doc/00-intro.md) и опубликован на [packagist.org](https://packagist.org/packages/notagency/notagency.base).

В корне сайта, где установлен битрикс, необходимо выполнить:

```bash
composer require notagency/notagency.base
```

Модуль должен появиться в списке *Marketplace → Установленные решения*.
Далее следует стандартная процедура установки marketplace-модуля.