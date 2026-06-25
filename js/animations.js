document.addEventListener('DOMContentLoaded', () => {
  if (typeof gsap === 'undefined') return;

  if (typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
  }

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) return;

  /* ─── Background floating orbs ─── */
  document.querySelectorAll('[class*="animate-blob"]').forEach((orb, i) => {
    gsap.to(orb, {
      y: -28 - i * 8,
      x: 12 - i * 6,
      duration: 6 + i * 2,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
      delay: i * 1.8,
    });
  });

  /* Animate the abstract gradient blobs in the dashboard background */
  const dashboardOrbs = document.querySelectorAll('.dashboard-body > div[class*="absolute"]');
  dashboardOrbs.forEach((orb, i) => {
    gsap.to(orb, {
      scale: 1.15,
      x: i === 0 ? 25 : -20,
      y: i === 0 ? -20 : 18,
      duration: 8 + i * 3,
      ease: 'sine.inOut',
      yoyo: true,
      repeat: -1,
      delay: i * 2,
    });
  });

  /* ─── Page entrance animations ─── */

  // Topbar (mobile dashboard)
  const topbar = document.querySelector('.dashboard-topbar');
  if (topbar) {
    gsap.from(topbar, { y: -20, opacity: 0, duration: 0.5, ease: 'power2.out' });
  }

  // Public header
  const publicHeader = document.querySelector('.public-header');
  if (publicHeader) {
    gsap.from(publicHeader, { y: -24, opacity: 0, duration: 0.6, ease: 'power2.out' });
  }

  // Page title block
  const pageTitle = document.querySelector('.page-title');
  if (pageTitle) {
    gsap.from(pageTitle, { y: 28, opacity: 0, duration: 0.65, ease: 'power3.out' });
  }

  // Breadcrumb / toolbar
  const toolbar = document.querySelector('.page-toolbar');
  const breadcrumb = document.querySelector('.page-breadcrumb');
  [toolbar, breadcrumb].forEach(el => {
    if (!el) return;
    gsap.from(el, { y: 20, opacity: 0, duration: 0.5, ease: 'power2.out', delay: 0.1 });
  });

  /* ─── Stat cards: stagger entrance + counter ─── */
  const statCards = document.querySelectorAll('.dashboard-stat-card');
  if (statCards.length) {
    gsap.from(statCards, {
      y: 44,
      opacity: 0,
      duration: 0.65,
      ease: 'power3.out',
      stagger: 0.1,
      delay: 0.15,
      onComplete() {
        // Count up numbers after cards are visible
        statCards.forEach(card => {
          const valueEl = card.querySelector('.dashboard-stat-card__value');
          if (!valueEl) return;
          const target = parseInt(valueEl.textContent.trim(), 10);
          if (isNaN(target) || target === 0) return;
          const proxy = { val: 0 };
          gsap.to(proxy, {
            val: target,
            duration: 1.1,
            ease: 'power2.out',
            onUpdate() {
              valueEl.textContent = Math.round(proxy.val);
            },
          });
        });
      },
    });
  }

  /* ─── Side panels: scroll-triggered entrance ─── */
  document.querySelectorAll('.dashboard-side-panel').forEach((panel, i) => {
    if (typeof ScrollTrigger === 'undefined') {
      gsap.from(panel, { y: 40, opacity: 0, duration: 0.65, ease: 'power3.out', delay: 0.2 + (i % 2) * 0.12 });
      return;
    }
    gsap.from(panel, {
      y: 48,
      opacity: 0,
      duration: 0.7,
      ease: 'power3.out',
      delay: (i % 2) * 0.13,
      scrollTrigger: { trigger: panel, start: 'top 88%', once: true },
    });
  });

  /* ─── Form shell ─── */
  const formShell = document.querySelector('.form-shell');
  if (formShell) {
    gsap.from(formShell, { y: 40, opacity: 0, duration: 0.7, ease: 'power3.out', delay: 0.2 });
  }

  /* ─── Table panel ─── */
  const tablePanel = document.querySelector('.table-panel');
  if (tablePanel) {
    if (typeof ScrollTrigger !== 'undefined') {
      gsap.from(tablePanel, {
        y: 40, opacity: 0, duration: 0.7, ease: 'power3.out',
        scrollTrigger: { trigger: tablePanel, start: 'top 88%', once: true },
      });
    } else {
      gsap.from(tablePanel, { y: 40, opacity: 0, duration: 0.7, ease: 'power3.out', delay: 0.3 });
    }
  }

  /* ─── Public landing: hero content ─── */
  const heroContent = document.querySelector('.public-hero__content');
  if (heroContent) {
    const kids = Array.from(heroContent.querySelectorAll('h1, p, .public-hero__actions'));
    if (kids.length) {
      gsap.from(kids, { y: 32, opacity: 0, duration: 0.8, ease: 'power3.out', stagger: 0.14 });
    } else {
      gsap.from(heroContent, { y: 32, opacity: 0, duration: 0.8, ease: 'power3.out' });
    }
  }

  /* ─── Public feature cards ─── */
  const featureGrid = document.querySelector('.public-feature-grid');
  if (featureGrid && typeof ScrollTrigger !== 'undefined') {
    gsap.from(featureGrid.querySelectorAll('.public-feature-card'), {
      y: 48,
      opacity: 0,
      duration: 0.7,
      ease: 'power3.out',
      stagger: 0.14,
      scrollTrigger: { trigger: featureGrid, start: 'top 82%', once: true },
    });
  }

  /* ─── Auth / login card ─── */
  const authCard = document.querySelector('.public-auth-card, .auth-card');
  if (authCard) {
    gsap.from(authCard, { y: 36, opacity: 0, duration: 0.8, ease: 'power3.out', delay: 0.1 });
  }

  const authPreview = document.querySelector('.auth-preview');
  if (authPreview) {
    gsap.from(authPreview, { y: 36, opacity: 0, duration: 0.8, ease: 'power3.out', delay: 0.05 });
  }

  /* ─── Gallery cards ─── */
  if (typeof ScrollTrigger !== 'undefined') {
    document.querySelectorAll('.gallery-card, .public-gallery-card').forEach((card, i) => {
      gsap.from(card, {
        y: 44,
        opacity: 0,
        duration: 0.65,
        ease: 'power3.out',
        scrollTrigger: { trigger: card, start: 'top 88%', once: true },
        delay: (i % 3) * 0.1,
      });
    });
  }

  /* ─── Sidebar links ─── */
  const sidebarPanel = document.querySelector('.dashboard-sidebar__panel');
  if (sidebarPanel) {
    const brand = sidebarPanel.querySelector('.dashboard-sidebar__brand');
    const links = sidebarPanel.querySelectorAll('.dashboard-sidebar__link');
    const footer = sidebarPanel.querySelector('.dashboard-sidebar__footer');

    const tl = gsap.timeline({ delay: 0.05 });
    if (brand) tl.from(brand, { x: -20, opacity: 0, duration: 0.45, ease: 'power2.out' });
    if (links.length) tl.from(links, { x: -18, opacity: 0, duration: 0.4, ease: 'power2.out', stagger: 0.055 }, '-=0.1');
    if (footer) tl.from(footer, { y: 14, opacity: 0, duration: 0.4, ease: 'power2.out' }, '-=0.15');
  }
});
