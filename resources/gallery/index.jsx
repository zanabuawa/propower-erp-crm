import React, { useState, useEffect, useMemo } from 'react';
import ReactDOM from 'react-dom/client';
import '../landing/styles.css';
import { PROJECT_CATEGORIES, ALL_GALLERY_IMAGES } from '../landing/gallery-data.jsx';
import { MasonryGrid } from '../landing/masonry.jsx';

const LOGO_RED   = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1 red.png';
const LOGO_H_RED = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1h red.png';
const HERO_BG    = '/assets/img/hero/hero-background-galery.webp';

// ── Nav ───────────────────────────────────────────────────────────────────────
const Nav = () => (
  <div style={{
    display: 'flex', alignItems: 'center', justifyContent: 'space-between',
    padding: '22px 56px', background: '#0a0a0a',
    borderBottom: '1px solid rgba(255,255,255,0.08)', color: '#fff',
  }}>
    <a href="/" className="pp-logo-link">
      <img src={LOGO_H_RED} alt="ProPower" style={{ height: 72, display: 'block' }} />
    </a>
    <div style={{ display: 'flex', gap: 40, fontSize: 14, fontWeight: 500, letterSpacing: '0.02em' }}>
      {[
        { l: 'Inicio',    href: '/#inicio' },
        { l: 'Nosotros',  href: '/#nosotros' },
        { l: 'Servicios', href: '/#servicios' },
        { l: 'Galería',   href: '/galeria' },
        { l: 'Contacto',  href: '/#contacto' },
      ].map(({ l, href }) => (
        <a key={l} href={href}
          className={`pp-nav-link${l === 'Galería' ? ' is-active' : ''}`}
          style={{ color: l === 'Galería' ? 'var(--pp-red)' : '#fff', textDecoration: 'none' }}>
          {l}
        </a>
      ))}
    </div>
    <a href="tel:6141666340" style={{ display: 'flex', alignItems: 'center', gap: 12, fontFamily: 'JetBrains Mono, monospace', fontSize: 12, color: 'rgba(255,255,255,0.7)', letterSpacing: '0.08em', textDecoration: 'none' }}>
      <span style={{ color: 'var(--pp-red)' }}>◆</span>
      614 166 6340
    </a>
  </div>
);

// ── Hero ──────────────────────────────────────────────────────────────────────
const Hero = ({ totalImages, totalCats }) => {
  const stats = [
    { n: totalImages, l: 'Imágenes' },
    { n: totalCats,   l: 'Categorías' },
    { n: '8+',        l: 'Años' },
    { n: '200+',      l: 'Proyectos' },
  ];
  return (
    <div style={{ position: 'relative', height: 460, overflow: 'hidden', background: '#0a0a0a', color: '#fff' }}>
      <img src={HERO_BG} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
      <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(90deg, rgba(10,10,10,0.92) 0%, rgba(10,10,10,0.65) 60%, rgba(10,10,10,0.85) 100%)' }} />
      <img src={LOGO_RED} alt="" style={{ position: 'absolute', right: -80, top: 30, width: 480, opacity: 0.07, filter: 'brightness(0) invert(1)', pointerEvents: 'none' }} />
      <div style={{ position: 'relative', padding: '90px 56px', maxWidth: 1100 }}>
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 10, fontSize: 12, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 22 }}>
          <span style={{ width: 28, height: 1, background: 'var(--pp-red)' }} />
          [ Galería · 8 años de obras ]
        </div>
        <h1 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 900, fontSize: 88, lineHeight: 0.9, letterSpacing: '-0.04em', margin: 0, textTransform: 'uppercase' }}>
          Cada obra,<br/>
          <span style={{ color: 'var(--pp-red)' }}>una garantía.</span>
        </h1>
        <div style={{ display: 'flex', gap: 0, marginTop: 36, border: '1px solid rgba(255,255,255,0.15)', maxWidth: 720 }}>
          {stats.map((s, i) => (
            <div key={s.l} style={{ flex: 1, padding: '14px 20px', borderRight: i < 3 ? '1px solid rgba(255,255,255,0.15)' : 'none' }}>
              <div className="pp-counter" style={{ fontFamily: 'Archivo, sans-serif', fontSize: 28, fontWeight: 800, letterSpacing: '-0.02em' }}>{s.n}</div>
              <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', textTransform: 'uppercase', letterSpacing: '0.12em' }}>{s.l}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

// ── Lightbox ──────────────────────────────────────────────────────────────────
const Lightbox = ({ image, onClose, onPrev, onNext }) => {
  useEffect(() => {
    const onKey = e => {
      if (e.key === 'Escape')     onClose();
      if (e.key === 'ArrowLeft')  onPrev();
      if (e.key === 'ArrowRight') onNext();
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [onClose, onPrev, onNext]);

  if (!image) return null;
  const src = typeof image === 'string' ? image : image.src;
  const cat = typeof image === 'string' ? null : image.cat;

  return (
    <div onClick={onClose} style={{
      position: 'fixed', inset: 0, background: 'rgba(10,10,10,0.94)',
      zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center',
      padding: 56, cursor: 'zoom-out',
    }}>
      <img src={src} alt={cat || ''} style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' }} onClick={e => e.stopPropagation()} />
      {cat && (
        <div style={{ position: 'absolute', top: 28, left: 28, fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.18em', textTransform: 'uppercase', color: '#fff', padding: '6px 14px', background: 'var(--pp-red)' }}>
          {cat}
        </div>
      )}
      <div onClick={e => { e.stopPropagation(); onClose(); }} style={{ position: 'absolute', top: 28, right: 28, width: 44, height: 44, border: '1px solid rgba(255,255,255,0.3)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', fontSize: 18 }}>✕</div>
      <div onClick={e => { e.stopPropagation(); onPrev(); }} style={{ position: 'absolute', left: 28, top: '50%', transform: 'translateY(-50%)', width: 50, height: 50, border: '1px solid rgba(255,255,255,0.3)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', fontSize: 24 }}>‹</div>
      <div onClick={e => { e.stopPropagation(); onNext(); }} style={{ position: 'absolute', right: 28, top: '50%', transform: 'translateY(-50%)', width: 50, height: 50, border: '1px solid rgba(255,255,255,0.3)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', fontSize: 24 }}>›</div>
    </div>
  );
};

// ── App ───────────────────────────────────────────────────────────────────────
const App = () => {
  const [filter, setFilter]       = useState('all');
  const [lbIdx, setLbIdx]         = useState(null);
  const [preloaderOut, setOut]     = useState(false);
  const [preloaderGone, setGone]   = useState(false);

  useEffect(() => {
    const t1 = setTimeout(() => setOut(true),  900);
    const t2 = setTimeout(() => setGone(true), 1500);
    return () => { clearTimeout(t1); clearTimeout(t2); };
  }, []);

  useEffect(() => {
    document.body.style.overflow = lbIdx !== null ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [lbIdx]);

  const filteredImages = useMemo(() =>
    filter === 'all'
      ? ALL_GALLERY_IMAGES
      : ALL_GALLERY_IMAGES.filter(im => im.slug === filter),
  [filter]);

  const activeCat = filter !== 'all' ? PROJECT_CATEGORIES.find(c => c.id === filter) : null;

  const pillStyle = (id) => ({
    padding: '8px 14px',
    border: `1px solid ${filter === id ? 'var(--pp-red)' : '#0a0a0a'}`,
    fontFamily: 'JetBrains Mono, monospace', fontSize: 11,
    textTransform: 'uppercase', letterSpacing: '0.1em',
    background: filter === id ? 'var(--pp-red)' : '#fff',
    color: filter === id ? '#fff' : '#0a0a0a',
    cursor: 'pointer',
    transition: 'background 0.3s, color 0.3s, border-color 0.3s, transform 0.25s',
  });

  return (
    <>
      {!preloaderGone && (
        <div id="pp-preloader" className={preloaderOut ? 'is-hidden' : ''}>
          <img src={LOGO_H_RED} alt="ProPower" />
          <div className="pp-bar" />
        </div>
      )}

      <div style={{ background: '#fff', minHeight: '100vh', color: '#0a0a0a', fontFamily: 'Figtree, -apple-system, system-ui, sans-serif' }}>
        <Nav />
        <Hero totalImages={ALL_GALLERY_IMAGES.length} totalCats={PROJECT_CATEGORIES.length} />

        {/* Filtros sticky */}
        <div style={{ position: 'sticky', top: 0, zIndex: 50, background: '#fff', borderBottom: '1px solid #e7e5e4', padding: '20px 56px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
            <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.18em', color: '#78716c', marginRight: 4 }}>
              Filtrar →
            </div>
            <button style={pillStyle('all')} onClick={() => setFilter('all')}>
              Todos · {ALL_GALLERY_IMAGES.length}
            </button>
            {PROJECT_CATEGORIES.map(c => (
              <button key={c.id} style={pillStyle(c.id)} onClick={() => setFilter(c.id)}>
                {c.short} · {c.images.length}
              </button>
            ))}
          </div>
        </div>

        {/* Grid */}
        <div style={{ padding: '40px 56px 80px' }}>
          {activeCat ? (
            <>
              <div style={{ marginBottom: 32, display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
                <div>
                  <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, color: 'var(--pp-red)', letterSpacing: '0.2em', textTransform: 'uppercase', marginBottom: 10 }}>
                    Sector · {activeCat.sector}
                  </div>
                  <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 44, lineHeight: 1.0, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>{activeCat.title}</h2>
                  <p style={{ fontSize: 16, color: '#57534e', marginTop: 12, maxWidth: 680, lineHeight: 1.55 }}>{activeCat.desc}</p>
                </div>
                <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 12, color: '#78716c', textTransform: 'uppercase', letterSpacing: '0.1em' }}>
                  {activeCat.images.length} imágenes
                </div>
              </div>
              <MasonryGrid
                images={activeCat.images.map(src => ({ src, cat: activeCat.title, sector: activeCat.sector, slug: activeCat.id }))}
                columns={4} gap={12}
                onImageClick={(img, i) => setLbIdx(i)}
              />
            </>
          ) : (
            <MasonryGrid
              images={filteredImages}
              columns={4} gap={12}
              onImageClick={(img, i) => setLbIdx(i)}
            />
          )}
        </div>

        {/* CTA */}
        <section style={{ background: 'var(--pp-red)', color: '#fff', padding: '80px 56px', position: 'relative', overflow: 'hidden' }}>
          <img src={LOGO_RED} alt="" style={{ position: 'absolute', right: -80, top: -40, width: 460, opacity: 0.08, filter: 'brightness(0) invert(1)', pointerEvents: 'none' }} />
          <div style={{ position: 'relative', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 40 }}>
            <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 52, lineHeight: 0.98, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>
              ¿Tu obra es la siguiente?
            </h2>
            <a href="/#contacto" className="pp-cta" style={{ padding: '18px 36px', background: '#fff', color: 'var(--pp-red)', fontSize: 14, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', textDecoration: 'none', border: '1px solid #fff', whiteSpace: 'nowrap' }}>
              Contáctanos →
            </a>
          </div>
        </section>

        {/* Footer */}
        <footer style={{ background: '#000', color: '#fff', padding: '40px 56px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <img src={LOGO_H_RED} alt="ProPower" style={{ height: 32 }} />
          <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.5)' }}>© 2026 ProPower Electroconstrucciones</div>
        </footer>
      </div>

      {lbIdx !== null && (
        <Lightbox
          image={filteredImages[lbIdx]}
          onClose={() => setLbIdx(null)}
          onPrev={() => setLbIdx(i => (i - 1 + filteredImages.length) % filteredImages.length)}
          onNext={() => setLbIdx(i => (i + 1) % filteredImages.length)}
        />
      )}
    </>
  );
};

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
