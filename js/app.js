/******************************************
 * DOM ELEMENTS
 ******************************************/

// NAVIGATION
const navNewBook   = document.getElementById("nav-newbook");
const navLibrary   = document.getElementById("nav-library");

// SECTION VIEWS
const libraryView  = document.getElementById("libraryView");
const newBookView  = document.getElementById("newBookView");

// LIBRARY ELEMENTS
const booksBody    = document.getElementById("booksBody");
const searchInput  = document.getElementById("searchInput");
const filterGroup  = document.getElementById("filterGroup");
const btnFilter    = document.getElementById("btnFilter");
const btnNewBook   = document.getElementById("btnNewBook");
const loading      = document.getElementById('loading');
const loadingText  = document.getElementById('loadingText');


// BANNER (SUCCESS/ERROR)
const banner       = document.getElementById("banner");


// NEW BOOK FORM
const newBookForm    = document.getElementById("newBookForm");
const nbTitle        = document.getElementById("nbTitle");
const nbAuthor       = document.getElementById("nbAuthor");
const nbGenre        = document.getElementById("nbGenre");
const nbCollection   = document.getElementById("nbCollection");
const nbYear         = document.getElementById("nbYear");
const nbDate         = document.getElementById("nbDate");
const nbErrTitle     = document.getElementById("nbErrTitle");
const nbErrAuthor    = document.getElementById("nbErrAuthor");
const btnCancelNewBook = document.getElementById("btnCancelNewBook");

// MODAL FOR DETAILS + EDIT
const modalBackdrop = document.getElementById("modalBackdrop");
const modalClose    = document.getElementById("modalClose");
const modalTitle    = document.getElementById("modalTitle");
const detailsView   = document.getElementById("detailsView");
const bookForm      = document.getElementById("bookForm");

// Inside modal (edit fields)
const titleInput      = document.getElementById("titleInput");
const authorInput     = document.getElementById("authorInput");
const genreInput      = document.getElementById("genreInput");
const collectionInput = document.getElementById("collectionInput");
const yearInput       = document.getElementById("yearInput");
const dateInput       = document.getElementById("dateInput");
const bookIdInput     = document.getElementById("bookId");
const errTitle        = document.getElementById("errTitle");
const errAuthor       = document.getElementById("errAuthor");

// Modal buttons
const btnEdit        = document.getElementById("btnEdit");
const btnSave        = document.getElementById("btnSave");
const btnDelete      = document.getElementById("btnDelete");
const btnCancel      = document.getElementById("btnCancel");

// Read-only modal detail fields
const dAuthor      = document.getElementById("dAuthor");
const dGenre       = document.getElementById("dGenre");
const dCollection  = document.getElementById("dCollection");
const dDate        = document.getElementById("dDate");

/******************************************
 * VARIABLES
 ******************************************/
let currentBooks = [];
let currentFilter = "title";
let currentBook = null;


/******************************************
 * UI + UTILITY HELPERS
 ******************************************/

function showBanner(message, isError = false) {
  banner.textContent = message;
  banner.classList.toggle("error", isError);
  banner.style.display = "block";
  setTimeout(() => (banner.style.display = "none"), 2500);
}

function setLoading(isLoading) {
  loading.style.display = isLoading ? "block" : "none";
}

function openModal() {
  modalBackdrop.style.display = "flex";
}

function closeModal() {
  modalBackdrop.style.display = "none";
  currentBook = null;
  bookForm.reset();
  errTitle.style.display = "none";
  errAuthor.style.display = "none";
}

function showLibraryView() {
  libraryView.classList.remove("hidden");
  newBookView.classList.add("hidden");

  if (navLibrary) navLibrary.classList.add("active");
  if (navNewBook) navNewBook.classList.remove("active");
}

function showNewBookView() {
  libraryView.classList.add("hidden");
  newBookView.classList.remove("hidden");

  if (navLibrary) navLibrary.classList.remove("active");
  if (navNewBook) navNewBook.classList.add("active");

  newBookForm.reset();
  nbErrTitle.style.display = "none";
  nbErrAuthor.style.display = "none";
}


/******************************************
 * FETCH BOOKS LIST
 ******************************************/
function loadBooks(search = "", filterBy = "title") {
  setLoading(true);

  let url = "books_api.php?action=list";
  if (search.trim() !== "") {
    url +=
      "&search=" +
      encodeURIComponent(search.trim()) +
      "&by=" +
      encodeURIComponent(filterBy);
  }

  fetch(url)
    .then((r) => r.json())
    .then((data) => {
      setLoading(false);
      if (!data.success) {
        showBanner(data.message || "Failed to load books", true);
        return;
      }
      currentBooks = data.data;
      renderBooks();
    })
    .catch(() => {
      setLoading(false);
      showBanner("Server error while fetching books", true);
    });
}

function renderBooks() {
  booksBody.innerHTML = "";

  if (!currentBooks || currentBooks.length === 0) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 6;
    td.textContent = "No books found";
    tr.appendChild(td);
    booksBody.appendChild(tr);
    return;
  }

  currentBooks.forEach((book, index) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${index + 1}</td>
      <td>${book.title}</td>
      <td>${book.genre || ""}</td>
      <td>${book.author}</td>
      <td>${book.year || ""}</td>
      <td><span class="icon-button" data-id="${book.id}">&#128196;</span></td>
    `;
    tr.querySelector(".icon-button").addEventListener("click", () =>
      openDetails(book)
    );
    booksBody.appendChild(tr);
  });
}


/******************************************
 * DETAILS + EDIT MODAL
 ******************************************/
function openDetails(book) {
  currentBook = book;

  modalTitle.textContent = book.title;

  detailsView.style.display = "block";
  bookForm.classList.add("hidden");

  if (btnSave) btnSave.classList.add("hidden"); // guest safe
  if (btnEdit) btnEdit.classList.remove("hidden");
  if (btnDelete) btnDelete.classList.remove("hidden");

  dAuthor.textContent = book.author;
  dGenre.textContent = book.genre || "";
  dCollection.textContent = book.collection_name || "";
  dDate.textContent = book.date_of_release || "";

  openModal();
}

function openEditForm() {
  if (!currentBook) return;

  modalTitle.textContent = "Edit Book";

  detailsView.style.display = "none";
  bookForm.classList.remove("hidden");

  if (btnSave) btnSave.classList.remove("hidden"); // user only
  if (btnEdit) btnEdit.classList.add("hidden");

  // Pre-fill fields
  bookIdInput.value = currentBook.id;
  titleInput.value = currentBook.title;
  authorInput.value = currentBook.author;
  genreInput.value = currentBook.genre || "";
  collectionInput.value = currentBook.collection_name || "";
  yearInput.value = currentBook.year || "";
  dateInput.value = currentBook.date_of_release || "";
}

function validateEditForm() {
  let valid = true;
  errTitle.style.display = "none";
  errAuthor.style.display = "none";

  if (titleInput.value.trim() === "") {
    errTitle.style.display = "block";
    valid = false;
  }
  if (authorInput.value.trim() === "") {
    errAuthor.style.display = "block";
    valid = false;
  }
  return valid;
}

function saveBook() {
  if (!validateEditForm()) return;

  const formData = new FormData();
  formData.append("title", titleInput.value.trim());
  formData.append("author", authorInput.value.trim());
  formData.append("genre", genreInput.value.trim());
  formData.append("collection", collectionInput.value.trim());
  formData.append("year", yearInput.value.trim());
  formData.append("date_of_release", dateInput.value);

  const url =
    "books_api.php?action=update&id=" +
    encodeURIComponent(bookIdInput.value);

  fetch(url, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        showBanner(data.message || "Update failed", true);
        return;
      }
      closeModal();
      showBanner("Update Successful!");
      loadBooks(searchInput.value, currentFilter);
    })
    .catch(() => showBanner("Server error while updating", true));
}

function deleteBook() {
  if (!currentBook) return;
  if (!confirm("Are you sure you want to delete this book?")) return;

  const url =
    "books_api.php?action=delete&id=" + encodeURIComponent(currentBook.id);

  fetch(url, { method: "POST" })
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        showBanner(data.message || "Delete failed", true);
        return;
      }
      closeModal();
      showBanner("Book Deleted");
      loadBooks(searchInput.value, currentFilter);
    })
    .catch(() => showBanner("Server error while deleting", true));
}
let loadingStartTime = 0;     // tracks when loader started
const MIN_LOAD_DURATION = 1000; // 1 second minimum

function showLoading(message = 'Loading...') {
  if (loadingText) loadingText.textContent = message;
  loading.style.display = 'flex';
  loadingStartTime = Date.now();  // timestamp start
}

function hideLoading() {
  const elapsed = Date.now() - loadingStartTime;
  const remaining = MIN_LOAD_DURATION - elapsed;

  const finish = () => {
    loading.classList.add('fade-out');
    setTimeout(() => {
      loading.style.display = 'none';
      loading.classList.remove('fade-out'); // reset for next load
    }, 250);
  };

  if (remaining <= 0) {
    finish();
  } else {
    setTimeout(finish, remaining);
  }
}



function loadBooks(search = '', filterBy = 'title') {
  showLoading('Fetching books...');

  let url = 'books_api.php?action=list';
  if (search.trim() !== '') {
    url += '&search=' + encodeURIComponent(search.trim()) +
           '&by=' + encodeURIComponent(filterBy);
  }

  fetch(url)
    .then(async (r) => {
      const text = await r.text(); // optional: helpful for debugging
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        hideLoading();
        showBanner('Server response was not valid JSON', true);
        console.error('Raw response:', text);
        return;
      }

      hideLoading();

      if (!data.success) {
        showBanner(data.message || 'Failed to load books', true);
        return;
      }

      currentBooks = data.data;
      renderBooks();
    })
    .catch((err) => {
      console.error('Fetch error:', err);
      hideLoading();
      showBanner('Server error while fetching books', true);
    });
}
function renderBooks() {
  booksBody.innerHTML = '';

  if (!currentBooks || currentBooks.length === 0) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 6;
    td.textContent = 'No books found';
    tr.appendChild(td);
    booksBody.appendChild(tr);
    return;
  }

  currentBooks.forEach((book, index) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${index + 1}</td>
      <td>${book.title}</td>
      <td>${book.genre || ''}</td>
      <td>${book.author}</td>
      <td>${book.year || ''}</td>
      <td><span class="icon-button" data-id="${book.id}">&#128196;</span></td>
    `;
    tr.querySelector('.icon-button').addEventListener('click', () => openDetails(book));
    booksBody.appendChild(tr);
  });
}


/******************************************
 * NEW BOOK FORM (DOM PAGE)
 ******************************************/
function validateNewBookForm() {
  let valid = true;
  nbErrTitle.style.display = "none";
  nbErrAuthor.style.display = "none";

  if (nbTitle.value.trim() === "") {
    nbErrTitle.style.display = "block";
    valid = false;
  }
  if (nbAuthor.value.trim() === "") {
    nbErrAuthor.style.display = "block";
    valid = false;
  }
  return valid;
}

function createBookFromPage(e) {
  e.preventDefault();
  if (!validateNewBookForm()) return;

  const formData = new FormData();
  formData.append("title", nbTitle.value.trim());
  formData.append("author", nbAuthor.value.trim());
  formData.append("genre", nbGenre.value.trim());
  formData.append("collection", nbCollection.value.trim());
  formData.append("year", nbYear.value.trim());
  formData.append("date_of_release", nbDate.value);

  fetch("books_api.php?action=create", {
    method: "POST",
    body: formData,
  })
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        showBanner(data.message || "Save failed", true);
        return;
      }

      showBanner("Book Created");
      showLibraryView();
      loadBooks(searchInput.value, currentFilter);
    })
    .catch(() => showBanner("Server error while saving", true));
}
function createBookFromPage(e) {
  e.preventDefault();
  if (!validateNewBookForm()) return;

  const formData = new FormData();
  formData.append('title', nbTitle.value.trim());
  formData.append('author', nbAuthor.value.trim());
  formData.append('genre', nbGenre.value.trim());
  formData.append('collection', nbCollection.value.trim());
  formData.append('year', nbYear.value.trim());
  formData.append('date_of_release', nbDate.value);

  showLoading('Saving book...');

  fetch('books_api.php?action=create', {
    method: 'POST',
    body: formData
  })
    .then((r) => r.json())
    .then((data) => {
      hideLoading();

      if (!data.success) {
        showBanner(data.message || 'Save failed', true);
        return;
      }

      showBanner('Book created successfully');
      showLibraryView();
      loadBooks(searchInput.value, currentFilter);
    })
    .catch((err) => {
      hideLoading();
      console.error('Save error:', err);
      showBanner('Server error while saving', true);
    });
}
function deleteBook() {
  if (!currentBook) return;
  if (!confirm('Are you sure you want to delete this book?')) return;

  const url = 'books_api.php?action=delete&id=' + encodeURIComponent(currentBook.id);

  showLoading('Deleting book...');

  fetch(url, { method: 'POST' })
    .then((r) => r.json())
    .then((data) => {
      hideLoading();

      if (!data.success) {
        showBanner(data.message || 'Delete failed', true);
        return;
      }
      closeModal();
      showBanner('Book deleted');
      loadBooks(searchInput.value, currentFilter);
    })
    .catch((err) => {
      hideLoading();
      console.error('Delete error:', err);
      showBanner('Server error while deleting', true);
    });
}


/******************************************
 * SAFE EVENT BINDING — supports guest mode
 ******************************************/

// LIBRARY TAB
if (navLibrary) {
  navLibrary.addEventListener("click", (e) => {
    e.preventDefault();
    showLibraryView();
  });
}

if (btnSave) {
  btnSave.addEventListener('click', saveBook);
}


// NEW BOOK TAB (users only)
if (navNewBook) {
  navNewBook.addEventListener("click", (e) => {
    e.preventDefault();
    showNewBookView();
  });
}

// NEW BOOK BUTTON in toolbar
if (btnNewBook) {
  btnNewBook.addEventListener("click", (e) => {
    e.preventDefault();
    showNewBookView();
  });
}

// FILTER BUTTON
if (btnFilter) {
  btnFilter.addEventListener("click", () => {
    loadBooks(searchInput.value, currentFilter);
  });
}

// SEARCH FILTER GROUP
if (filterGroup) {
  filterGroup.addEventListener("click", (e) => {
    if (e.target.tagName !== "BUTTON") return;

    currentFilter = e.target.dataset.filter;
    [...filterGroup.querySelectorAll("button")].forEach((btn) =>
      btn.classList.remove("active")
    );
    e.target.classList.add("active");
  });
}

// CANCEL NEW BOOK FORM
if (btnCancelNewBook) {
  btnCancelNewBook.addEventListener("click", (e) => {
    e.preventDefault();
    showLibraryView();
  });
}

// SUBMIT NEW BOOK FORM
if (newBookForm) {
  newBookForm.addEventListener("submit", createBookFromPage);
}

// MODAL BUTTONS
if (btnEdit) btnEdit.addEventListener("click", openEditForm);
if (btnSave) btnSave.addEventListener("click", saveBook);
if (btnDelete) btnDelete.addEventListener("click", deleteBook);

// MODAL CLOSING
if (modalClose) modalClose.addEventListener("click", closeModal);
if (btnCancel) btnCancel.addEventListener("click", closeModal);

/******************************************
 * INITIAL LOAD
 ******************************************/
window.addEventListener("load", () => loadBooks());
