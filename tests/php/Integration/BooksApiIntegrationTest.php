<?php

use PHPUnit\Framework\TestCase;

class BooksApiIntegrationTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE books (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, author TEXT, genre TEXT, collection_name TEXT, year INTEGER, date_of_release TEXT)');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
    }

    public function testCreateAndListFlow(): void
    {
        $_SESSION['role'] = 'user';
        $_POST = [
            'title' => 'Integration Book',
            'author' => 'QA Bot',
            'genre' => 'Testing',
            'collection' => 'Suite',
            'year' => 2024,
            'date_of_release' => '2024-01-01',
        ];

        ob_start();
        createBook($this->pdo);
        $createResponse = json_decode(ob_get_clean(), true);

        $this->assertTrue($createResponse['success']);
        $this->assertNotEmpty($createResponse['id']);

        ob_start();
        listBooks($this->pdo);
        $listResponse = json_decode(ob_get_clean(), true);

        $this->assertTrue($listResponse['success']);
        $this->assertSame('Integration Book', $listResponse['data'][0]['title']);
    }

    public function testUpdateAndDeleteFlow(): void
    {
        // Seed a record
        $this->pdo->exec("INSERT INTO books (title, author) VALUES ('Old Title', 'Old Author')");
        $id = (int) $this->pdo->lastInsertId();

        // Update
        $_SESSION['role'] = 'user';
        $_GET['id'] = $id;
        $_POST = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'genre' => '',
            'collection' => '',
            'year' => '',
            'date_of_release' => '',
        ];

        ob_start();
        updateBook($this->pdo);
        $updateResponse = json_decode(ob_get_clean(), true);

        $this->assertTrue($updateResponse['success']);

        // Delete
        $_GET['id'] = $id;
        ob_start();
        deleteBook($this->pdo);
        $deleteResponse = json_decode(ob_get_clean(), true);

        $this->assertTrue($deleteResponse['success']);
    }
}
