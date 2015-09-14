<?php

class StatementTest extends PHPUnit_Framework_TestCase
{
    /** @var \PDO */
    private $conn;

    protected function setUp()
    {
        $this->conn = new PDO('sqlite::memory:');
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->query("CREATE TABLE books (id INT PRIMARY KEY, name CHAR, category_id INT)");
    }

    public function testHydrate()
    {
        $mock = $this->getMockBuilder('Mappa\Hydrator')
            ->setMethods(['hydrate'])
            ->getMock();

        $this->conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, [Mappa\Statement::class, [$mock]]);

        $stmt = $this->conn->prepare("SELECT * FROM books");

        $this->assertInstanceOf(Mappa\Statement::class, $stmt);

        $stmt->execute();

        $mock->expects($this->once())
            ->method('hydrate')
            ->with($this->identicalTo($stmt), $this->equalTo([StdClass::class]))
            ->willReturn(false);

        $this->assertSame(false, $stmt->hydrate([StdClass::class]));
    }

    public function testHydrateAll()
    {
        $mock = $this->getMockBuilder('Mappa\Hydrator')
            ->setMethods(['hydrateAll'])
            ->getMock();

        $this->conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, [Mappa\Statement::class, [$mock]]);

        $stmt = $this->conn->prepare("SELECT * FROM books");

        $this->assertInstanceOf(Mappa\Statement::class, $stmt);

        $stmt->execute();

        $mock->expects($this->once())
            ->method('hydrateAll')
            ->with($this->identicalTo($stmt), $this->equalTo([StdClass::class]))
            ->willReturn([]);

        $this->assertSame([], $stmt->hydrateAll([StdClass::class]));
    }
}