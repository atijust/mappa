<?php

class HydratorTest extends PHPUnit_Framework_TestCase
{
    /** @var \PDO */
    private $conn;

    protected function setUp()
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->query("CREATE TABLE books (id INT PRIMARY KEY, name CHAR, category_id INT)");
        $this->conn->query("CREATE TABLE categories (id INT PRIMARY KEY, name CHAR)");
        $this->conn->query("INSERT INTO books (id, name, category_id) VALUES (1, 'B01', 1)");
        $this->conn->query("INSERT INTO books (id, name, category_id) VALUES (2, 'B02', 2)");
        $this->conn->query("INSERT INTO categories (id, name) VALUES (1, 'C01')");
        $this->conn->query("INSERT INTO categories (id, name) VALUES (2, 'C02')");
    }

    public function testHydrate()
    {
        $hydrator = new Mappa\Hydrator();

        $stmt = $this->conn->prepare("SELECT * FROM books JOIN categories ON categories.id = books.category_id WHERE books.id = ?");
        $stmt->execute([1]);

        $expected = ['books' => Book::build('1', 'B01', '1'), 'categories' => Category::build('1', 'C01'), '' => null,];
        $this->assertEquals($expected, $hydrator->hydrate($stmt, [Book::class, Category::class]));
    }

    public function testHydrate_行が存在しないときfalseを返す()
    {
        $hydrator = new Mappa\Hydrator();

        $stmt = $this->conn->prepare("SELECT * FROM books JOIN categories ON categories.id = books.category_id WHERE books.id = ?");
        $stmt->execute([100]);

        $this->assertFalse($hydrator->hydrate($stmt, [Book::class, Category::class]));
    }

    public function testHydrate_対応するテーブルが存在しないフィールドはStdClassに格納()
    {
        $hydrator = new Mappa\Hydrator();

        $stmt = $this->conn->prepare("SELECT books.name || ' - ' || categories.name AS title FROM books JOIN categories ON categories.id = books.category_id WHERE books.id = ?");
        $stmt->execute([1]);

        $expected = ['books' => null, 'categories' => null, '' => (object)['title' => 'B01 - C01']];
        $this->assertEquals($expected, $hydrator->hydrate($stmt, [Book::class, Category::class]));
    }

    public function testCovertClassToTableCallback()
    {
        $hydrator = new Mappa\Hydrator(function ($class) {
            return 'books';
        });

        $stmt = $this->conn->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([1]);

        $expected = ['books' => (object)['id' => '1', 'name' => 'B01', 'category_id' => '1'], '' => null];
        $this->assertEquals($expected, $hydrator->hydrate($stmt, [StdClass::class]));
    }

    public function testHydrateAll()
    {
        $hydrator = new Mappa\Hydrator();

        $stmt = $this->conn->prepare("SELECT * FROM books JOIN categories ON categories.id = books.category_id WHERE books.id IN (?, ?)");
        $stmt->execute([1, 2]);

        $expected = [
            ['books' => Book::build('1', 'B01', '1'), 'categories' => Category::build('1', 'C01'), '' => null],
            ['books' => Book::build('2', 'B02', '2'), 'categories' => Category::build('2', 'C02'), '' => null],
        ];
        $this->assertEquals($expected, $hydrator->hydrateAll($stmt, [Book::class, Category::class]));
    }

    public function testHydrateAll_行が存在しないとき空配列を返す()
    {
        $hydrator = new Mappa\Hydrator();

        $stmt = $this->conn->prepare("SELECT * FROM books JOIN categories ON categories.id = books.category_id WHERE books.id = ?");
        $stmt->execute([100]);

        $this->assertSame([], $hydrator->hydrateAll($stmt, [Book::class, Category::class]));
    }
}

class Book
{
    public $id;
    public $name;
    public $category_id;

    public static function build($id, $name, $categoryId)
    {
        $book = new self();
        $book->id = $id;
        $book->name = $name;
        $book->category_id = $categoryId;

        return $book;
    }
}

class Category
{
    public $id;
    public $name;

    public static function build($id, $name)
    {
        $category = new self();
        $category->id = $id;
        $category->name = $name;

        return $category;
    }
}