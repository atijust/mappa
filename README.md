# Mappa

[![Build Status](https://scrutinizer-ci.com/g/atijust/mappa/badges/build.png?b=master)](https://scrutinizer-ci.com/g/atijust/mappa/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/atijust/mappa/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/atijust/mappa/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/atijust/mappa/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/atijust/mappa/?branch=master)

Mappa is a simple object mapper for PDO.

```php
$pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [Mappa\Statement::class, [new Mappa\Hydrator()]]);

$stmt = $pdo->prepare("SELECT *, books.name || ' - ' || categories.name AS title FROM books JOIN categories ON categories.id = books.category_id WHERE books.id = ?");

echo get_class($stmt);
// Mappa\Statement

$stmt->execute([1]);

var_export($stmt->hydrate([Book::class, Category::class]));
// array (
//   'books' =>
//   Book::__set_state(array(
//      'id' => '1',
//      'name' => 'B01',
//      'category_id' => '1',
//   )),
//   'categories' =>
//   Category::__set_state(array(
//      'id' => '1',
//      'name' => 'C01',
//   )),
//   '' =>
//   stdClass::__set_state(array(
//      'title' => 'B01 - C01',
//   )),
// )
```