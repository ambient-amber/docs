<?php
// ---------------------------------------------

// Install
composer create-project symfony/website-skeleton my-project

// Server
>cd your-project/
>composer require symfony/web-server-bundle --dev
>php bin/console server:start
>php bin/console server:stop

// ---------------------------------------------

// Phpstrom plugins
File/Settings/Plugins->Browse Repositories

PHP Annotations
PHP Toolbox
Symfony Plugin - tons of ridiculous auto-completion


File | Settings | Plugins

// ---------------------------------------------

// Router
composer require annotations

/**
 * @Route("/news/{slug}")
 */
public function show($slug){}

// Покажет все пути роутера приложения
> php bin/console debug:router

// ---------------------------------------------

// Flex
composer require "recipe"
Using Symfony Flex to Manage Symfony Applications https://symfony.com/doc/current/setup/flex.html

// ---------------------------------------------

// bin/console

// ---------------------------------------------

// The Web Debug Toolbar
symfony/profiler-pack

// ---------------------------------------------

// Dynamic paths (assets)
composer require symfony/asset

<img src="{{ asset('images/logo.png') }}" />
<img src="{{ absolute_url(asset('images/logo.png')) }}" />

// ---------------------------------------------

// Log var/log/dev.log
use Psr\Log\LoggerInterface;
public function slug(LoggerInterface $logger)
{
	$logger->info('Lucky hm');
}

// ---------------------------------------------

// The following classes & interfaces can be used as type-hints when autowiring(автоматическое связывание):
> ./bin/console debug:autowiring

use Twig\Environment;
public function slug(Environment $twigEnvironment)
{
	$html = $twigEnvironment->render('article/show.html.twig', ['title' => 'title']);
	return new Response($html);
}

// ---------------------------------------------

/* Основное в симфони - сервисы. Они входят в бандлы и находятся в других объектах, называемых контейнерами. Каждый сервис, так же, как и путь, имеет внутренее имя (internal name).
 * Бандлы - система плагинов симфони.
 * Подключение сервисов осуществляется за счет config/bundles.php
 * Система рецептов автоматически обновляет файл, когда подключается бандл.
 * 
 * Симфони - коллекция сервисов, а бандлы - то, что подготавливает объекты сервисов и кладет в контейнер.
 * Например, MonologBundle ответственен за предоставление сервиса логирования.
 * 
 * Бандлы также добавляют новые маршруты.
 * 
 * Контроллеры тоже воспринимаются как сервисы. В config/services.yaml есть
 * 	App\:
 *      resource: '../src/*'
 * И в debug:autowiring контроллеры будут в списке
 * */
 
/* parameters, по сути, создают переменные в конфигах, их можно добавлять в любой файл.
 * Как правило параметры размещаются в одном файле, в этом плане хорош services.yaml:
 * parameters:
 *	    cache_adapter: cache.adapter.apcu
 * Тогда в config/packages/framework.yaml можно использовать переменную:
 * framework:
 * 	cache:
 * 		app: '%cache_adapter%'
 * 
 * Нужно учитывать порядок обработки файлов, очередь такова:
 * 	config/packages
 * 	config/packages/dev
 * 	services.yaml
 * Может получиться что настройка в services.yaml перезаписывает значение в config/packages/dev.
 * На этот случай есть возможность создавать зависимый от среды файл services_dev.yaml
 * */

 /* Переменные из настроек можно использовать и в самих сервисах.
 * В config/services.yaml
 * services:
 * 	_defaults:
 * 		bind:
 * 			$isDebug: true
 * В конструктор сервиса передается настройка
 * 
 * */
public function __construct(bool $isDebug)
{
	$this->isDebug = $isDebug;
}
  
/* Однако ставить значение true/false у $isDebug неверно. В симфони уже есть параметр, отвечающий за это.
 * debug:container обычно выводит список сервисов, но можно вывести и список параметров контейнера
 * > php bin/console debug:container --parameters
 * 
 * В config/services.yaml можно использовать
 * 	$isDebug: '%kernel.debug%'
 * 
 * */
 
/* Использовать такие параметры можно и в конструкторе котроллера
 * */
class LuckyController extends AbstractController
{
	public function __construct(bool $isDebug)
	{
		$this->isDebug = $isDebug;
	}
}
 	
// ---------------------------------------------

// Посмотреть все возможные настройки пакета (bundle) можно через
> ./bin/console config:dump fos_user

// ---------------------------------------------

// Автоматические связывание (autowiring) применяется как параметры в методе контроллера
// The following classes & interfaces can be used as type-hints when autowiring:
> php bin/console debug:autowiring
 
// Например LoggerInterface - ссылка на monolog.logger. Последнее - это id сервиса, который был нам передан.
// Узнать больше о сервисе можно с помощью:
> php bin/console debug:container monolog.logger

// Как правило, debug:container используется для просмотра списка всех сервисов в контейнере, но можно и отфильтровать.
// Например сервисы, содержащие слово log
> php bin/console debug:container --show-private log

// До версии 4.0.5 после создания нового конфига нужно сбрасывать кэш
> php bin/console cache:clear

// ---------------------------------------------

// Окружение dev, prod, test. Определяется константой APP_ENV в файле .env

// В состоянии prod cache не сбрасывается автоматически
> php bin/console cache:clear

// Создание всего кэша, который только нужен. Например для быстрого старта после деплоя
> php bin/console cache:warmup

// ---------------------------------------------

// Создание своего сервиса https://symfonycasts.com/screencast/symfony-fundamentals/create-service

// ---------------------------------------------

/* В Symfony 3 сервисы были публичными и в контроллере их можно было получить с помощью $this->get() или $container->get() по id сервиса.
 * В 4 версии большинство сервисов приватны и подключаться должны через dependency injection (внедрения зависимости)
 * 
 * Например, из-за Client $slack в следующем коде возникнет ошибка
 * 
 * Cannot autowire service ArticleController: argument $slack of method __construct() references class Nexy\Slack\Client, but no such service exists.
 * */
class ArticleController extends AbstractController
{
	public function __construct(bool $isDebug, Client $slack){}
}

/* Это означает нехватку в конфигурации у контейнера информации о том, какой сервис передавать в type-hint
 * В config/services.yaml добавляем
 * services:
 * 		_defaults:
 * 			bind:
 * 				Nexy\Slack\Client: '@nexy_slack.client'
 * Этим определяем свое правило автоматического связывания.
 * 
 * Еще лучше добавить правило так:
 * services:
 * 		Nexy\Slack\Client: '@nexy_slack.client'
 * 
 * Разница в том, что при выводе > php bin/console debug:autowiring это правило отобразится в списке.
 * */
 
/* Вопрос в том, как автоматическое связывание определяет какой сервис передавать.
 * Оно ищет сервис, чей id совпадает с type-hint. Id в данном случае Nexy\Slack\Client
 * Каждый класс в src/ автоматически регистрируется как сервис и получает свой id, совпадающий с названием класса 
 * */

// ---------------------------------------------

// Переменные среды берутся из файлов .env, .end.local, env.dist
// Пример вставки переменной в config/packages/nexy_slack.yaml
nexy_slack:
	endpoint: '%env(SLACK_WEBHOOK_ENDPOINT)%'
	
// В .env
SLACK_WEBHOOK_ENDPOINT = bla_bla

// Раньше подключение .env было в public/index.php, теперь в config/bootstrap.php

// ---------------------------------------------

// setter injection
// За счет required сработает также, как если бы передавали в __construct
class SlackClient
{
	 /**
     * @var LoggerInterface|null
     */
    private $logger;
    
    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger){}
}

// ---------------------------------------------

// The LoggerTrait https://symfonycasts.com/screencast/symfony-fundamentals/logger-trait

// ---------------------------------------------

// MakerBundle - инсрумент для генерации кода
> composer require maker --dev

// Посмотреть доступные комманды в консоли
> php bin/console

// Создаст консольную комманду, запросив для нее имя. Например article:stats. Физически появится src/Command/ArticleStatsCommand.php
> php bin/console make:command

// Новая комманда сразу станет доступна за счет того, что все классы в src/ грузятся как сервисы.
// Симфони отлавливает момент регистрации сервиса - это называется autoconfigure
// В config/services.yaml autoconfigure: true

// Подробнее о возможностях создания комманды https://symfonycasts.com/screencast/symfony-fundamentals/command-fun

// ---------------------------------------------

// Ленивые сервисы
https://symfony.ru/doc/current/service_container/lazy_services.html

// ---------------------------------------------
