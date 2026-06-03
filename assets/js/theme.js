(function () {
  const header = document.querySelector('.site-header');
  const menuToggle = document.querySelector('.site-header__menu-toggle');
  const scrollBtn = document.getElementById('scroll-to-top');
  const heroCarousel = document.querySelector('[data-carousel="hero"]');
  const floorplansCarousels = Array.from(document.querySelectorAll('[data-carousel="floorplans"]'));

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
})();
