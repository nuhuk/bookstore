<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

if (!defined('BOOKS_API_TEST_MODE')) {
    $role = $_SESSION['role'] ?? 'guest'; // 'user' or 'guest'
    $pdo  = getPDO();
    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            listBooks($pdo);
            break;

        case 'create':
            if ($role !== 'user') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized (create)']);
                exit;
            }
            createBook($pdo);
            break;

        case 'update':
            if ($role !== 'user') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized (update)']);
                exit;
            }
            updateBook($pdo);
            break;

        case 'delete':
            if ($role !== 'user') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized (delete)']);
                exit;
            }
            deleteBook($pdo);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function listBooks(PDO $pdo) {
    $sql = "SELECT id, title, author, genre, collection_name, year, date_of_release FROM books";
    $params = [];

    if (!empty($_GET['search']) && !empty($_GET['by'])) {
        $search = '%' . $_GET['search'] . '%';
        if ($_GET['by'] === 'title') {
            $sql .= " WHERE title LIKE :q";
        } elseif ($_GET['by'] === 'author') {
            $sql .= " WHERE author LIKE :q";
        }
        $params[':q'] = $search;
    }

    $sql .= " ORDER BY id ASC";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare() failed in listBooks']);
        return;
    }

    $stmt->execute($params);
    $books = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $books]);
}

function createBook(PDO $pdo) {
    $title      = trim($_POST['title'] ?? '');
    $author     = trim($_POST['author'] ?? '');
    $genre      = trim($_POST['genre'] ?? '');
    $collection = trim($_POST['collection'] ?? '');
    $year       = $_POST['year'] ?? null;
    $date       = $_POST['date_of_release'] ?? null;

    if ($title === '' || $author === '') {
        echo json_encode(['success' => false, 'message' => 'Title and author are required.']);
        return;
    }

    $sql = "INSERT INTO books (title, author, genre, collection_name, year, date_of_release)
            VALUES (:title, :author, :genre, :collection_name, :year, :date_of_release)";
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare() failed in createBook']);
        return;
    }

    try {
        $stmt->execute([
            ':title'           => $title,
            ':author'          => $author,
            ':genre'           => $genre ?: null,
            ':collection_name' => $collection ?: null,
            ':year'            => $year ?: null,
            ':date_of_release' => $date ?: null,
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log("CreateBook failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Insert failed']);
    }
}

function updateBook(PDO $pdo) {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }

    $title      = trim($_POST['title'] ?? '');
    $author     = trim($_POST['author'] ?? '');
    $genre      = trim($_POST['genre'] ?? '');
    $collection = trim($_POST['collection'] ?? '');
    $year       = $_POST['year'] ?? null;
    $date       = $_POST['date_of_release'] ?? null;

    if ($title === '' || $author === '') {
        echo json_encode(['success' => false, 'message' => 'Title and author are required.']);
        return;
    }

    $sql = "UPDATE books
            SET title = :title,
                author = :author,
                genre = :genre,
                collection_name = :collection_name,
                year = :year,
                date_of_release = :date_of_release
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare() failed in updateBook']);
        return;
    }

    try {
        $stmt->execute([
            ':title'           => $title,
            ':author'          => $author,
            ':genre'           => $genre ?: null,
            ':collection_name' => $collection ?: null,
            ':year'            => $year ?: null,
            ':date_of_release' => $date ?: null,
            ':id'              => $id,
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("UpdateBook failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}

function deleteBook(PDO $pdo) {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare() failed in deleteBook']);
        return;
    }

    try {
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("DeleteBook failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
}
