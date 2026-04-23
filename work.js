/* ============================================================
   WORK PAGE — VIDEO PORTFOLIO LOGIC
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ======================== FILTER SYSTEM ======================== */
  const filterBtns = document.querySelectorAll('.filter-btn');
  const allWorkItems = document.querySelectorAll('.work-item');
  const sections = {
    all:       document.querySelectorAll('.work-item'),
    reel:      document.querySelectorAll('.work-item[data-cat="reel"]'),
    youtube:   document.querySelectorAll('.work-item[data-cat="youtube"]'),
    ads:       document.querySelectorAll('.work-item[data-cat="ads"]'),
    cinematic: document.querySelectorAll('.work-item[data-cat="cinematic"]'),
    dev:       document.querySelectorAll('.work-item[data-cat="dev"]'),
  };

  // Section containers to show/hide
  const sectionMap = {
    all:       ['#featured', '#reels-sec', '#yt-sec', '#ads-sec', '#cinema-sec', '#dev-sec'],
    reel:      ['#reels-sec', '#featured'],
    youtube:   ['#yt-sec'],
    ads:       ['#ads-sec'],
    cinematic: ['#cinema-sec'],
    dev:       ['#dev-sec'],
  };

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.dataset.cat;
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      // Show/hide sections with animation
      const sectionsToShow = sectionMap[cat] || [];
      const allSectionEls = document.querySelectorAll('.featured-section, .reels-section, .ws-section');

      allSectionEls.forEach(sec => {
        const id = '#' + sec.id;
        const shouldShow = cat === 'all' || sectionsToShow.includes(id);
        if (shouldShow) {
          sec.style.display = '';
          sec.style.opacity = '0';
          sec.style.transform = 'translateY(20px)';
          requestAnimationFrame(() => {
            sec.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            sec.style.opacity = '1';
            sec.style.transform = 'translateY(0)';
          });
        } else {
          sec.style.transition = 'opacity 0.3s ease';
          sec.style.opacity = '0';
          setTimeout(() => { sec.style.display = 'none'; }, 300);
        }
      });

      // Scroll to first visible section
      setTimeout(() => {
        const firstVisible = document.querySelector('.featured-section, .reels-section, .ws-section');
        if (firstVisible && firstVisible.style.display !== 'none' && cat !== 'all') {
          const nav = document.getElementById('site-nav');
          const offset = (nav ? nav.offsetHeight : 80) + 70;
          window.scrollTo({ top: firstVisible.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
        }
      }, 100);
    });
  });

  /* ======================== VIDEO LIGHTBOX ======================== */
  const lightbox   = document.getElementById('lightbox');
  const lbBackdrop = document.getElementById('lb-backdrop');
  const lbClose    = document.getElementById('lb-close');
  const lbTitle    = document.getElementById('lb-title');
  const lbDesc     = document.getElementById('lb-desc');
  const lbCat      = document.getElementById('lb-cat');
  const lbPlaceholder = document.getElementById('lb-placeholder');
  const lbIframeWrap  = document.getElementById('lb-iframe-wrap');

  function openLightbox(title, desc, cat, videoUrl) {
    if (!lightbox) return;
    lbTitle.textContent = title || 'Project';
    lbDesc.textContent  = desc  || '';
    lbCat.textContent   = cat   || 'Video';

    // Handle video embed
    lbIframeWrap.innerHTML = '';
    if (videoUrl && videoUrl.trim()) {
      // Convert YouTube watch URL to embed URL
      let embedUrl = videoUrl;
      if (videoUrl.includes('youtube.com/watch?v=')) {
        embedUrl = videoUrl.replace('youtube.com/watch?v=', 'youtube.com/embed/');
      } else if (videoUrl.includes('youtu.be/')) {
        embedUrl = videoUrl.replace('youtu.be/', 'youtube.com/embed/');
      } else if (videoUrl.includes('vimeo.com/') && !videoUrl.includes('player.vimeo.com')) {
        embedUrl = videoUrl.replace('vimeo.com/', 'player.vimeo.com/video/');
      }
      const iframe = document.createElement('iframe');
      iframe.src = embedUrl + (embedUrl.includes('?') ? '&' : '?') + 'autoplay=1&rel=0';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      iframe.style.cssText = 'width:100%;height:100%;border:none;';
      lbIframeWrap.appendChild(iframe);
      lbIframeWrap.classList.add('has-video');
      lbPlaceholder.style.display = 'none';
    } else {
      lbIframeWrap.classList.remove('has-video');
      lbPlaceholder.style.display = '';
    }

    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lightbox.classList.remove('open');
    document.body.style.overflow = '';
    // Stop video
    setTimeout(() => {
      lbIframeWrap.innerHTML = '';
      lbIframeWrap.classList.remove('has-video');
      lbPlaceholder.style.display = '';
    }, 400);
  }

  // Attach click to all video work items
  function attachVideoClicks() {
    // All clickable video items (reels, yt, ads, cinematic) — NOT dev cards (those use live links)
    document.querySelectorAll(
      '.reel-card, .yt-card, .ad-card, .cinema-card, .feat-cta, .featured-card .feat-play-btn'
    ).forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
        // Find parent work-item for data
        const parent = el.closest('.work-item') || el;
        const title = parent.dataset.title  || el.dataset.title  || 'Project';
        const desc  = parent.dataset.desc   || el.dataset.desc   || '';
        const cat   = parent.dataset.cat    || 'Video';
        const url   = parent.dataset.videoUrl || el.dataset.videoUrl || '';
        openLightbox(title, desc, formatCat(cat), url);
      });
    });
  }
  attachVideoClicks();

  if (lbClose)    lbClose.addEventListener('click', closeLightbox);
  if (lbBackdrop) lbBackdrop.addEventListener('click', closeLightbox);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

  // Close lightbox contact btn
  const lbHire = document.getElementById('lb-hire-btn');
  if (lbHire) lbHire.addEventListener('click', closeLightbox);

  function formatCat(cat) {
    const map = { reel: 'Short-Form Reel', youtube: 'YouTube', ads: 'Ad Creative', cinematic: 'Cinematic', dev: 'Development' };
    return map[cat] || cat;
  }

  /* ======================== STICKY FILTER ON SCROLL ======================== */
  const workFilters = document.getElementById('work-filters');
  if (workFilters) {
    window.addEventListener('scroll', () => {
      workFilters.classList.toggle('is-stuck', window.scrollY > 400);
    }, { passive: true });
  }

  /* ======================== REEL CARD HOVER PARALLAX ======================== */
  document.querySelectorAll('.reel-card').forEach(card => {
    const frame = card.querySelector('.phone-frame');
    if (!frame) return;
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      const x = ((e.clientX - r.left) / r.width - 0.5) * 10;
      const y = ((e.clientY - r.top) / r.height - 0.5) * -10;
      frame.style.transform = `perspective(800px) rotateX(${y}deg) rotateY(${x}deg)`;
    });
    card.addEventListener('mouseleave', () => { frame.style.transform = ''; });
  });

  /* ======================== CINEMA CARD PARALLAX ======================== */
  document.querySelectorAll('.cinema-card').forEach(card => {
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      const x = ((e.clientX - r.left) / r.width - 0.5) * 8;
      const y = ((e.clientY - r.top) / r.height - 0.5) * -8;
      card.style.transform = `perspective(1000px) rotateX(${y}deg) rotateY(${x}deg)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
  });

  /* ======================== WORK HERO COUNTER TRIGGER ======================== */
  // Already handled by main script.js, but also trigger for wh-stats
  const whStatsObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.count').forEach(el => {
          const target = parseInt(el.dataset.n);
          const dur = 1800;
          const start = performance.now();
          (function step(now) {
            const t = Math.min((now - start) / dur, 1);
            el.textContent = Math.floor((1 - Math.pow(1 - t, 3)) * target);
            if (t < 1) requestAnimationFrame(step);
            else el.textContent = target;
          })(performance.now());
        });
        whStatsObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.wh-stats').forEach(el => whStatsObs.observe(el));

  /* ======================== VIDEO ITEM ENTRANCE ANIMATIONS ======================== */
  const vObs = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => {
          e.target.style.opacity = '1';
          e.target.style.transform = 'translateY(0)';
        }, (e.target.dataset.i || 0) * 80);
        vObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.reel-card, .yt-card, .ad-card, .dev-card, .cinema-card').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(32px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    el.dataset.i = i % 6;
    vObs.observe(el);
  });

  console.log('%c✦ Work Page Loaded ✦', 'color:#d4a847;font-size:12px;');
});
