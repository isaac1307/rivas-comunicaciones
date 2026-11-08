/* Rivas Comunicaciones — Progressive Enhancement & Motion Layer */
(() => {
  const calm = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* 1. Choreographed once-only section entrances */
  const revealEls = [...document.querySelectorAll('[data-reveal]')];
  if (revealEls.length) {
    if (!('IntersectionObserver' in window) || calm.matches) {
      revealEls.forEach((el) => el.classList.add('is-in'));
    } else {
      const io = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-in');
              io.unobserve(entry.target);
            }
          }
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.08 }
      );
      revealEls.forEach((el) => io.observe(el));
      /* Safety fallback so nothing remains hidden if layout shifts */
      window.setTimeout(() => {
        revealEls.forEach((el) => {
          const r = el.getBoundingClientRect();
          if (r.top < window.innerHeight * 1.05) {
            el.classList.add('is-in');
            io.unobserve(el);
          }
        });
      }, 900);
    }
  }

  /* 2. Scroll-driven drift fallback when animation-timeline: view() is unsupported */
  if (
    !calm.matches &&
    typeof CSS !== 'undefined' &&
    !CSS.supports('(animation-timeline: view()) and (animation-range: entry)') &&
    'IntersectionObserver' in window
  ) {
    const driftEls = [...document.querySelectorAll('.rc-drift')];
    if (driftEls.length) {
      const driftObserver = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            const offset = (0.5 - entry.intersectionRatio) * 14;
            entry.target.style.transform = `scale(1.05) translateY(${offset.toFixed(1)}px)`;
          }
        },
        { threshold: [0, 0.25, 0.5, 0.75, 1] }
      );
      driftEls.forEach((el) => driftObserver.observe(el));
    }
  }

  /* 3. Decorative muted loop videos: play only in viewport, pause on reduced motion */
  const clips = [...document.querySelectorAll('video[data-loop-video]')];
  if (clips.length) {
    const inView = new Set();
    const syncClip = (v) => {
      if (calm.matches) return;
      if (inView.has(v)) {
        v.play().catch(() => {});
      } else {
        v.pause();
      }
    };
    const settleClips = () => {
      for (const v of clips) {
        if (calm.matches) {
          v.removeAttribute('autoplay');
          v.pause();
        } else if (inView.has(v)) {
          v.play().catch(() => {});
        }
      }
    };
    if ('IntersectionObserver' in window) {
      const videoObserver = new IntersectionObserver(
        (entries) => {
          for (const e of entries) {
            if (e.isIntersecting) inView.add(e.target);
            else inView.delete(e.target);
            syncClip(e.target);
          }
        },
        { threshold: 0.2 }
      );
      clips.forEach((v) => videoObserver.observe(v));
    }
    settleClips();
    calm.addEventListener('change', settleClips);
  }

  /* 4. Mobile Navigation Drawer (popover="manual" + scroll-snap + inert) */
  const drawer = document.getElementById('rc-drawer');
  const openBtn = document.getElementById('rc-drawer-open');
  const closeBtn = document.getElementById('rc-drawer-close');
  const mainEl = document.querySelector('main.rc-main');

  if (drawer && openBtn && typeof drawer.showPopover === 'function') {
    const scroller = drawer.querySelector('.rc-drawer__scroller');
    const sheet = drawer.querySelector('.rc-drawer__sheet');

    const openDrawer = async () => {
      drawer.showPopover();
      if (typeof CSS !== 'undefined' && !CSS.supports('scroll-initial-target', 'nearest')) {
        scroller.scrollTo({ left: scroller.offsetWidth, behavior: 'instant' });
        await new Promise((resolve) =>
          requestAnimationFrame(() => requestAnimationFrame(resolve))
        );
      }
      scroller.scrollTo({ left: 0, behavior: 'auto' });
    };

    const closeDrawer = () => {
      if (!drawer.matches(':popover-open')) return;
      scroller.scrollTo({ left: scroller.offsetWidth, behavior: 'auto' });
    };

    if (typeof CSS !== 'undefined' && !CSS.supports('animation-timeline: scroll()')) {
      scroller.addEventListener(
        'scroll',
        () => {
          const ratio = Math.max(0, Math.min(1, 1 - scroller.scrollLeft / Math.max(1, sheet.offsetWidth)));
          drawer.style.setProperty('--drawer-backdrop', String(ratio));
        },
        { passive: true }
      );
    }

    const visibleThreshold = 1 / Math.max(320, window.innerWidth);
    const sheetObserver = new IntersectionObserver(
      (entries) => {
        const entry = entries.at(-1);
        if (!entry) return;
        if (entry.intersectionRatio < visibleThreshold && drawer.matches(':popover-open')) {
          drawer.hidePopover();
          if (mainEl) mainEl.inert = false;
          openBtn.setAttribute('aria-expanded', 'false');
        } else if (entry.intersectionRatio >= 0.95) {
          if (mainEl) mainEl.inert = true;
          openBtn.setAttribute('aria-expanded', 'true');
          sheet.focus();
        }
      },
      { root: drawer, threshold: [visibleThreshold, 0.95, 1] }
    );
    sheetObserver.observe(sheet);

    openBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);

    drawer.addEventListener('click', (event) => {
      if (!sheet.contains(event.target)) closeDrawer();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && drawer.matches(':popover-open')) {
        closeDrawer();
      }
    });
  }

  /* 5. Live Thing: Optical Caudal Calculator ("Many into one" 144-channel diagram + DB plan matcher) */
  const caudalRoot = document.querySelector('[data-caudal-calc]');
  if (caudalRoot) {
    let plans = [];
    try {
      plans = JSON.parse(caudalRoot.getAttribute('data-plans') || '[]');
    } catch {
      plans = [];
    }

    const svgNodes = [...caudalRoot.querySelectorAll('[data-optical-cell]')];
    const mbpsOut = caudalRoot.querySelector('[data-caudal-mbps]');
    const planNameOut = caudalRoot.querySelector('[data-caudal-plan-name]');
    const planPriceOut = caudalRoot.querySelector('[data-caudal-plan-price]');
    const planLinkOut = caudalRoot.querySelector('[data-caudal-plan-link]');
    const tileEls = [...document.querySelectorAll('[data-plan-tile-id]')];

    const state = { tv: 2, calls: 2, cloud: 1, cams: 2 };
    const weights = { tv: 25, calls: 15, cloud: 45, cams: 10 };
    const limits = { tv: 6, calls: 8, cloud: 6, cams: 8 };

    const renderCaudal = () => {
      const rawMbps =
        state.tv * weights.tv +
        state.calls * weights.calls +
        state.cloud * weights.cloud +
        state.cams * weights.cams;
      const neededMbps = Math.max(25, Math.round(rawMbps * 1.15));

      if (mbpsOut) {
        mbpsOut.textContent = `${neededMbps} Mbps`;
      }

      /* Pick matching plan from DB */
      let chosen = plans.find((p) => Number(p.down) >= neededMbps) || plans[plans.length - 1] || null;
      if (chosen) {
        if (planNameOut) planNameOut.textContent = chosen.name;
        if (planPriceOut) planPriceOut.textContent = `$${chosen.price} / mes`;
        if (planLinkOut && chosen.url) planLinkOut.setAttribute('href', chosen.url);
        tileEls.forEach((tile) => {
          tile.classList.toggle(
            'is-recommended',
            String(tile.getAttribute('data-plan-tile-id')) === String(chosen.id)
          );
        });
      }

      /* Light up active proportion of the 144 optical cells */
      if (svgNodes.length) {
        const activeCount = Math.max(
          12,
          Math.min(svgNodes.length, Math.round((neededMbps / 360) * svgNodes.length))
        );
        svgNodes.forEach((rect, idx) => {
          const zone = rect.getAttribute('data-zone');
          const lit = idx < activeCount;
          if (lit) {
            rect.setAttribute('fill', '#c9a45a');
            rect.setAttribute('opacity', zone === 'ordered' ? '1' : '0.88');
          } else {
            rect.setAttribute('fill', zone === 'scatter' ? '#575049' : '#3a342e');
            rect.setAttribute('opacity', zone === 'scatter' ? '0.65' : '0.5');
          }
        });
      }

      Object.keys(state).forEach((key) => {
        const valEl = caudalRoot.querySelector(`[data-load-val="${key}"]`);
        if (valEl) valEl.textContent = String(state[key]);
      });
    };

    caudalRoot.querySelectorAll('[data-load-step]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const key = btn.getAttribute('data-load-key');
        const delta = Number(btn.getAttribute('data-load-step'));
        if (!key || !(key in state)) return;
        state[key] = Math.max(0, Math.min(limits[key] || 8, state[key] + delta));
        renderCaudal();
      });
    });

    renderCaudal();
  }

  /* 6. Folio Direct Navigation (progressive enhancement over GET /?folio=...) */
  document.querySelectorAll('[data-folio-form]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const input = form.querySelector('input[name="folio"]');
      if (!input) return;
      const val = input.value.trim().toUpperCase();
      if (val.startsWith('RES-')) {
        e.preventDefault();
        window.location.href = `/reservaciones/${encodeURIComponent(val)}`;
      } else if (val.startsWith('TKT-')) {
        e.preventDefault();
        window.location.href = `/soporte/ticket/${encodeURIComponent(val)}`;
      }
    });
  });

  /* 7. Contextual form validation on blur, clear on input, guard double-submit */
  document.querySelectorAll('form[data-rc-form]').forEach((form) => {
    const fields = [...form.querySelectorAll('input, select, textarea')];
    fields.forEach((field) => {
      field.addEventListener('input', () => {
        field.classList.remove('is-invalid');
      });
      field.addEventListener('blur', () => {
        if (field.value && !field.checkValidity()) {
          field.classList.add('is-invalid');
        }
      });
    });

    form.addEventListener('submit', () => {
      if (form.checkValidity()) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
        }
      }
    });
  });
})();

