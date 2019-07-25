<?

// ---------------------------------------------

// Inheritance

// parent
{% block stylesheets %}
	<link href="{{ asset('css/main.css') }}" rel="stylesheet" />
{% endblock %}

// child
{% block stylesheets %}
    {{ parent() }}

    <link href="{{ asset('css/contact.css') }}" rel="stylesheet" />
{% endblock %}

// ---------------------------------------------

/* Будет искать у сущности свойство title.
 * Свойства, как правило, private, но сработает метод getTitle()
 * 
 * Если выводится свойство типа boolean, например published, то будет поиск методов isPublished(), hasPublished()
 * 
 * Если article не объект, а массив, то твиг выведет элемент массива
 * */
{{ article.title }}

// -------------------------------------------------

// Twig Extensions
// https://symfonycasts.com/screencast/symfony-doctrine/twig-extension

/* Одна из практик создавать 1 класс AppExtension, в к-м будут все кастомные функции и фильтры твига на весь проект
 * 
 * */
> php bin/console make:twig-extension

// -------------------------------------------------

// Тернарный оператор с функцией
{{ article.publishedAt ? article.publishedAt|date('Y-m-d') : 'unpublished' }}

// -------------------------------------------------

// KnpTimeBundle - вместо даты выводит five minutes ago, two weeks ago
> composer require knplabs/knp-time-bundle

// -------------------------------------------------

/* Контейнер симфони не создает экземпляры сервисов пока они не используются.
 * Если использоват hit-type в конструкторе твига
 * public function __construct(MarkdownHelper $markdownHelper)
 * то инстанцирование будет происходить всегда
 * 
 * Во избежание этого создается Service Subscriber
 * https://symfonycasts.com/screencast/symfony-doctrine/service-subscriber
 * */
 
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Psr\Container\ContainerInterface;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
	private $container;
	
	public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function processMarkdown($value)
    {
        return $this->container
            ->get(MarkdownHelper::class)
            ->parse($value);
    }
    
    public static function getSubscribedServices()
    {
        return [
            MarkdownHelper::class,
        ];
    }
}

// -------------------------------------------------

// Вывод списка из бд в шаблоне https://symfonycasts.com/screencast/symfony-doctrine/repository
// Экшен homepage в котроллере

// -------------------------------------------------
