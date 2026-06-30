gsap.registerPlugin(ScrollTrigger);

/* ─── Shared dashboard page animations ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {

  /* page-title ──────────────────────────────────────────────────────────── */
  const pageTitle = document.querySelector('.page-title');
  if (pageTitle) {
    gsap.from(pageTitle, {
      y: 28,
      opacity: 0,
      duration: 0.6,
      ease: 'power3.out',
    });
  }

  /* breadcrumb ──────────────────────────────────────────────────────────── */
  const breadcrumb = document.querySelector('.breadcrumb, [aria-label="Breadcrumb"], [aria-label="breadcrumb"]');
  if (breadcrumb) {
    gsap.from(breadcrumb, {
      y: -12,
      opacity: 0,
      duration: 0.45,
      ease: 'power2.out',
    });
  }

  /* page-toolbar ────────────────────────────────────────────────────────── */
  const toolbar = document.querySelector('.page-toolbar');
  if (toolbar) {
    gsap.from(toolbar, {
      y: 20,
      opacity: 0,
      duration: 0.5,
      delay: 0.15,
      ease: 'power3.out',
    });
  }

  /* stat cards (dashboard index) ────────────────────────────────────────── */
  const statCards = gsap.utils.toArray('[data-gsap="stat-card"]');
  if (statCards.length) {
    gsap.from(statCards, {
      y: 32,
      opacity: 0,
      duration: 0.55,
      stagger: 0.08,
      delay: 0.2,
      ease: 'power3.out',
    });
  }

  /* info / summary panels ───────────────────────────────────────────────── */
  const panels = gsap.utils.toArray('[data-gsap="panel"]');
  if (panels.length) {
    gsap.from(panels, {
      y: 28,
      opacity: 0,
      duration: 0.6,
      stagger: 0.1,
      delay: 0.35,
      ease: 'power3.out',
    });
  }

  /* table panel ─────────────────────────────────────────────────────────── */
  const tablePanel = document.querySelector('.table-panel');
  if (tablePanel) {
    gsap.from(tablePanel, {
      y: 24,
      opacity: 0,
      duration: 0.6,
      delay: 0.3,
      ease: 'power3.out',
    });
  }

  /* form sections — explicit data-gsap attr OR auto-detect dashboard forms ── */
  const formCards = [
    ...gsap.utils.toArray('[data-gsap="form-card"]'),
    ...gsap.utils.toArray('main form.tw\\:grid, main form.tw\\:flex, main > div > form'),
  ].filter((el, i, arr) => arr.indexOf(el) === i);
  if (formCards.length) {
    gsap.from(formCards, {
      y: 28,
      opacity: 0,
      duration: 0.55,
      stagger: 0.1,
      delay: 0.3,
      ease: 'power3.out',
    });
  }

  /* sidebar nav links (stagger entrance on desktop) ─────────────────────── */
  const sidebarLinks = gsap.utils.toArray('.dashboard-sidebar__link');
  if (sidebarLinks.length) {
    gsap.from(sidebarLinks, {
      x: -20,
      opacity: 0,
      duration: 0.4,
      stagger: 0.06,
      delay: 0.1,
      ease: 'power2.out',
    });
  }

  /* emergency alert banner ──────────────────────────────────────────────── */
  const emergencyBanner = document.querySelector('[data-gsap="emergency"]');
  if (emergencyBanner) {
    gsap.from(emergencyBanner, {
      scale: 0.97,
      opacity: 0,
      duration: 0.5,
      ease: 'back.out(1.4)',
    });
  }

  /* ScrollTrigger: rows inside panels ───────────────────────────────────── */
  gsap.utils.toArray('[data-gsap="panel"] > div > div, [data-gsap="panel"] .tw\\:grid > div').forEach((el, i) => {
    gsap.from(el, {
      scrollTrigger: {
        trigger: el,
        start: 'top 92%',
        toggleActions: 'play none none none',
      },
      y: 16,
      opacity: 0,
      duration: 0.45,
      delay: i * 0.04,
      ease: 'power2.out',
    });
  });

  /* ScrollTrigger: task/list items ──────────────────────────────────────── */
  gsap.utils.toArray('[data-gsap="list-item"]').forEach((el) => {
    gsap.from(el, {
      scrollTrigger: {
        trigger: el,
        start: 'top 94%',
        toggleActions: 'play none none none',
      },
      x: -12,
      opacity: 0,
      duration: 0.4,
      ease: 'power2.out',
    });
  });

});
