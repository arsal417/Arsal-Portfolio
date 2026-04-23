/* ============================================================
   ARSALAN ABBAS — MAIN SCRIPT (shared across all pages)
   ============================================================ */

/* ======================== PRELOADER ======================== */
;(function () {
  const pre = document.getElementById('preloader');
  if (!pre) { initSite(); return; }

  const bar = document.getElementById('pre-bar');
  const num = document.getElementById('pre-num');
  let p = 0;
  document.body.style.overflow = 'hidden';

  const tick = setInterval(() => {
    p += Math.random() * 9 + 2;
    if (p > 97) p = 97;
    bar.style.width = p + '%';
    num.textContent = Math.floor(p) + '%';
  }, 70);

  window.addEventListener('load', () => {
    clearInterval(tick);
    p = 100;
    bar.style.width = '100%';
    num.textContent = '100%';
    setTimeout(() => {
      pre.classList.add('exit');
      document.body.style.overflow = '';
      setTimeout(() => { pre.style.display = 'none'; initSite(); }, 900);
    }, 400);
  });
})();

/* ======================== SITE INIT ======================== */
function initSite() {

  /* ------- CURSOR ------- */
  const cdot = document.getElementById('cdot');
  const cring = document.getElementById('cring');
  const ctext = document.getElementById('ctext');
  let mx = 0, my = 0, rx = 0, ry = 0;

  if (cdot && cring) {
    document.addEventListener('mousemove', e => {
      mx = e.clientX;
      my = e.clientY;
      cdot.style.left = mx + 'px';
      cdot.style.top  = my + 'px';
      if (ctext) { ctext.style.left = mx + 'px'; ctext.style.top = my + 'px'; }
    });
    (function followRing() {
      rx += (mx - rx) * 0.11;
      ry += (my - ry) * 0.11;
      cring.style.left = rx + 'px';
      cring.style.top  = ry + 'px';
      requestAnimationFrame(followRing);
    })();

    // hover expand
    document.querySelectorAll('a, button, .flip-card, .preview-card, .reel-card, .yt-card, .ad-card, .cinema-card, .dev-card, .featured-card, .testi-card, .filter-btn, .tab-btn').forEach(el => {
      el.addEventListener('mouseenter', () => { cdot.classList.add('big'); cring.classList.add('big'); });
      el.addEventListener('mouseleave', () => { cdot.classList.remove('big'); cring.classList.remove('big'); });
    });

    // data-tip label
    document.querySelectorAll('[data-tip]').forEach(el => {
      el.addEventListener('mouseenter', () => { ctext.textContent = el.dataset.tip; ctext.classList.add('show'); });
      el.addEventListener('mouseleave', () => ctext.classList.remove('show'));
    });
  }

  /* ------- SCROLL PROGRESS ------- */
  const sp = document.getElementById('scroll-progress');
  if (sp) {
    window.addEventListener('scroll', () => {
      const s = window.scrollY;
      const d = document.documentElement.scrollHeight - window.innerHeight;
      sp.style.width = ((s / d) * 100) + '%';
    }, { passive: true });
  }

  /* ------- STICKY NAV ------- */
  const nav = document.getElementById('site-nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('stuck', window.scrollY > 60);
    }, { passive: true });
  }

  /* ------- BURGER MENU ------- */
  const burger = document.getElementById('burger');
  const mobileNav = document.getElementById('mobile-nav');
  if (burger && mobileNav) {
    burger.addEventListener('click', () => {
      burger.classList.toggle('open');
      mobileNav.classList.toggle('open');
    });
    mobileNav.querySelectorAll('.mnl').forEach(l => {
      l.addEventListener('click', () => {
        burger.classList.remove('open');
        mobileNav.classList.remove('open');
      });
    });
  }

  /* ------- SMOOTH SCROLL ------- */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const t = document.querySelector(a.getAttribute('href'));
      if (t) {
        e.preventDefault();
        const offset = nav ? nav.offsetHeight + 12 : 80;
        window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
      }
    });
  });

  /* ------- REVEAL ON SCROLL ------- */
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const d = parseInt(e.target.dataset.d || 0);
        setTimeout(() => e.target.classList.add('in'), d);
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  document.querySelectorAll('.rv').forEach(el => obs.observe(el));

  /* ------- COUNTER ANIMATION ------- */
  const cntObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { animCount(e.target); cntObs.unobserve(e.target); }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.count').forEach(el => cntObs.observe(el));

  function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
  function animCount(el) {
    const target = parseInt(el.dataset.n);
    const dur = 2000;
    const start = performance.now();
    (function step(now) {
      const t = Math.min((now - start) / dur, 1);
      el.textContent = Math.floor(easeOut(t) * target);
      if (t < 1) requestAnimationFrame(step);
      else el.textContent = target;
    })(performance.now());
  }

  /* ------- SKILL BARS ------- */
  const sbObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.skill-fill').forEach(bar => {
          bar.style.width = bar.dataset.w + '%';
        });
        sbObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.3 });
  document.querySelectorAll('.skill-bars').forEach(el => sbObs.observe(el));

  /* ------- MAGNETIC BUTTONS ------- */
  document.querySelectorAll('.magnetic-btn').forEach(btn => {
    btn.addEventListener('mousemove', e => {
      const r = btn.getBoundingClientRect();
      const x = (e.clientX - r.left - r.width / 2) * 0.28;
      const y = (e.clientY - r.top - r.height / 2) * 0.28;
      btn.style.transform = `translate(${x}px,${y}px) scale(1.03)`;
    });
    btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
  });

  /* ------- 3D TILT ON CARDS ------- */
  document.querySelectorAll('.flip-card, .preview-card, .testi-card, .yt-card, .ad-card, .dev-card').forEach(card => {
    if (card.classList.contains('flip-card')) return; // flip cards use CSS hover
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      const x = ((e.clientX - r.left) / r.width  - 0.5) *  6;
      const y = ((e.clientY - r.top)  / r.height - 0.5) * -6;
      card.style.transform = `perspective(900px) rotateX(${y}deg) rotateY(${x}deg) translateY(-6px)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
  });

  /* ------- PARTICLE CANVAS (HERO) ------- */
  const canvas = document.getElementById('particle-canvas');
  if (canvas) {
    const ctx = canvas.getContext('2d');
    let W = 0, H = 0;
    const resize = () => { W = canvas.width = canvas.offsetWidth; H = canvas.height = canvas.offsetHeight; };
    resize();
    window.addEventListener('resize', resize, { passive: true });

    const pts = Array.from({ length: 130 }, () => ({
      x: Math.random() * W, y: Math.random() * H,
      vx: (Math.random() - 0.5) * 0.45, vy: (Math.random() - 0.5) * 0.45,
      r: Math.random() * 1.4 + 0.3,
      a: Math.random() * 0.5 + 0.1,
      gold: Math.random() > 0.65
    }));

    (function frame() {
      ctx.clearRect(0, 0, W, H);
      pts.forEach(p => {
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > W) p.vx *= -1;
        if (p.y < 0 || p.y > H) p.vy *= -1;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = p.gold ? `rgba(212,168,71,${p.a})` : `rgba(255,255,255,${p.a * 0.4})`;
        ctx.fill();
      });
      for (let i = 0; i < pts.length; i++) {
        for (let j = i + 1; j < pts.length; j++) {
          const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y;
          const d = Math.sqrt(dx*dx + dy*dy);
          if (d < 100) {
            ctx.beginPath();
            ctx.moveTo(pts[i].x, pts[i].y);
            ctx.lineTo(pts[j].x, pts[j].y);
            ctx.strokeStyle = `rgba(212,168,71,${(1 - d/100) * 0.07})`;
            ctx.lineWidth = 0.5;
            ctx.stroke();
          }
        }
      }
      requestAnimationFrame(frame);
    })();
  }

  /* ------- HERO SPOTLIGHT ------- */
  const spotHero = document.getElementById('hero');
  const spotlight = document.getElementById('scene-spotlight');
  if (spotHero && spotlight) {
    spotHero.addEventListener('mousemove', e => {
      const r = spotHero.getBoundingClientRect();
      const x = e.clientX - r.left, y = e.clientY - r.top;
      spotlight.style.background = `radial-gradient(700px circle at ${x}px ${y}px, rgba(212,168,71,0.07), transparent 70%)`;
    });
  }

  /* ------- TYPEWRITER (HERO) ------- */
  const typeEl = document.getElementById('hero-type');
  if (typeEl) {
    const text = 'Built for brands that want to stand out — not blend in.';
    let i = 0;
    setTimeout(function type() {
      typeEl.textContent = text.slice(0, i++);
      if (i <= text.length) setTimeout(type, 42 + Math.random() * 24);
    }, 1400);
  }

  /* ------- TEXT SCRAMBLE (HERO H1) ------- */
  const headline = document.getElementById('hero-h1');
  if (headline) {
    const chars = '!<>-_\\/[]{}=+*^?#@ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    headline.querySelectorAll('.h1-line').forEach((line, idx) => {
      const final = line.dataset.final || line.textContent.trim();
      setTimeout(() => {
        let frame = 0;
        const total = 40;
        const t = setInterval(() => {
          let out = '';
          for (let i = 0; i < final.length; i++) {
            if (final[i] === ' ') { out += ' '; continue; }
            out += i < Math.floor((frame / total) * final.length)
              ? final[i]
              : chars[Math.floor(Math.random() * chars.length)];
          }
          line.textContent = out;
          if (++frame > total) { line.textContent = final; clearInterval(t); }
        }, 24);
      }, idx * 180 + 200);
    });
  }

  /* ------- PARALLAX ORBS ------- */
  window.addEventListener('scroll', () => {
    const sy = window.scrollY;
    document.querySelectorAll('.orb-a, .orb-b, .orb-c').forEach((o, i) => {
      o.style.transform = `translateY(${sy * (i + 1) * 0.1}px)`;
    });
  }, { passive: true });

  /* ------- RIPPLE ON BUTTONS ------- */
  document.querySelectorAll('.btn-gold, .btn-ghost').forEach(btn => {
    btn.addEventListener('click', e => {
      const r = btn.getBoundingClientRect();
      const span = document.createElement('span');
      const size = Math.max(r.width, r.height);
      span.className = 'ripple-span';
      span.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - r.left - size/2}px;top:${e.clientY - r.top - size/2}px`;
      btn.appendChild(span);
      setTimeout(() => span.remove(), 700);
    });
  });

  /* ------- CONTACT FORM (AJAX → api/contact.php) ------- */
  const cform = document.getElementById('cform');
  if (cform) {
    cform.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = document.getElementById('cf-submit');
      const txt = document.getElementById('cf-text');
      if (!btn || !txt) return;
      const orig = txt.textContent;

      // Loading state
      txt.textContent = 'Sending…';
      btn.disabled = true;

      try {
        const data = Object.fromEntries(new FormData(cform).entries());
        const res  = await fetch('../api/contact.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        });
        const json = await res.json();

        if (json.success) {
          txt.textContent = '✓ Sent! I\'ll reply within 24 hours.';
          btn.querySelector('.btn-gold-bg').style.background = '#16a34a';
          cform.reset();
        } else {
          txt.textContent = '✗ ' + (json.message || 'Something went wrong.');
          btn.querySelector('.btn-gold-bg').style.background = '#991b1b';
        }
      } catch {
        txt.textContent = '✗ Network error — please try WhatsApp.';
        btn.querySelector('.btn-gold-bg').style.background = '#991b1b';
      }

      setTimeout(() => {
        txt.textContent = orig;
        btn.querySelector('.btn-gold-bg').style.background = '';
        btn.disabled = false;
      }, 5000);
    });
  }

  /* ------- REVIEW FORM (AJAX → api/review.php) ------- */
  const rform = document.getElementById('review-form');
  if (rform) {
    // Star rating interaction
    rform.querySelectorAll('.star-btn').forEach(star => {
      star.addEventListener('click', () => {
        const val = star.dataset.v;
        rform.querySelector('#review-rating').value = val;
        rform.querySelectorAll('.star-btn').forEach(s => {
          s.classList.toggle('active', parseInt(s.dataset.v) <= parseInt(val));
        });
      });
    });

    rform.addEventListener('submit', async e => {
      e.preventDefault();
      const btn  = rform.querySelector('.review-submit-btn');
      const orig = btn.textContent;
      btn.textContent = 'Submitting…';
      btn.disabled = true;

      try {
        const data = Object.fromEntries(new FormData(rform).entries());
        const res  = await fetch('../api/review.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        });
        const json = await res.json();
        if (json.success) {
          btn.textContent = '✓ Thank you! Your review is under review.';
          btn.style.background = '#16a34a';
          rform.reset();
          rform.querySelectorAll('.star-btn').forEach(s => s.classList.remove('active'));
        } else {
          btn.textContent = '✗ ' + (json.message || 'Error submitting review.');
        }
      } catch {
        btn.textContent = '✗ Network error.';
      }
      setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.disabled = false; }, 5000);
    });
  }

  /* ------- ACTIVE NAV LINKS (index) ------- */
  document.querySelectorAll('section[id]').forEach(sec => {
    new IntersectionObserver(([e]) => {
      if (e.isIntersecting) {
        document.querySelectorAll('.nl').forEach(l => {
          l.classList.toggle('active', l.getAttribute('href') === `#${sec.id}`);
        });
      }
    }, { threshold: 0.4 }).observe(sec);
  });

  console.log('%c✦ Arsalan Abbas Portfolio ✦', 'color:#d4a847;font-size:14px;font-weight:bold;');
}
