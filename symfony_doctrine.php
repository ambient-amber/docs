<?php
// Установка
> composer require doctrine

// Создание бд
> php bin/console doctrine:database:create

// С помощью MakerBundle создаем сущность. Нужен php 7.1
> php bin/console make:entity

// Класс сущности и его свойства имеют аннотации с ифморацией:
// класс - это сущность, которую надо сопоставить (mapped to) с бд
// у свойств информация о хранении полей
// погуглить doctrine annotations reference

/* После создания сущности (entity) создаем миграцию.
 * Комманда каждый раз будет сравнивать базу с сущностями, вычислять разницу и генерировать необходимые запросы на обновление.
 * */
> php bin/console make:migration
 
/* Накатываем миграцию
 * В бд создается таблица migration_versions, где фиксируются установленные миграции.
 * */
> php bin/console doctrine:migrations:migrate

// Отчет о состояниях миграций
> php bin/console doctrine:migrations:status

// ------------------------------------------

// Пересоздание базы
> php bin/console doctrine:database:drop --force
> php bin/console doctrine:database:create
> php bin/console doctrine:migrations:migrate

// ------------------------------------------

// Запрос в консоли
> php bin/console doctrine:query:sql "SELECT * FROM genus_scientist"

// ------------------------------------------

// Saving Entities https://symfonycasts.com/screencast/symfony-doctrine/saving-entities

// ------------------------------------------

// Получение данных
class ArticleController extends AbstractController
{
	public function show($slug, EntityManagerInterface $em)
    {
		$repository = $em->getRepository(Article::class);
		
		/** @var Article $article */
        $article = $repository->findOneBy(['slug' => $slug]);
        
        if (!$article) {
            throw $this->createNotFoundException(sprintf('No article for slug "%s"', $slug));
        }
	}
}

// ------------------------------------------

// Свои запросы
// https://symfonycasts.com/screencast/symfony-doctrine/custom-queries

// Go Pro with Doctrine Queries
// https://symfonycasts.com/screencast/doctrine-queries

// Query Logic Re-use & Shortcuts
// https://symfonycasts.com/screencast/symfony-doctrine/query-reuse-tricks

// ------------------------------------------

/* Updating an Entity with New Fields
 * 
 * Для обновления можно ввести имя существующей сущности
 * 
 * */
> php bin/console make:entity

// Выполнение запроса в консоли
> php bin/console doctrine:query:sql "TRUNCATE TABLE article"

// ------------------------------------------

// Миграции для уже существующих таблиц

/* Могут быть проблемы с миграциями, в которых несколько запросов.
 * Часть выполнилась часть нет.
 * PostgreSQL достаточна умна, чтобы откатить первые изменения, если последние не удались.
 * Для других бд рецепт - удалить миграцию, базу, накатить миграции стабильного состояния.
 * Например, ситуация с добавление колонки, которая не может быть пустой. Будет проблема с существующими строками.
 * Реализация в 2 миграции:
 * */

// 1
public function up(Schema $schema) : void
{
	$this->addSql('ALTER TABLE user ADD agreed_terms_at DATETIME DEFAULT NULL');
	$this->addSql('UPDATE user SET agreed_terms_at = NOW()');
}

// 2
public function up(Schema $schema) : void
{
	$this->addSql('ALTER TABLE user CHANGE agreed_terms_at agreed_terms_at DATETIME NOT NULL');
}
// ------------------------------------------




// ------------------------------------------

// Go Pro with Doctrine Queries

// ------------------------------------------

// Пример написания запроса вручную
class CategoryRepository extends EntityRepository
{
	// $mysql = 'SELECT * FROM table';
	// Большое отличие DQL в том, что работа ведется не с таблицами, а с PHP классами.
	// Подробности в Google по Doctrine DQL
	$dql = 'SELECT cat FROM AppBundle\Entity\Category cat';
	
	$query = $this->getEntityManager()->createQuery($dql);
	
	// Дебаг сформированного настоящего запроса
	var_dump($query->getSQL());
	
	// Вернет массив объектов типа Category. В обычном режиме Doctrine всегда возвращает объекты.
	return $query->execute();
}

// Использование QueryBuilder, что обычно и происходит
class CategoryRepository extends EntityRepository
{
	$qb = $this->createQueryBuilder('cat')
            ->addOrderBy('cat.name', 'ASC');
            
	$query = $qb->getQuery();
	
	// Дебаг DQL doctrine query language
	var_dump($query->getDQL());
	
	// Для фильтрации используется, как правило, только andWhere, им можно ограничиться
	$this->createQueryBuilder('cat')
            ->andWhere('cat.name LIKE :searchTerm OR cat.iconKey LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$term.'%')
	
	// Или ->getResult(), к-й внутри содержит execute
	return $query->execute();
}

// ------------------------------------------

// Joins через relations в аннотациях

// Один ко многим - строка в таблице А может быть связана со многими строками таблицы Б, но строка в таблице Б связана только с одной строкой таблицы А
// Таблица А - администраторы ManyToOne
// Таблица Б - статьи OneToMany

class Article
{
	/**
     * @var Category
     *
     * @ORM\OneToMany(targetEntity="Category", inversedBy="categoryId")
     * @ORM\JoinColumn(nullable=false)
     */
    private $authorId;
}

// ------------------------------------------

/*
    Паттерны проектирования, к-е использует doctrine:

    1) Unit of work
        $entity1 = new Entity();
        $em->persist($entity1);

        $entity2 = new Entity();
        $em->persist($entity2);

        $em->flush();

        В начале собираются (persist) действия над объектами в рамках одного процесса
            $entityUpdates()
            $entityInsertions()
            $entityDeletions()
        Потом один раз применяются (flush) собранные изменения

    2) Identity map
        $repo = $em->getRepository(Entity::Class);
        $entity1 = $repo->find(25);
        $entity2 = $repo->find(25);

        $entity1 === $entity2
        Второго запроса не будет
*/

// ------------------------------------------
