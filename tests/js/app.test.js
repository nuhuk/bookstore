/** @jest-environment jsdom */
const { TextEncoder, TextDecoder } = require('util');

global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

global.fetch = require('whatwg-fetch');

function setupDom() {
  document.body.innerHTML = `
    <div id="libraryView"></div>
    <div id="newBookView" class="hidden"></div>
    <table><tbody id="booksBody"></tbody></table>
    <input id="searchInput" />
    <div id="filterGroup"></div>
    <button id="btnFilter"></button>
    <button id="btnNewBook"></button>
    <div id="loading"></div>
    <div id="loadingText"></div>
    <div id="banner"></div>
    <form id="newBookForm"></form>
    <input id="nbTitle" />
    <input id="nbAuthor" />
    <input id="nbGenre" />
    <input id="nbCollection" />
    <input id="nbYear" />
    <input id="nbDate" />
    <div id="nbErrTitle"></div>
    <div id="nbErrAuthor"></div>
    <div id="modalBackdrop"></div>
    <button id="modalClose"></button>
    <div id="modalTitle"></div>
    <div id="detailsView"></div>
    <form id="bookForm" class="hidden"></form>
    <input id="titleInput" />
    <input id="authorInput" />
    <input id="genreInput" />
    <input id="collectionInput" />
    <input id="yearInput" />
    <input id="dateInput" />
    <input id="bookId" />
    <div id="errTitle"></div>
    <div id="errAuthor"></div>
    <button id="btnEdit"></button>
    <button id="btnSave"></button>
    <button id="btnDelete"></button>
    <button id="btnCancel"></button>
    <span id="dAuthor"></span>
    <span id="dGenre"></span>
    <span id="dCollection"></span>
    <span id="dDate"></span>
  `;
}

describe('app.js helpers', () => {
  let app;

  beforeEach(() => {
    jest.useFakeTimers();
    setupDom();
    jest.isolateModules(() => {
      app = require('../../js/app');
    });
  });

  afterEach(() => {
    jest.useRealTimers();
    jest.resetModules();
  });

  test('validateNewBookForm enforces title and author', () => {
    const nbErrTitle = document.getElementById('nbErrTitle');
    const nbErrAuthor = document.getElementById('nbErrAuthor');

    const valid = app.validateNewBookForm();

    expect(valid).toBe(false);
    expect(nbErrTitle.style.display).toBe('block');
    expect(nbErrAuthor.style.display).toBe('block');
  });

  test('renderBooks populates rows with titles', () => {
    const sampleBooks = [
      { id: 1, title: 'JS Testing', author: 'QA', genre: 'QA', year: '2024' },
      { id: 2, title: 'DOM Testing', author: 'QA', genre: 'QA', year: '2023' },
    ];
    app.setCurrentBooks(sampleBooks);

    app.renderBooks();

    const rows = document.querySelectorAll('#booksBody tr');
    expect(rows.length).toBe(2);
    expect(rows[0].textContent).toContain('JS Testing');
    expect(rows[1].textContent).toContain('DOM Testing');
  });

  test('showLoading/hideLoading toggle loader visibility with delay', () => {
    const loading = document.getElementById('loading');
    app.showLoading('Testing load');
    expect(loading.style.display).toBe('flex');

    app.hideLoading();
    jest.runAllTimers();

    expect(loading.style.display).toBe('none');
  });
});
