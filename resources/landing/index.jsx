import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './styles.css';
import {
  Nav, HeroDesktop, HeroMobile, OfertaDesktop,
  NosotrosDesktop, NosotrosMobile, ServiciosDesktop, ServiciosMobile,
  GaleriaDesktop, GaleriaMobile, ContactoDesktop, ContactoMobile,
  Footer, FooterMobile, WhatsappFloat,
} from './sections.jsx';

const LOGO_H_RED = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1h red.png';

const App = () => {
  const [isMobile, setIsMobile] = useState(window.innerWidth < 768);
  const [preloaderOut, setPreloaderOut] = useState(false);
  const [preloaderGone, setPreloaderGone] = useState(false);

  useEffect(() => {
    const handler = () => setIsMobile(window.innerWidth < 768);
    window.addEventListener('resize', handler);
    return () => window.removeEventListener('resize', handler);
  }, []);

  // Preloader: fade out after 1.1s, remove from DOM after transition
  useEffect(() => {
    const t1 = setTimeout(() => setPreloaderOut(true), 1100);
    const t2 = setTimeout(() => setPreloaderGone(true), 1750);
    return () => { clearTimeout(t1); clearTimeout(t2); };
  }, []);

  // Counter animation: parse numeric prefix, animate to target, keep suffix
  useEffect(() => {
    if (!preloaderGone) return;
    const els = document.querySelectorAll('.pp-counter[data-counter]');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        const raw = e.target.dataset.counter ?? '';
        const num = parseFloat(raw);
        if (isNaN(num)) return;
        const suffix = raw.replace(/[\d.]/g, '');
        const duration = 1400;
        const start = performance.now();
        const step = (now) => {
          const p = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          const display = Number.isInteger(num)
            ? Math.round(eased * num)
            : (eased * num).toFixed(1);
          e.target.textContent = display + suffix;
          if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
      });
    }, { threshold: 0.3 });
    els.forEach(el => io.observe(el));
    return () => io.disconnect();
  }, [preloaderGone, isMobile]);

  // Scroll reveal: observe all .reveal* and .stagger elements
  useEffect(() => {
    const sel = '.reveal, .reveal-left, .reveal-right, .reveal-zoom, .stagger';
    const els = document.querySelectorAll(sel);
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -48px 0px' });
    els.forEach(el => io.observe(el));
    return () => io.disconnect();
  }, [isMobile, preloaderGone]);

  return (
    <>
      {/* Preloader */}
      {!preloaderGone && (
        <div id="pp-preloader" className={preloaderOut ? 'is-hidden' : ''}>
          <img src={LOGO_H_RED} alt="ProPower" />
          <div className="pp-bar" />
        </div>
      )}

      <div style={{ position: 'relative' }}>
        {isMobile ? (
          <>
            <HeroMobile />
            <NosotrosMobile />
            <ServiciosMobile />
            <GaleriaMobile />
            <ContactoMobile />
            <FooterMobile />
          </>
        ) : (
          <>
            <HeroDesktop />
            <OfertaDesktop />
            <NosotrosDesktop />
            <ServiciosDesktop />
            <GaleriaDesktop />
            <ContactoDesktop />
            <Footer />
          </>
        )}
        <WhatsappFloat />
      </div>
    </>
  );
};

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
