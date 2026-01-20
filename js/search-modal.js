/**
 * Модальное окно поиска с live search
 *
 * Обрабатывает открытие/закрытие модального окна и AJAX поиск в реальном времени.
 */
(function() {
  "use strict";

  const searchModal = document.getElementById("search-modal");
  const searchToggle = document.querySelector(".search-toggle");
  const searchClose = document.querySelector(".search-modal__close");
  const searchInput = document.getElementById("search-modal-input");
  const searchForm = document.querySelector(".search-modal__form");
  const searchResults = document.getElementById("search-results");
  const searchOverlay = document.querySelector(".search-modal__overlay");

  if (!searchModal || !searchToggle || !searchInput) {
    return;
  }

  let searchTimeout;
  const SEARCH_DELAY = 300; // Задержка перед поиском в миллисекундах.

  /**
   * Открывает модальное окно поиска.
   */
  function openSearchModal() {
    searchModal.classList.add("is-active");
    searchModal.setAttribute("aria-hidden", "false");
    searchToggle.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
    searchInput.focus();

    // Закрываем мобильное меню, если открыто.
    const siteNavigation = document.getElementById("site-navigation");
    if (siteNavigation && siteNavigation.classList.contains("toggled")) {
      siteNavigation.classList.remove("toggled");
      const menuButton = siteNavigation.querySelector(".menu-toggle");
      if (menuButton) {
        menuButton.setAttribute("aria-expanded", "false");
      }
    }
  }

  /**
   * Закрывает модальное окно поиска.
   */
  function closeSearchModal() {
    searchModal.classList.remove("is-active");
    searchModal.setAttribute("aria-hidden", "true");
    searchToggle.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
    searchInput.value = "";
    searchResults.innerHTML = "";
    searchToggle.focus();
  }

  /**
   * Выполняет AJAX поиск.
   *
   * @param {string} query Поисковый запрос.
   */
  function performSearch(query) {
    if (!query || query.length < 2) {
      searchResults.innerHTML = "";
      return;
    }

    // Показываем индикатор загрузки.
    searchResults.innerHTML = '<div class="search-results__loading">' +
      '<span class="screen-reader-text">Поиск...</span>' +
      '</div>';

    // Отправляем AJAX запрос.
    const formData = new FormData();
    formData.append("action", "the_capon_live_search");
    formData.append("query", query);
    formData.append("nonce", theCaponSearch.nonce);

    fetch(theCaponSearch.ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin"
    })
      .then(function(response) {
        if (!response.ok) {
          throw new Error("Ошибка сети");
        }
        return response.json();
      })
      .then(function(data) {
        // Проверяем успешность и наличие данных в правильном формате.
        if (data.success && data.data) {
          // data.data может быть строкой или объектом с полем html
          let html = "";
          if (typeof data.data === "string") {
            html = data.data;
          } else if (data.data.html) {
            html = data.data.html;
          } else if (data.data.data) {
            html = data.data.data;
          }
          
          if (html && html.trim().length > 0) {
            displaySearchResults(html);
          } else {
            displayNoResults();
          }
        } else {
          displayNoResults();
        }
      })
      .catch(function(error) {
        console.error("Ошибка поиска:", error);
        displayNoResults();
      });
  }

  /**
   * Отображает результаты поиска.
   *
   * @param {string} html HTML с результатами.
   */
  function displaySearchResults(html) {
    if (!html || html.trim().length === 0) {
      displayNoResults();
      return;
    }
    
    // Проверяем, что HTML содержит реальные результаты, а не только пустые контейнеры
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    const hasResults = tempDiv.querySelector(".search-result__item") || tempDiv.querySelector(".search-results__list");
    
    if (hasResults) {
      searchResults.innerHTML = html;
    } else {
      displayNoResults();
    }
  }

  /**
   * Отображает сообщение об отсутствии результатов.
   */
  function displayNoResults() {
    searchResults.innerHTML = '<div class="search-results__empty">' +
      '<p>Ничего не найдено. Попробуйте изменить запрос.</p>' +
      '</div>';
  }

  /**
   * Обработчик ввода в поле поиска.
   */
  function handleSearchInput() {
    const query = searchInput.value.trim();

    // Очищаем предыдущий таймер.
    clearTimeout(searchTimeout);

    // Устанавливаем новый таймер для поиска.
    searchTimeout = setTimeout(function() {
      performSearch(query);
    }, SEARCH_DELAY);
  }

  // Обработчики событий.
  searchToggle.addEventListener("click", function(e) {
    e.preventDefault();
    openSearchModal();
  });

  if (searchClose) {
    searchClose.addEventListener("click", function(e) {
      e.preventDefault();
      closeSearchModal();
    });
  }

  if (searchOverlay) {
    searchOverlay.addEventListener("click", function() {
      closeSearchModal();
    });
  }

  // Закрытие по клавише Escape.
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && searchModal.classList.contains("is-active")) {
      closeSearchModal();
    }
  });

  // Live search при вводе.
  searchInput.addEventListener("input", handleSearchInput);

  // Обработка отправки формы.
  if (searchForm) {
    searchForm.addEventListener("submit", function(e) {
      const query = searchInput.value.trim();
      if (!query) {
        e.preventDefault();
      }
      // Если есть запрос, форма отправится стандартным способом.
    });
  }

  // Закрытие при клике на результат (для мобильных устройств).
  searchResults.addEventListener("click", function(e) {
    if (e.target.closest(".search-result__item")) {
      // Не закрываем сразу, даём пользователю перейти по ссылке.
      setTimeout(function() {
        closeSearchModal();
      }, 100);
    }
  });
})();
