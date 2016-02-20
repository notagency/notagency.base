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