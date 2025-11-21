<?php

use PHPUnit\Framework\TestCase;

class BooksApiValidationTest extends TestCase
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

    public function testCreateBookRequiresTitleAndAuthor(): void
    {
        $_POST = ['title' => '', 'author' => ''];

        ob_start();
        createBook($this->pdo);
        $response = json_decode(ob_get_clean(), true);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Title and author are required', $response['message']);
    }

    public function testUpdateBookRejectsInvalidId(): void
    {
        $_GET['id'] = 0;

        ob_start();
        updateBook($this->pdo);
        $response = json_decode(ob_get_clean(), true);

        $this->assertFalse($response['success']);
        $this->assertSame('Invalid ID', $response['message']);
    }

    public function testListBooksReturnsArray(): void
    {
        $this->pdo->exec("INSERT INTO books (title, author) VALUES ('Book A', 'Author A'), ('Book B', 'Author B')");

        ob_start();
        listBooks($this->pdo);
        $response = json_decode(ob_get_clean(), true);

        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['data']);
    }
}
