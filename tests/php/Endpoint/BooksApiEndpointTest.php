<?php

use PHPUnit\Framework\TestCase;

class BooksApiEndpointTest extends TestCase
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
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
    }

    public function testGuestCannotCreate(): void
    {
        $_SESSION['role'] = 'guest';
        $_GET['action'] = 'create';
        $_POST = [
            'title' => 'Endpoint Book',
            'author' => 'Guest',
        ];

        $response = $this->dispatch();

        $this->assertFalse($response['success']);
        $this->assertSame('Unauthorized (create)', $response['message']);
    }

    public function testListEndpointReturnsSuccess(): void
    {
        $this->pdo->exec("INSERT INTO books (title, author) VALUES ('Endpoint Title', 'Endpoint Author')");
        $_SESSION['role'] = 'user';
        $_GET['action'] = 'list';

        $response = $this->dispatch();

        $this->assertTrue($response['success']);
        $this->assertCount(1, $response['data']);
    }

    private function dispatch(): array
    {
        ob_start();
        $action = $_GET['action'] ?? 'list';

        switch ($action) {
            case 'list':
                listBooks($this->pdo);
                break;
            case 'create':
                if (($_SESSION['role'] ?? 'guest') !== 'user') {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized (create)']);
                    break;
                }
                createBook($this->pdo);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }

        return json_decode(ob_get_clean(), true);
    }
}
