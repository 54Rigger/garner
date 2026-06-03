(function () {
  const header = document.querySelector('.site-header');
  const menuToggle = document.querySelector('.site-header__menu-toggle');
  const scrollBtn = document.getElementById('scroll-to-top');
  const heroCarousel = document.querySelector('[data-carousel="hero"]');
  const floorplansCarousels = Array.from(document.querySelectorAll('[data-carousel="floorplans"]'));
  const favoriteButtons = Array.from(document.querySelectorAll('[data-favorite-toggle]'));
  const headerFavoritesToggle = document.querySelector('[data-header-favorites-toggle]');
  const headerFavoritesPanel = document.querySelector('[data-header-favorites-panel]');
  const headerFavoritesContent = document.querySelector('[data-header-favorites-content]');
  const headerFavoritesCount = document.querySelector('[data-header-favorites-count]');
  const favoritesConfig = window.garnernewthemeFavorites || null;

  function setHeaderFavoritesCount(count) {
    if (!headerFavoritesCount) {
      return;
    }

    const safeCount = Math.max(0, parseInt(count, 10) || 0);

    headerFavoritesCount.textContent = String(safeCount);
    headerFavoritesCount.classList.toggle('is-visible', safeCount > 0);
  }

  function fetchHeaderFavoritesGrid() {
    if (!favoritesConfig || !window.fetch) {
      return Promise.resolve(null);
    }

    return fetch(favoritesConfig.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: new URLSearchParams({
        action: 'garnernewtheme_get_favorite_products_grid',
        nonce: favoritesConfig.nonce
      }).toString()
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!(payload && payload.success && payload.data)) {
          return null;
        }

        if (headerFavoritesContent && typeof payload.data.html === 'string') {
          headerFavoritesContent.innerHTML = payload.data.html;
        }

        setHeaderFavoritesCount(payload.data.count || 0);

        return payload.data;
      })
      .catch(function () {
        return null;
      });
  }

  if (menuToggle && header) {
    menuToggle.addEventListener('click', function () {
      const isOpen = header.classList.toggle('is-open');
      menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  if (scrollBtn) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        scrollBtn.classList.add('is-visible');
      } else {
        scrollBtn.classList.remove('is-visible');
      }
    });

    scrollBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  if (heroCarousel) {
    const slides = Array.from(heroCarousel.querySelectorAll('.hero__bg'));
    const dots = Array.from(heroCarousel.querySelectorAll('[data-carousel-dot]'));
    const prevBtn = heroCarousel.querySelector('[data-carousel-prev]');
    const nextBtn = heroCarousel.querySelector('[data-carousel-next]');

    if (slides.length > 1) {
      const interval = parseInt(heroCarousel.getAttribute('data-interval'), 10) || 5000;
      let activeIndex = 0;
      let timerId;

      function render(index) {
        slides.forEach(function (slide, i) {
          slide.classList.toggle('is-active', i === index);
        });

        dots.forEach(function (dot, i) {
          dot.classList.toggle('is-active', i === index);
          dot.setAttribute('aria-current', i === index ? 'true' : 'false');
        });
      }

      function goTo(index) {
        activeIndex = (index + slides.length) % slides.length;
        render(activeIndex);
      }

      function startTimer() {
        timerId = setInterval(function () {
          goTo(activeIndex + 1);
        }, interval);
      }

      function restartTimer() {
        if (timerId) {
          clearInterval(timerId);
        }
        startTimer();
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          goTo(activeIndex - 1);
          restartTimer();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          goTo(activeIndex + 1);
          restartTimer();
        });
      }

      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          const index = parseInt(dot.getAttribute('data-carousel-dot'), 10);
          if (!Number.isNaN(index)) {
            goTo(index);
            restartTimer();
          }
        });
      });

      render(activeIndex);
      startTimer();
    }
  }

  if (floorplansCarousels.length) {
    floorplansCarousels.forEach(function (carousel) {
      const slides = Array.from(carousel.querySelectorAll('[data-floorplans-slide]'));
      const dots = Array.from(carousel.querySelectorAll('[data-floorplans-dot]'));
      const prevBtn = carousel.querySelector('[data-floorplans-prev]');
      const nextBtn = carousel.querySelector('[data-floorplans-next]');
      const captionText = carousel.querySelector('[data-floorplans-caption-text]');
      const lightbox = carousel.querySelector('[data-floorplans-dialog]');
      const lightboxImage = carousel.querySelector('[data-floorplans-lightbox-image]');
      const lightboxCaption = carousel.querySelector('[data-floorplans-lightbox-caption]');
      const lightboxPrev = carousel.querySelector('[data-floorplans-lightbox-prev]');
      const lightboxNext = carousel.querySelector('[data-floorplans-lightbox-next]');
      const closeButtons = Array.from(carousel.querySelectorAll('[data-floorplans-close]'));

      if (!slides.length) {
        return;
      }

      let activeIndex = 0;
      let lightboxIndex = 0;

      function render(index) {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach(function (slide, i) {
          slide.classList.toggle('is-active', i === activeIndex);
          slide.setAttribute('aria-hidden', i === activeIndex ? 'false' : 'true');
        });

        dots.forEach(function (dot, i) {
          dot.classList.toggle('is-active', i === activeIndex);
          dot.setAttribute('aria-current', i === activeIndex ? 'true' : 'false');
        });

        if (captionText) {
          captionText.textContent = slides[activeIndex].getAttribute('data-floorplans-caption') || '';
        }
      }

      function openLightbox(index) {
        lightboxIndex = (index + slides.length) % slides.length;
        const slideImage = slides[lightboxIndex].querySelector('img');

        if (lightboxImage) {
          lightboxImage.src = slides[lightboxIndex].getAttribute('data-floorplans-full') || '';
          lightboxImage.alt = slideImage ? slideImage.alt : '';
        }

        if (lightboxCaption) {
          lightboxCaption.textContent = slides[lightboxIndex].getAttribute('data-floorplans-caption') || '';
        }

        if (lightbox) {
          lightbox.hidden = false;
          document.body.classList.add('has-floorplans-lightbox-open');
        }
      }

      function closeLightbox() {
        if (lightbox) {
          lightbox.hidden = true;
          document.body.classList.remove('has-floorplans-lightbox-open');
        }
      }

      function moveLightbox(direction) {
        openLightbox(lightboxIndex + direction);
        render(lightboxIndex);
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          render(activeIndex - 1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          render(activeIndex + 1);
        });
      }

      slides.forEach(function (slide, index) {
        slide.addEventListener('click', function () {
          openLightbox(index);
        });
      });

      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          const index = parseInt(dot.getAttribute('data-floorplans-index'), 10);
          if (!Number.isNaN(index)) {
            render(index);
          }
        });
      });

      closeButtons.forEach(function (closeBtn) {
        closeBtn.addEventListener('click', closeLightbox);
      });

      if (lightboxPrev) {
        lightboxPrev.addEventListener('click', function () {
          moveLightbox(-1);
        });
      }

      if (lightboxNext) {
        lightboxNext.addEventListener('click', function () {
          moveLightbox(1);
        });
      }

      if (lightbox) {
        lightbox.addEventListener('click', function (event) {
          if (event.target === lightbox) {
            closeLightbox();
          }
        });
      }

      document.addEventListener('keydown', function (event) {
        if (lightbox && !lightbox.hidden) {
          if (event.key === 'Escape') {
            closeLightbox();
          } else if (event.key === 'ArrowLeft') {
            moveLightbox(-1);
          } else if (event.key === 'ArrowRight') {
            moveLightbox(1);
          }
        }
      });

      render(activeIndex);
      closeLightbox();
    });
  }

  if (headerFavoritesToggle && headerFavoritesPanel) {
    function closeHeaderFavoritesPanel() {
      headerFavoritesPanel.hidden = true;
      headerFavoritesToggle.setAttribute('aria-expanded', 'false');
    }

    function openHeaderFavoritesPanel() {
      headerFavoritesPanel.hidden = false;
      headerFavoritesToggle.setAttribute('aria-expanded', 'true');
      fetchHeaderFavoritesGrid();
    }

    headerFavoritesToggle.addEventListener('click', function () {
      if (headerFavoritesPanel.hidden) {
        openHeaderFavoritesPanel();
      } else {
        closeHeaderFavoritesPanel();
      }
    });

    document.addEventListener('click', function (event) {
      if (headerFavoritesPanel.hidden) {
        return;
      }

      if (!headerFavoritesPanel.contains(event.target) && !headerFavoritesToggle.contains(event.target)) {
        closeHeaderFavoritesPanel();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !headerFavoritesPanel.hidden) {
        closeHeaderFavoritesPanel();
      }
    });
  }

  if (favoriteButtons.length && window.fetch && favoritesConfig) {

    function updateFavoriteButtons(productId, isFavorited) {
      const matchingButtons = document.querySelectorAll('[data-favorite-toggle][data-product-id="' + productId + '"]');

      matchingButtons.forEach(function (button) {
        const addLabel = button.getAttribute('data-add-label') || 'Add to favorites';
        const removeLabel = button.getAttribute('data-remove-label') || 'Remove from favorites';
        const buttonLabel = isFavorited ? removeLabel : addLabel;
        const srLabel = button.querySelector('.screen-reader-text');

        button.classList.toggle('is-active', isFavorited);
        button.setAttribute('aria-pressed', isFavorited ? 'true' : 'false');
        button.setAttribute('aria-label', buttonLabel);

        if (srLabel) {
          srLabel.textContent = buttonLabel;
        }
      });
    }

    favoriteButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const productId = parseInt(button.getAttribute('data-product-id'), 10);

        if (!productId || button.classList.contains('is-loading')) {
          return;
        }

        button.classList.add('is-loading');

        fetch(favoritesConfig.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: new URLSearchParams({
            action: 'garnernewtheme_toggle_favorite_product',
            nonce: favoritesConfig.nonce,
            product_id: String(productId)
          }).toString()
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (payload) {
            if (payload && payload.success && payload.data) {
              updateFavoriteButtons(productId, !!payload.data.is_favorited);
              setHeaderFavoritesCount(payload.data.count || 0);

              if (headerFavoritesPanel && !headerFavoritesPanel.hidden) {
                fetchHeaderFavoritesGrid();
              }
            }
          })
          .catch(function () {
            return null;
          })
          .finally(function () {
            button.classList.remove('is-loading');
          });
      });
    });
  }
})();
