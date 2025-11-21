<?php
session_start();
if (!isset($_SESSION['role'])) {
  header("Location: index.php");
  exit;
}

$role = $_SESSION['role']; // 'user' or 'guest'
$username = $_SESSION['username'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="banner" id="banner"></div>

<div class="app-shell">
  <aside>
    <div>
      <div class="brand">BOOK STORE</div>
      <nav>
        <?php if ($role === 'user'): ?>
          <a href="#" id="nav-newbook">NEW BOOK</a>
        <?php endif; ?>
        <a href="#" id="nav-library" class="active">LIBRARY</a>
      </nav>
    </div>
    <div class="user-footer">
      <?= htmlspecialchars($username); ?>
      <?php if ($role === 'guest'): ?>
        (view-only)
      <?php endif; ?>
      · <a href="logout.php" style="cursor:pointer; text-decoration:none;">LOGOUT</a>
    </div>
  </aside>

  <main>
    <!-- LIBRARY VIEW -->
    <section id="libraryView">
      <div class="toolbar">
        <input id="searchInput" class="search-input" type="text" placeholder="Find a book...">
        <div class="chip-group" id="filterGroup">
          <button data-filter="title" class="active">Title</button>
          <button data-filter="author">Author</button>
        </div>
        <button class="btn btn-primary" id="btnFilter">Filter</button>

        <?php if ($role === 'user'): ?>
          <button class="btn btn-secondary" id="btnNewBook">New Book</button>
        <?php endif; ?>
      </div>

      <div id="tableContainer">
        <table>
          <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Genre</th>
            <th>Author</th>
            <th>Year</th>
            <th>Details</th>
          </tr>
          </thead>
          <tbody id="booksBody"></tbody>
        </table>
      </div>
      <div id="loading" class="loading-overlay">
        <div class="loading-content">
          <div class="spinner"></div>
          <div class="loading-text" id="loadingText">Fetching books...</div>
        </div>
      </div>

    </section>

    <!-- NEW BOOK VIEW (SEPARATE PAGE VIA DOM) -->
    <?php if ($role === 'user'): ?>
      <section id="newBookView" class="hidden">
        <h2 style="margin-top:0;">New Book</h2>
        <p style="margin-bottom:16px; color:#666; font-size:14px;">
          Fill in the details below to add a new book to the library.
        </p>

        <form id="newBookForm">
          <div class="form-field">
            <label for="nbTitle">Title *</label>
            <input id="nbTitle" name="title">
            <div class="error-text" id="nbErrTitle">Title is required</div>
          </div>

          <div class="form-field">
            <label for="nbAuthor">Author *</label>
            <input id="nbAuthor" name="author">
            <div class="error-text" id="nbErrAuthor">Author is required</div>
          </div>

          <div class="form-field">
            <label for="nbGenre">Genre</label>
            <input id="nbGenre" name="genre">
          </div>

          <div class="form-field">
            <label for="nbCollection">Collection</label>
            <input id="nbCollection" name="collection">
          </div>

          <div class="form-field">
            <label for="nbYear">Year</label>
            <input id="nbYear" name="year" type="number">
          </div>

          <div class="form-field">
            <label for="nbDate">Date of release</label>
            <input id="nbDate" name="date_of_release" type="date">
          </div>

          <div style="margin-top:16px;">
            <button type="button" class="btn btn-secondary" id="btnCancelNewBook">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Book</button>
          </div>
        </form>
      </section>
    <?php endif; ?>
  </main>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="modalBackdrop">
  <div class="modal" id="modal">
    <span class="close" id="modalClose">&times;</span>
    <h2 id="modalTitle">Book Details</h2>

    <form id="bookForm" class="hidden">
      <input type="hidden" id="bookId">
      <div class="form-field">
        <label for="titleInput">Title *</label>
        <input id="titleInput" name="title">
        <div class="error-text" id="errTitle">Title is required</div>
      </div>
      <div class="form-field">
        <label for="authorInput">Author *</label>
        <input id="authorInput" name="author">
        <div class="error-text" id="errAuthor">Author is required</div>
      </div>
      <div class="form-field">
        <label for="genreInput">Genre</label>
        <input id="genreInput" name="genre">
      </div>
      <div class="form-field">
        <label for="collectionInput">Collection</label>
        <input id="collectionInput" name="collection">
      </div>
      <div class="form-field">
        <label for="yearInput">Year</label>
        <input id="yearInput" name="year" type="number">
      </div>
      <div class="form-field">
        <label for="dateInput">Date of release</label>
        <input id="dateInput" name="date_of_release" type="date">
      </div>
    </form>

    <div id="detailsView">
      <div class="modal-row"><label>Author:</label><span id="dAuthor"></span></div>
      <div class="modal-row"><label>Genre:</label><span id="dGenre"></span></div>
      <div class="modal-row"><label>Collections:</label><span id="dCollection"></span></div>
      <div class="modal-row"><label>Date of Release:</label><span id="dDate"></span></div>
    </div>

    <div class="modal-actions">
      <button class="btn btn-secondary" id="btnCancel">Close</button>
      <?php if ($role === 'user'): ?>
        <button class="btn btn-primary hidden" id="btnSave">Save</button>
        <button class="btn btn-primary" id="btnEdit">Edit</button>
        <button class="btn btn-secondary" id="btnDelete">Delete</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="js/app.js"></script>
</body>
</html>
