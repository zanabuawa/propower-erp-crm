import React from 'react';

const LOGO_RED = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1 red.png';
const LOGO_H_RED = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1h red.png';

const HERO_IMGS = [
  '/assets/img/hero/hero-background.webp',
  '/assets/img/Carrousel/pexels-1920-1.jpg',
  '/assets/img/Carrousel/pexels-1920-2.jpg',
  '/assets/img/Carrousel/pexels-1920-4.jpg',
  '/assets/img/Carrousel/pexels-cottonbro-5089126.jpg',
  '/assets/img/Carrousel/pexels-mateusz-dach-99805-5956083.jpg',
];
const NOSOTROS_IMGS = {
  industria: '/assets/img/Nosotros/Industria.webp',
  comercial: '/assets/img/Nosotros/Comercial.webp',
  integracion: '/assets/img/Nosotros/Integracion-de-proyectos.webp',
  mantenimiento: '/assets/img/Nosotros/Mantenimiento.webp',
  mineria: '/assets/img/Servicios/s.webp',
  mision: '/assets/img/Nosotros/Mision.webp',
  vision: '/assets/img/Nosotros/Vision.webp',
};
const INDUSTRIA_IMG = NOSOTROS_IMGS.industria;
const MINERIA_IMG = NOSOTROS_IMGS.mineria;
const INGENIERIA_IMG = NOSOTROS_IMGS.integracion;

const GALLERY_IMGS = [...HERO_IMGS, INDUSTRIA_IMG, MINERIA_IMG, INGENIERIA_IMG];

const BRAND_LOGOS = [
  '3M', 'ABB', 'Agros', 'Anclo', 'Burndy', 'Cooper Crouse-Hinds', 'Cooper Lighting',
  'Coundmex', 'Eaton', 'Erico', 'General Cable', 'General Electric', 'Himmel', 'Hubbel',
  'IUSA', 'Igesa', 'Indiana Wire & Cable', 'KleinTools', 'Leviton', 'Littelfuse',
  'MotorsUs', 'Omron', 'Osram', 'Philiphs', 'Prolec', 'Rawlet', 'Siemens', 'Southwire',
  'SquareD', 'Telemecanique Sensors', 'Viakon', 'Weg', 'WestingHouse',
].map(n => `/assets/img/Marcas/${n}.webp`);

const CLIENT_LOGOS = [
  'ANGICO EAGLE','Abitat','Alcodeza','Andrea','CFE','COMIPA','Cerrey','Delan','Fresnillo','GCC',
  'Gobierno del estado','Grupo Hermes','Grupo Mexico','INTERMEX','JCAS','John Deere','Latinoamericana',
  'Minera Meridian','Minera Plata Real','NCH','Nocheluna','Ozone Ecological',
  'Productode del norte la santa cruz','SAT','SEECH','Sedena','Senerg','Slurp','Thermodisc',
  'Tristone','Yamana Gold','jabil','servinliz',
].map(n => `/assets/img/Servicios/clientes/${n}.webp`);

// ==================== Nav ====================
const Nav = ({ mobile = false, active = 'Inicio' }) => {
  const [open, setOpen] = React.useState(false);
  const links = [
    { l: 'Inicio', href: '#inicio' },
    { l: 'Nosotros', href: '#nosotros' },
    { l: 'Servicios', href: '#servicios' },
    { l: 'Galería', href: '/galeria' },
    { l: 'Contacto', href: '#contacto' },
  ];
  if (mobile) {
    return (
      <>
        {/* Top bar */}
        <div style={{ background: '#0a0a0a', position: 'relative', zIndex: 200 }}>
          <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            padding: '14px 18px', borderBottom: '1px solid rgba(255,255,255,0.08)',
          }}>
            <a href="#inicio" className="pp-logo-link">
              <img src={LOGO_H_RED} alt="ProPower" style={{ height: 'auto', maxHeight: 64, width: 'auto', maxWidth: '65vw', display: 'block' }} />
            </a>
            <button
              onClick={() => setOpen(o => !o)}
              aria-label="Menú"
              style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 6, display: 'flex', flexDirection: 'column', gap: 5, zIndex: 310 }}
            >
              <div style={{ width: 26, height: 2, background: '#fff', transition: 'transform 0.3s', transform: open ? 'rotate(45deg) translate(5px, 5px)' : 'none' }} />
              <div style={{ width: 26, height: 2, background: '#fff', transition: 'opacity 0.3s', opacity: open ? 0 : 1 }} />
              <div style={{ width: 26, height: 2, background: '#fff', transition: 'transform 0.3s', transform: open ? 'rotate(-45deg) translate(5px, -5px)' : 'none' }} />
            </button>
          </div>
        </div>

        {/* Overlay backdrop with blur */}
        <div
          onClick={() => setOpen(false)}
          style={{
            position: 'fixed', inset: 0, zIndex: 290,
            background: 'rgba(0,0,0,0.45)',
            backdropFilter: open ? 'blur(6px)' : 'blur(0px)',
            WebkitBackdropFilter: open ? 'blur(6px)' : 'blur(0px)',
            opacity: open ? 1 : 0,
            pointerEvents: open ? 'auto' : 'none',
            transition: 'opacity 0.35s, backdrop-filter 0.35s, -webkit-backdrop-filter 0.35s',
          }}
        />

        {/* Side drawer */}
        <div style={{
          position: 'fixed', top: 0, right: 0, bottom: 0, zIndex: 300,
          width: 'min(80vw, 280px)',
          background: '#0a0a0a',
          borderLeft: '1px solid rgba(255,255,255,0.07)',
          borderTop: '3px solid var(--pp-red)',
          boxShadow: '-8px 0 40px rgba(0,0,0,0.8)',
          transform: open ? 'translateX(0)' : 'translateX(100%)',
          transition: 'transform 0.35s cubic-bezier(0.22,1,0.36,1)',
          display: 'flex', flexDirection: 'column',
          overflowY: 'auto',
        }}>
          {/* Drawer header */}
          <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            padding: '20px 20px 18px',
            borderBottom: '1px solid rgba(255,255,255,0.07)',
          }}>
            <img src={LOGO_H_RED} alt="ProPower" style={{ height: 'auto', maxHeight: 56, width: 'auto', maxWidth: '55vw', display: 'block' }} />
            <button
              onClick={() => setOpen(false)}
              aria-label="Cerrar menú"
              style={{
                background: 'none', border: '1px solid rgba(255,255,255,0.15)',
                cursor: 'pointer', color: '#fff', width: 32, height: 32,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: 18, lineHeight: 1, borderRadius: 2,
              }}
            >
              ✕
            </button>
          </div>

          {/* Nav links */}
          <nav style={{ flex: 1, paddingTop: 8 }}>
            {links.map(({ l, href }) => {
              const isActive = l === active;
              return (
                <a
                  key={l} href={href}
                  onClick={() => setOpen(false)}
                  style={{
                    display: 'flex', alignItems: 'center',
                    padding: '0 24px',
                    height: 56,
                    color: '#fff',
                    fontFamily: 'Figtree, sans-serif', fontSize: 14, fontWeight: 700,
                    textTransform: 'uppercase', letterSpacing: '0.12em',
                    textDecoration: 'none',
                    borderLeft: isActive ? '3px solid var(--pp-red)' : '3px solid transparent',
                    background: isActive ? 'rgba(200,30,30,0.07)' : 'transparent',
                    transition: 'color 0.2s, background 0.2s',
                  }}
                >
                  {l}
                </a>
              );
            })}
          </nav>

          {/* Phone */}
          <div style={{ padding: '20px 24px 28px', borderTop: '1px solid rgba(255,255,255,0.07)' }}>
            <a href="tel:6141666340" style={{
              display: 'flex', alignItems: 'center', gap: 10,
              color: 'rgba(255,255,255,0.45)', fontFamily: 'JetBrains Mono, monospace',
              fontSize: 12, letterSpacing: '0.1em', textDecoration: 'none',
            }}>
              <span style={{ color: 'var(--pp-red)', fontSize: 9 }}>◆</span>
              614 166 6340
            </a>
          </div>
        </div>
      </>
    );
  }
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '20px 56px', background: '#0a0a0a',
      borderBottom: '1px solid rgba(255,255,255,0.08)',
      color: '#fff',
    }}>
      <a href="#inicio" className="pp-logo-link">
        <img src={LOGO_H_RED} alt="ProPower" style={{ height: 72, display: 'block' }} />
      </a>
      <div style={{ display: 'flex', gap: 40, fontSize: 14, fontWeight: 500, letterSpacing: '0.02em' }}>
        {links.map(({ l, href }) => (
          <a key={l} href={href} className={`pp-nav-link ${l === active ? 'is-active' : ''}`} style={{ color: l === active ? 'var(--pp-red)' : '#fff' }}>
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
};

// ==================== HERO ====================
const HeroDesktop = () => {
  const [idx, setIdx] = React.useState(0);
  React.useEffect(() => {
    const t = setInterval(() => setIdx(i => (i + 1) % HERO_IMGS.length), 5000);
    return () => clearInterval(t);
  }, []);
  return (
  <div id="inicio" style={{ width: '100%', background: '#0a0a0a', color: '#fff', fontFamily: 'Figtree, sans-serif' }}>
    <Nav active="Inicio" />
    <div style={{ position: 'relative', height: 'clamp(460px, 72vh, 640px)', overflow: 'hidden' }}>
      {HERO_IMGS.map((src, i) => (
        <img key={src} src={src} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', opacity: i === idx ? 1 : 0, transition: 'opacity 1.2s ease-in-out' }} />
      ))}
      <div style={{
        position: 'absolute', inset: 0,
        background: 'linear-gradient(90deg, rgba(10,10,10,0.94) 0%, rgba(10,10,10,0.75) 45%, rgba(10,10,10,0.3) 80%, rgba(10,10,10,0.55) 100%)',
      }} />
      <div style={{ position: 'relative', padding: '64px 56px', maxWidth: 860 }}>
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 10, fontSize: 12, fontWeight: 700, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 24 }}>
          <span style={{ width: 28, height: 1, background: 'var(--pp-red)' }} />
          ProPower Electroconstrucciones · desde 2018
        </div>
        <h1 style={{
          fontFamily: 'Archivo, sans-serif', fontWeight: 900,
          fontSize: 96, lineHeight: 0.92, letterSpacing: '-0.04em',
          margin: 0, textTransform: 'uppercase',
        }}>
          Soluciones,<br/>
          calidad y<br/>
          <span style={{ color: 'var(--pp-red)' }}>garantía.</span>
        </h1>
        <div style={{ display: 'flex', gap: 0, marginTop: 48, border: '1px solid rgba(255,255,255,0.15)' }}>
          {[
            { n: '8+', l: 'Años operando' },
            { n: '200+', l: 'Proyectos entregados' },
            { n: '2', l: 'Sucursales en CHIH' },
            { n: '100%', l: 'Capital mexicano' },
          ].map((s, i) => (
            <div key={s.l} style={{ flex: 1, padding: '16px 20px', borderRight: i < 3 ? '1px solid rgba(255,255,255,0.15)' : 'none' }}>
              <div className="pp-counter" data-counter={s.n} style={{ fontFamily: 'Archivo, sans-serif', fontSize: 30, fontWeight: 800, letterSpacing: '-0.02em', color: '#fff' }}>{s.n}</div>
              <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', textTransform: 'uppercase', letterSpacing: '0.12em', marginTop: 2 }}>{s.l}</div>
            </div>
          ))}
        </div>
        <div style={{ display: 'flex', gap: 20, marginTop: 32, alignItems: 'center' }}>
          <a href="#contacto" className="pp-cta" style={{ padding: '16px 30px', background: 'var(--pp-red)', color: '#fff', fontSize: 14, fontWeight: 700, letterSpacing: '0.04em', textTransform: 'uppercase', textDecoration: 'none', display: 'inline-block', border: '1px solid var(--pp-red)' }}>
            Contáctanos →
          </a>
          <a href="#nosotros" style={{ fontSize: 13, color: 'rgba(255,255,255,0.7)', fontFamily: 'JetBrains Mono, monospace', letterSpacing: '0.08em', textTransform: 'uppercase', cursor: 'pointer', textDecoration: 'none' }}>
            ↓ Conoce nuestro proceso
          </a>
        </div>
      </div>
      <div style={{ position: 'absolute', bottom: 28, left: 56, right: 56, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div style={{ display: 'flex', gap: 6 }}>
          {HERO_IMGS.map((_, i) => (
            <div key={i} onClick={() => setIdx(i)} style={{ width: i === idx ? 36 : 10, height: 3, background: i === idx ? 'var(--pp-red)' : 'rgba(255,255,255,0.35)', transition: 'all 0.3s', cursor: 'pointer' }} />
          ))}
        </div>
        <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.1em', textTransform: 'uppercase' }}>
          {String(idx + 1).padStart(2, '0')} / {String(HERO_IMGS.length).padStart(2, '0')}
        </div>
      </div>
    </div>
  </div>
  );
};

const HeroMobile = () => {
  const [idx, setIdx] = React.useState(0);
  React.useEffect(() => {
    const t = setInterval(() => setIdx(i => (i + 1) % HERO_IMGS.length), 4500);
    return () => clearInterval(t);
  }, []);
  return (
    <div id="inicio" style={{ background: '#0a0a0a', color: '#fff' }}>
      <Nav mobile />
      <div style={{ position: 'relative', height: '92vw', maxHeight: 520, minHeight: 380, overflow: 'hidden' }}>
        {HERO_IMGS.map((src, i) => (
          <img key={src} src={src} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', opacity: i === idx ? 1 : 0, transition: 'opacity 1s ease-in-out' }} />
        ))}
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, rgba(10,10,10,0.35) 0%, rgba(10,10,10,0.92) 65%)' }} />
        <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, padding: '20px' }}>
          <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 14 }}>
            ◼ Desde 2018
          </div>
          <h1 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 900, fontSize: 'clamp(32px, 9vw, 44px)', lineHeight: 0.94, letterSpacing: '-0.035em', margin: 0, textTransform: 'uppercase' }}>
            Soluciones, calidad y <span style={{ color: 'var(--pp-red)' }}>garantía.</span>
          </h1>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 0, border: '1px solid rgba(255,255,255,0.15)', marginTop: 20 }}>
            {[['8+','años'],['200+','obras'],['2','sedes'],['100%','MX']].map(([n,l],i) => (
              <div key={l} style={{ padding: '8px 6px', borderRight: i < 3 ? '1px solid rgba(255,255,255,0.15)' : 'none', textAlign: 'center' }}>
                <div style={{ fontFamily: 'Archivo, sans-serif', fontSize: 16, fontWeight: 800 }}>{n}</div>
                <div style={{ fontSize: 9, color: 'rgba(255,255,255,0.55)', textTransform: 'uppercase', letterSpacing: '0.1em' }}>{l}</div>
              </div>
            ))}
          </div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 14 }}>
            <a href="#contacto" style={{ display: 'inline-block', padding: '12px 20px', background: 'var(--pp-red)', color: '#fff', fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', textDecoration: 'none' }}>
              Contáctanos →
            </a>
            <div style={{ display: 'flex', gap: 5 }}>
              {HERO_IMGS.map((_, i) => (
                <div key={i} onClick={() => setIdx(i)} style={{ width: i === idx ? 24 : 8, height: 2, background: i === idx ? 'var(--pp-red)' : 'rgba(255,255,255,0.35)', transition: 'all 0.3s', cursor: 'pointer' }} />
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

// ==================== Sectores ====================
const IconBolt = () => <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.5"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" /></svg>;
const IconPick = () => <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.5"><path d="M18 2l-7 7M5 5l14 14M10 10l-5 5-3-3 5-5M14 14l5 5 3-3-5-5" /></svg>;
const IconCompass = () => <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.5"><circle cx="12" cy="12" r="10" /><path d="M16.24 7.76l-2.12 6.36-6.36 2.12 2.12-6.36 6.36-2.12z" /></svg>;

const OfertaDesktop = () => (
  <section style={{ padding: '120px 56px', background: '#fff' }}>
    <div className="reveal" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: 56 }}>
      <div>
        <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.25em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 16 }}>
          Nuestra oferta
        </div>
        <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 56, lineHeight: 1.0, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>
          Tres sectores.<br/>Una sola exigencia.
        </h2>
      </div>
      <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 12, color: '#78716c', letterSpacing: '0.1em', textTransform: 'uppercase' }}>
        Industria · Minería · Ingeniería
      </div>
    </div>
    <div className="stagger" style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16 }}>
      {[
        { img: '/assets/img/Inicio/pexels-sergey-sergeev-2153675005-32845692.jpg', t: 'Industria', d: 'Impulsa tu industria hoy con nuestros servicios electromecánicos.', Icon: IconBolt, tags: ['Subestaciones', 'Tableros', 'Automatización'] },
        { img: '/assets/img/Inicio/pexels-hannu-iso-oja-3301403-4946889.jpg', t: 'Minería', d: 'Explora nuestra oferta diseñada especialmente para la minería.', Icon: IconPick, tags: ['Subestaciones móviles', 'Bombeo', 'Cable G/GGC'] },
        { img: '/assets/img/Inicio/pexels-freek-wolsink-508219-34207359.jpg', t: 'Ingeniería', d: 'Descubre nuestras soluciones de ingeniería personalizadas para optimizar tu proyecto.', Icon: IconCompass, tags: ['Proyecto llave', 'Supervisión'] },
      ].map((c, i) => (
        <div key={c.t} style={{ position: 'relative', height: 'clamp(320px, 42vh, 460px)', overflow: 'hidden', cursor: 'pointer' }}>
          <img src={c.img} alt={c.t} loading="lazy" decoding="async" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, rgba(10,10,10,0.15) 0%, rgba(10,10,10,0.88) 70%)' }} />
          <div style={{ position: 'absolute', top: 24, left: 24, right: 24, display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
            <div style={{ width: 56, height: 56, border: '1px solid rgba(255,255,255,0.35)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <c.Icon />
            </div>
            <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.2em', color: 'rgba(255,255,255,0.7)', textTransform: 'uppercase' }}>
              0{i+1} / 03
            </div>
          </div>
          <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, padding: '28px', color: '#fff' }}>
            <h3 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 36, margin: 0, letterSpacing: '-0.02em', textTransform: 'uppercase' }}>{c.t}</h3>
            <p style={{ fontSize: 15, color: 'rgba(255,255,255,0.85)', marginTop: 10, lineHeight: 1.5 }}>{c.d}</p>
            <div style={{ display: 'flex', gap: 8, marginTop: 16, flexWrap: 'wrap' }}>
              {c.tags.map(t => (
                <span key={t} style={{ fontSize: 11, padding: '4px 10px', border: '1px solid rgba(255,255,255,0.35)', textTransform: 'uppercase', letterSpacing: '0.08em', fontFamily: 'JetBrains Mono, monospace' }}>{t}</span>
              ))}
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginTop: 22, fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', color: 'var(--pp-red)' }}>
              Más información <span>→</span>
            </div>
          </div>
        </div>
      ))}
    </div>
  </section>
);

// ==================== NOSOTROS ====================
const NosotrosDesktop = () => (
  <section id="nosotros" style={{ padding: '120px 56px', background: '#0a0a0a', color: '#fff' }}>
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.2fr', gap: 80 }}>
      <div className="reveal-left">
        <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.25em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 20 }}>
          ¿Quiénes somos?
        </div>
        <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 64, lineHeight: 0.98, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>
          100%<br/>mexicana.<br/><span style={{ color: 'var(--pp-red)' }}>Siempre a la</span><br/>vanguardia.
        </h2>
        <div style={{ marginTop: 40, fontFamily: 'Archivo Narrow, sans-serif', fontSize: 18, color: 'rgba(255,255,255,0.55)', lineHeight: 1.6, letterSpacing: '0.02em' }}>
          Desde 2018 · Chihuahua · México
        </div>
      </div>
      <div className="reveal-right">
        <p style={{ fontSize: 19, lineHeight: 1.65, color: 'rgba(255,255,255,0.85)', margin: 0 }}>
          <strong style={{ color: '#fff' }}>ProPower Electroconstrucciones</strong> es una empresa 100% mexicana
          especializada en servicios electromecánicos industriales y comerciales.
        </p>
        <p style={{ fontSize: 17, lineHeight: 1.65, color: 'rgba(255,255,255,0.7)', marginTop: 22 }}>
          Nuestro equipo, ambicioso y con un fuerte espíritu de trabajo, se mantiene siempre a la vanguardia,
          con el objetivo de ofrecer a nuestros clientes seguridad y calidad en cada proyecto.
        </p>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 0, marginTop: 48, border: '1px solid rgba(255,255,255,0.15)' }}>
          {[
            { t: 'Misión', d: 'Entregar soluciones electromecánicas con garantía y seguridad, superando las expectativas de cada cliente.' },
            { t: 'Visión', d: 'Ser el contratista de referencia en el norte de México en electroconstrucciones industriales.' },
            { t: 'Valores', d: 'Compromiso, responsabilidad y honestidad en cada obra y en cada relación.' },
          ].map((m, i) => (
            <div key={m.t} style={{ padding: '24px 22px', borderRight: i < 2 ? '1px solid rgba(255,255,255,0.15)' : 'none' }}>
              <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.2em', color: 'var(--pp-red)', textTransform: 'uppercase', marginBottom: 14 }}>0{i+1} · {m.t}</div>
              <p style={{ fontSize: 14, lineHeight: 1.55, color: 'rgba(255,255,255,0.8)', margin: 0 }}>{m.d}</p>
            </div>
          ))}
        </div>
        <div style={{ display: 'flex', gap: 28, marginTop: 40, fontFamily: 'JetBrains Mono, monospace', fontSize: 12, textTransform: 'uppercase', letterSpacing: '0.18em' }}>
          <span style={{ color: 'var(--pp-red)' }}>✦ Compromiso</span>
          <span style={{ color: 'var(--pp-red)' }}>✦ Responsabilidad</span>
          <span style={{ color: 'var(--pp-red)' }}>✦ Honestidad</span>
        </div>
      </div>
    </div>
  </section>
);

// ==================== SERVICIOS ====================
const SERVICES_INDUSTRIA = [
  { img: '/assets/img/Servicios/instalaciones-electricas.webp', t: 'Instalaciones eléctricas en baja y media tensión' },
  { img: '/assets/img/Servicios/b.webp', t: 'Implementación y ejecución de programas de mantenimientos' },
  { img: '/assets/img/Servicios/c.webp', t: 'Cálculo e instalación de sistemas de iluminación' },
  { img: '/assets/img/Servicios/d.webp', t: 'Instalación de sistemas mecánicos' },
  { img: '/assets/img/Servicios/e.webp', t: 'Estructuras, soldadura y pintura industrial' },
  { img: '/assets/img/Servicios/f.webp', t: 'Pruebas de resistencia de aislamiento' },
  { img: '/assets/img/Servicios/g.webp', t: 'Control y automatización de procesos' },
  { img: '/assets/img/Servicios/h.webp', t: 'Venta y montaje de transformadores' },
  { img: '/assets/img/Servicios/i.webp', t: 'Maniobras de izaje, montaje y colocación' },
  { img: '/assets/img/Servicios/j.webp', t: 'Venta e instalación de bancos de capacitores' },
  { img: '/assets/img/Servicios/k.webp', t: 'Mantenimiento a subestaciones eléctricas' },
  { img: '/assets/img/Servicios/l.webp', t: 'Memorias de cálculo eléctrico (NOM-001-SEDE-2012)' },
  { img: '/assets/img/Servicios/m.webp', t: 'Reparación equipo y motores eléctricos de cualquier capacidad' },
  { img: '/assets/img/Servicios/n.webp', t: 'Instalación de centros de control de motores (CCM)' },
  { img: '/assets/img/Servicios/ñ.webp', t: 'Venta de equipo y material eléctrico' },
  { img: '/assets/img/Servicios/o.webp', t: 'Pruebas de termografía y ultrasonido' },
  { img: '/assets/img/Servicios/p.webp', t: 'Dictámenes eléctricos (NOM-001-SEDE-2012)' },
];
const SERVICES_MINERIA = [
  { img: '/assets/img/Servicios/q.webp', t: 'Líneas de baja y media tensión' },
  { img: '/assets/img/Servicios/r.webp', t: 'Venta e instalación de transformadores' },
  { img: '/assets/img/Servicios/s.webp', t: 'Subestaciones móviles para interior mina 4.16 y 13.8 KV' },
  { img: '/assets/img/Servicios/t.webp', t: 'Venta, instalación y mantenimiento de bombas sumergibles, horizontales, etc.' },
  { img: '/assets/img/Servicios/u.webp', t: 'Reparación y mantenimiento a motores de cualquier capacidad' },
  { img: '/assets/img/Servicios/v.webp', t: 'Proyectos de iluminación' },
  { img: '/assets/img/Servicios/w.webp', t: 'Venta de tablero tipo centinela avanzado' },
  { img: '/assets/img/Servicios/x.webp', t: 'Venta de cable tipo G y GGC' },
  { img: '/assets/img/Servicios/y.webp', t: 'Venta de arrancadores suaves, variadores de frecuencia, etc.' },
  { img: '/assets/img/Servicios/z.webp', t: 'Venta y reparación de ventiladores tipo Zitron' },
];
const SERVICES_INGENIERIA = [
  { img: '/assets/img/Servicios/aa.webp', t: 'Diseño y planos', items: ['Diseño CAD', 'Diagramas eléctricos', 'Layout', 'Planos de control', 'Programación'] },
  { img: '/assets/img/Servicios/bb.webp', t: 'Análisis y mediciones', items: ['Calidad de la energía', 'Resistencia de puesta a tierra', 'Resistividad del terreno'] },
  { img: '/assets/img/Servicios/cc.webp', t: 'Cálculo y dictámenes', items: ['Memorias de cálculo', 'Cálculo de iluminación', 'Tierra y pararrayos', 'Dictámenes eléctricos'] },
];

const ServiceCard = ({ s, i }) => {
  const [hover, setHover] = React.useState(false);
  return (
    <a href="#contacto" onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)} style={{
      position: 'relative', display: 'block', height: 'clamp(200px, 22vw, 280px)', overflow: 'hidden',
      background: '#0a0a0a', textDecoration: 'none',
    }}>
      <img src={s.img} alt={s.t} loading="lazy" style={{
        position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover',
        transform: hover ? 'scale(1.06)' : 'scale(1)', transition: 'transform 0.5s, filter 0.3s',
        filter: hover ? 'brightness(0.6)' : 'brightness(0.55)',
      }} />
      <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, transparent 30%, rgba(10,10,10,0.95) 100%)' }} />
      <div style={{ position: 'absolute', top: 16, left: 16, fontFamily: 'JetBrains Mono, monospace', fontSize: 10, color: 'var(--pp-red)', letterSpacing: '0.2em', padding: '4px 8px', background: 'rgba(10,10,10,0.7)', border: '1px solid var(--pp-red)' }}>
        {String(i + 1).padStart(2, '0')}
      </div>
      <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, padding: 22, color: '#fff' }}>
        {s.items ? (
          <>
            <h3 style={{ fontFamily: 'Archivo, sans-serif', fontSize: 22, fontWeight: 800, margin: 0, letterSpacing: '-0.02em', textTransform: 'uppercase', lineHeight: 1.05 }}>{s.t}</h3>
            <div style={{ marginTop: 10, fontSize: 12, color: 'rgba(255,255,255,0.85)', lineHeight: 1.5 }}>
              {s.items.join(' · ')}
            </div>
          </>
        ) : (
          <h3 style={{ fontFamily: 'Archivo, sans-serif', fontSize: 18, fontWeight: 700, margin: 0, letterSpacing: '-0.01em', textTransform: 'uppercase', lineHeight: 1.15 }}>{s.t}</h3>
        )}
        <div style={{ marginTop: 12, height: 2, width: hover ? 60 : 24, background: 'var(--pp-red)', transition: 'width 0.3s' }} />
      </div>
    </a>
  );
};

const ServiciosDesktop = () => {
  const [tab, setTab] = React.useState('industria');
  const tabs = [
    { id: 'industria', l: 'Industria', n: SERVICES_INDUSTRIA.length },
    { id: 'mineria', l: 'Minería', n: SERVICES_MINERIA.length },
    { id: 'ingenieria', l: 'Ingeniería', n: SERVICES_INGENIERIA.length },
  ];
  const data = tab === 'industria' ? SERVICES_INDUSTRIA : tab === 'mineria' ? SERVICES_MINERIA : SERVICES_INGENIERIA;
  return (
    <section id="servicios" style={{ padding: '120px 56px', background: '#fff' }}>
      <div className="reveal" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: 48 }}>
        <div>
          <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.25em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 16 }}>Servicios</div>
          <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 56, lineHeight: 1.0, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>
            30 servicios.<br/>Tres especialidades.
          </h2>
        </div>
        <p style={{ fontSize: 15, color: '#57534e', maxWidth: 320, lineHeight: 1.6, margin: 0 }}>
          Desde la planeación eléctrica hasta la puesta en marcha, cubrimos cada etapa de tu obra con personal certificado.
        </p>
      </div>
      <div style={{ display: 'flex', gap: 0, marginBottom: 32, borderBottom: '1px solid #e7e5e4' }}>
        {tabs.map(tb => (
          <button key={tb.id} onClick={() => setTab(tb.id)} style={{
            padding: '18px 28px', background: 'transparent', border: 'none',
            borderBottom: tab === tb.id ? '3px solid var(--pp-red)' : '3px solid transparent',
            cursor: 'pointer', fontFamily: 'Archivo, sans-serif', fontSize: 18, fontWeight: 700,
            textTransform: 'uppercase', letterSpacing: '0.04em',
            color: tab === tb.id ? '#0a0a0a' : '#a8a29e',
            display: 'flex', alignItems: 'center', gap: 10, transition: 'all 0.2s',
          }}>
            {tb.l}
            <span style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, color: tab === tb.id ? 'var(--pp-red)' : '#a8a29e' }}>{String(tb.n).padStart(2, '0')}</span>
          </button>
        ))}
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12 }}>
        {data.map((s, i) => <ServiceCard key={s.t} s={s} i={i} />)}
      </div>
      <div style={{ marginTop: 100 }}>
        <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.2em', color: '#78716c', marginBottom: 28, textAlign: 'center' }}>
          Trabajamos con las mejores marcas del sector · {BRAND_LOGOS.length} marcas
        </div>
        <div className="brand-marquee" style={{ overflow: 'hidden', position: 'relative', maskImage: 'linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)', WebkitMaskImage: 'linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)' }}>
          <div className="brand-track" style={{ display: 'flex', gap: 0, animation: 'scrollX 60s linear infinite', width: 'max-content' }}>
            {[...BRAND_LOGOS, ...BRAND_LOGOS].map((src, i) => (
              <div key={i} style={{ flex: '0 0 auto', width: 180, height: 110, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 22, borderRight: '1px solid #e7e5e4' }}>
                <img src={src} alt="" className="brand-logo" />
              </div>
            ))}
          </div>
        </div>
      </div>
      <div style={{ marginTop: 64 }}>
        <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.2em', color: '#78716c', marginBottom: 28, textAlign: 'center' }}>
          Algunos de nuestros clientes · {CLIENT_LOGOS.length} empresas
        </div>
        <div className="brand-marquee" style={{ overflow: 'hidden', position: 'relative', maskImage: 'linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)', WebkitMaskImage: 'linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)' }}>
          <div className="brand-track" style={{ display: 'flex', gap: 0, animation: 'scrollXReverse 80s linear infinite', width: 'max-content' }}>
            {[...CLIENT_LOGOS, ...CLIENT_LOGOS].map((src, i) => (
              <div key={i} style={{ flex: '0 0 auto', width: 180, height: 110, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 22, borderRight: '1px solid #e7e5e4' }}>
                <img src={src} alt="" className="brand-logo" />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

// ==================== GALERÍA ====================
const PROJECTS = [
  { img: '/assets/img/Galeria/Media-Tension/25.webp', t: 'Subestación industrial', loc: 'Chihuahua, CHIH', year: '2024', cat: 'Media tensión' },
  { img: '/assets/img/Galeria/Baja-Tension/30.webp', t: 'Nave de manufactura', loc: 'Delicias, CHIH', year: '2024', cat: 'Baja tensión' },
  { img: '/assets/img/Galeria/Mejora de tableros electricos existentes/2.webp', t: 'Tableros eléctricos', loc: 'Sierra, CHIH', year: '2023', cat: 'Tableros' },
  { img: '/assets/img/Galeria/Actualizaciones de control para planta energetica/1.webp', t: 'Control de planta', loc: 'Chihuahua, CHIH', year: '2023', cat: 'Control' },
  { img: '/assets/img/Galeria/Pruebas Electricas/3.webp', t: 'Pruebas eléctricas', loc: 'Parque Industrial', year: '2023', cat: 'Pruebas' },
  { img: '/assets/img/Galeria/Remplazo de laminas translucidas y actualizacion de iluminacion led/11.webp', t: 'Iluminación LED industrial', loc: 'Nave 12, CHIH', year: '2022', cat: 'Iluminación' },
  { img: '/assets/img/Galeria/Laminado de estructura de molino/1.webp', t: 'Estructura de molino', loc: 'Proyecto industrial', year: '2022', cat: 'Estructural' },
];

const GaleriaDesktop = () => (
  <section id="galeria" style={{ padding: '120px 56px', background: '#fafaf9' }}>
    <div className="reveal" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: 56 }}>
      <div>
        <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.25em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 16 }}>Galería</div>
        <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 56, lineHeight: 1.0, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>
          Obras que<br/>hablan por sí solas.
        </h2>
      </div>
      <div style={{ display: 'flex', gap: 8, fontFamily: 'JetBrains Mono, monospace', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.12em' }}>
        {['Todos', 'Industria', 'Minería', 'Ingeniería'].map((f, i) => (
          <span key={f} style={{ padding: '8px 14px', border: '1px solid #0a0a0a', background: i === 0 ? '#0a0a0a' : 'transparent', color: i === 0 ? '#fff' : '#0a0a0a' }}>{f}</span>
        ))}
      </div>
    </div>
    <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr', gridTemplateRows: 'repeat(3, 240px)', gap: 12 }}>
      <div style={{ gridColumn: '1 / 2', gridRow: '1 / 3', position: 'relative', overflow: 'hidden', cursor: 'pointer' }}>
        <img src={PROJECTS[0].img} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, transparent 40%, rgba(10,10,10,0.88) 100%)' }} />
        <div style={{ position: 'absolute', top: 20, left: 20, fontFamily: 'JetBrains Mono, monospace', fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: '#fff', padding: '5px 10px', background: 'var(--pp-red)' }}>
          Proyecto destacado
        </div>
        <div style={{ position: 'absolute', bottom: 28, left: 28, right: 28, color: '#fff' }}>
          <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.7)', marginBottom: 8 }}>
            {PROJECTS[0].cat} · {PROJECTS[0].year}
          </div>
          <h3 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 36, margin: 0, letterSpacing: '-0.02em', textTransform: 'uppercase' }}>{PROJECTS[0].t}</h3>
          <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.85)', marginTop: 6 }}>{PROJECTS[0].loc}</div>
        </div>
      </div>
      {PROJECTS.slice(1, 5).map((p) => (
        <div key={p.t} style={{ position: 'relative', overflow: 'hidden', cursor: 'pointer' }}>
          <img src={p.img} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, transparent 40%, rgba(10,10,10,0.85) 100%)' }} />
          <div style={{ position: 'absolute', bottom: 16, left: 16, right: 16, color: '#fff' }}>
            <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 9, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.7)', marginBottom: 4 }}>{p.cat} · {p.year}</div>
            <h4 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 700, fontSize: 18, margin: 0, letterSpacing: '-0.01em', textTransform: 'uppercase', lineHeight: 1.1 }}>{p.t}</h4>
          </div>
        </div>
      ))}
      <div style={{ gridColumn: '1 / 3', position: 'relative', overflow: 'hidden', cursor: 'pointer' }}>
        <img src={PROJECTS[5].img} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(90deg, rgba(10,10,10,0.85) 0%, rgba(10,10,10,0.2) 60%)' }} />
        <div style={{ position: 'absolute', bottom: 20, left: 24, color: '#fff' }}>
          <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 10, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.7)', marginBottom: 4 }}>{PROJECTS[5].cat} · {PROJECTS[5].year}</div>
          <h4 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 700, fontSize: 22, margin: 0, letterSpacing: '-0.01em', textTransform: 'uppercase' }}>{PROJECTS[5].t}</h4>
        </div>
      </div>
      <div style={{ position: 'relative', overflow: 'hidden', cursor: 'pointer' }}>
        <img src={PROJECTS[6].img} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, transparent 40%, rgba(10,10,10,0.85) 100%)' }} />
        <div style={{ position: 'absolute', bottom: 16, left: 16, right: 16, color: '#fff' }}>
          <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 9, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.7)', marginBottom: 4 }}>{PROJECTS[6].cat}</div>
          <h4 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 700, fontSize: 18, margin: 0, letterSpacing: '-0.01em', textTransform: 'uppercase', lineHeight: 1.1 }}>{PROJECTS[6].t}</h4>
        </div>
      </div>
    </div>
    <div style={{ display: 'flex', justifyContent: 'center', marginTop: 48 }}>
      <a href="/galeria" style={{ padding: '16px 36px', background: '#0a0a0a', color: '#fff', fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', fontFamily: 'JetBrains Mono, monospace', textDecoration: 'none' }}>
        Ver galería completa →
      </a>
    </div>
  </section>
);

// ==================== CONTACTO ====================
const ContactoDesktop = () => {
  const SECTORES = ['Industria', 'Minería', 'Ingeniería', 'Mantenimiento', 'Otro'];
  const [form, setForm] = React.useState({ nombre: '', empresa: '', correo: '', telefono: '', sector: 'Industria', mensaje: '' });
  const [status, setStatus] = React.useState('idle'); // idle | loading | success | error
  const set = k => e => setForm(f => ({ ...f, [k]: e.target.value }));
  const inputStyle = { background: 'transparent', border: 'none', outline: 'none', color: '#fff', fontSize: 17, width: '100%', fontFamily: 'Archivo, sans-serif', padding: 0 };
  const labelStyle = { fontFamily: 'JetBrains Mono, monospace', fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)', marginBottom: 8, display: 'block' };
  const rowStyle = { borderBottom: '1px solid rgba(255,255,255,0.15)', padding: '18px 0' };

  const handleSubmit = async e => {
    e.preventDefault();
    setStatus('loading');
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const res = await fetch('/contacto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(form),
      });
      setStatus(res.ok ? 'success' : 'error');
    } catch {
      setStatus('error');
    }
  };

  return (
  <section id="contacto" style={{ background: '#0a0a0a', color: '#fff' }}>
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr' }}>
      <div className="reveal-left" style={{ padding: '100px 56px', borderRight: '1px solid rgba(255,255,255,0.1)' }}>
        <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.25em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 20 }}>Contacto</div>
        <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 52, lineHeight: 0.98, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>
          Cuéntanos tu<br/><span style={{ color: 'var(--pp-red)' }}>proyecto.</span>
        </h2>
        <p style={{ fontSize: 16, color: 'rgba(255,255,255,0.65)', marginTop: 18, marginBottom: 40, lineHeight: 1.6, maxWidth: 440 }}>
          Escríbenos y un asesor técnico se pondrá en contacto contigo en menos de 24 horas hábiles.
        </p>

        {status === 'success' ? (
          <div style={{ padding: '48px 0' }}>
            <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 16 }}>Mensaje enviado</div>
            <p style={{ fontSize: 18, color: 'rgba(255,255,255,0.8)', lineHeight: 1.6 }}>Gracias, {form.nombre.split(' ')[0]}. Un asesor se pondrá en contacto contigo pronto.</p>
          </div>
        ) : (
        <form onSubmit={handleSubmit} style={{ display: 'grid', gap: 0 }}>
          {[
            { l: 'Nombre *', k: 'nombre', p: 'Tu nombre completo', t: 'text', req: true },
            { l: 'Empresa', k: 'empresa', p: 'Nombre de tu empresa', t: 'text' },
            { l: 'Correo *', k: 'correo', p: 'correo@empresa.com', t: 'email', req: true },
            { l: 'Teléfono', k: 'telefono', p: '+52 614 000 0000', t: 'tel' },
          ].map(f => (
            <div key={f.k} style={rowStyle}>
              <label style={labelStyle}>{f.l}</label>
              <input type={f.t} required={f.req} placeholder={f.p} value={form[f.k]} onChange={set(f.k)} style={inputStyle} />
            </div>
          ))}
          <div style={rowStyle}>
            <label style={labelStyle}>Sector / Tipo de proyecto</label>
            <div style={{ display: 'flex', gap: 8, marginTop: 6, flexWrap: 'wrap' }}>
              {SECTORES.map(s => (
                <button type="button" key={s} onClick={() => setForm(f => ({ ...f, sector: s }))} style={{ padding: '6px 12px', border: '1px solid rgba(255,255,255,0.25)', fontSize: 12, textTransform: 'uppercase', letterSpacing: '0.06em', fontFamily: 'JetBrains Mono, monospace', background: form.sector === s ? 'var(--pp-red)' : 'transparent', color: '#fff', cursor: 'pointer' }}>{s}</button>
              ))}
            </div>
          </div>
          <div style={{ ...rowStyle, borderBottom: 'none' }}>
            <label style={labelStyle}>Mensaje *</label>
            <textarea required rows={4} placeholder="Cuéntanos sobre tu obra, ubicación y fechas tentativas…" value={form.mensaje} onChange={set('mensaje')} style={{ ...inputStyle, resize: 'vertical', lineHeight: 1.6 }} />
          </div>
          {status === 'error' && (
            <div style={{ fontSize: 13, color: '#f87171', marginBottom: 8 }}>Ocurrió un error. Intenta de nuevo o escríbenos directamente.</div>
          )}
          <button type="submit" disabled={status === 'loading'} style={{ display: 'inline-flex', padding: '18px 36px', background: 'var(--pp-red)', color: '#fff', fontSize: 14, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', marginTop: 24, width: 'fit-content', border: 'none', cursor: 'pointer', opacity: status === 'loading' ? 0.6 : 1 }}>
            {status === 'loading' ? 'Enviando…' : 'Enviar mensaje →'}
          </button>
        </form>
        )}
      </div>
      <div className="reveal-right" style={{ padding: '100px 56px' }}>
        <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)', marginBottom: 20 }}>Contacto directo</div>
        <div style={{ display: 'grid', gap: 20, marginBottom: 48 }}>
          {[
            { k: 'Teléfono', v: '614 166 6340' },
            { k: 'Correo', v: 'contacto@propower.mx' },
            { k: 'Horario', v: 'Lun–Vie · 9:00–18:00' },
          ].map(x => (
            <div key={x.k} style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid rgba(255,255,255,0.1)', paddingBottom: 14 }}>
              <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)' }}>{x.k}</div>
              <div style={{ fontFamily: 'Archivo, sans-serif', fontSize: 22, fontWeight: 700 }}>{x.v}</div>
            </div>
          ))}
        </div>
        <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 11, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)', marginBottom: 20 }}>Sucursales</div>
        <div style={{ display: 'grid', gap: 20 }}>
          {[
            { t: 'Sucursal Chihuahua', sub: '', emb: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6537.871959268932!2d-106.12901740537757!3d28.70382956590884!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86ea438974075dc5%3A0xb8c2426f69011cbb!2sProPower%20Electroconstrucciones!5e0!3m2!1ses-419!2smx!4v1763958506976!5m2!1ses-419!2smx' },
            { t: 'Sucursal Delicias', sub: '', emb: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8259.094005874922!2d-105.45656117616318!3d28.183644407838823!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86eb159b6256c213%3A0x3aa93cc16e2a0b9!2sPropower%20Electroconstrucciones!5e0!3m2!1ses-419!2smx!4v1763958405258!5m2!1ses-419!2smx' },
          ].map(s => (
            <div key={s.t} style={{ border: '1px solid rgba(255,255,255,0.15)' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '14px 18px', borderBottom: '1px solid rgba(255,255,255,0.15)' }}>
                <div>
                  <div style={{ fontFamily: 'Archivo, sans-serif', fontSize: 18, fontWeight: 700, textTransform: 'uppercase' }}>{s.t}</div>
                </div>
                <div style={{ color: 'var(--pp-red)', fontSize: 12, fontFamily: 'JetBrains Mono, monospace', textTransform: 'uppercase', letterSpacing: '0.1em' }}>Ver ruta →</div>
              </div>
              <iframe src={s.emb} width="100%" height="200" style={{ border: 0, display: 'block', filter: 'grayscale(1) invert(0.92) contrast(0.95)' }} loading="lazy" referrerPolicy="no-referrer-when-downgrade" />
            </div>
          ))}
        </div>
      </div>
    </div>
  </section>
  );
};

// ==================== FOOTER ====================
const Footer = () => (
  <footer style={{ background: '#000', color: '#fff', padding: '80px 56px 32px' }}>
    <div style={{ borderBottom: '1px solid rgba(255,255,255,0.15)', paddingBottom: 40, marginBottom: 40 }}>
      <img src={LOGO_H_RED} alt="ProPower" style={{ height: 140, display: 'block', marginBottom: 8 }} />
    </div>
    <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1fr', gap: 60, paddingBottom: 48 }}>
      <div>
        <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.6)', lineHeight: 1.6, maxWidth: 320 }}>
          Empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales desde 2018.
        </div>
      </div>
      {[
        { t: 'Navegación', l: ['Inicio', 'Nosotros', 'Servicios', 'Galería', 'Contacto'] },
        { t: 'Sectores', l: ['Industria', 'Minería', 'Ingeniería'] },
        { t: 'Contacto', l: ['614 166 6340', 'contacto@propower.mx', 'Chihuahua, CHIH', 'Delicias, CHIH'] },
      ].map(col => (
        <div key={col.t}>
          <div style={{ fontSize: 11, fontFamily: 'JetBrains Mono, monospace', textTransform: 'uppercase', letterSpacing: '0.2em', color: 'rgba(255,255,255,0.5)', marginBottom: 20 }}>{col.t}</div>
          {col.l.map(x => <div key={x} style={{ fontSize: 14, color: 'rgba(255,255,255,0.85)', marginBottom: 10 }}>{x}</div>)}
        </div>
      ))}
    </div>
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid rgba(255,255,255,0.1)', paddingTop: 24, fontSize: 12, color: 'rgba(255,255,255,0.45)' }}>
      <div>© 2026 ProPower Electroconstrucciones — Todos los derechos reservados.</div>
      <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
        <a href="#" style={{ color: 'rgba(255,255,255,0.7)', textDecoration: 'none' }}>Aviso de Privacidad</a>
        <span style={{ width: 1, height: 14, background: 'rgba(255,255,255,0.15)' }} />
        <a href="https://wa.me/526141666340" target="_blank" rel="noopener" aria-label="WhatsApp" style={{ width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', border: '1px solid rgba(255,255,255,0.2)', color: '#fff', textDecoration: 'none' }}>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.693.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </a>
        <a href="https://www.facebook.com/ProPowerMX" target="_blank" rel="noopener" aria-label="Facebook" style={{ width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', border: '1px solid rgba(255,255,255,0.2)', color: '#fff', textDecoration: 'none' }}>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
        <a href="https://www.instagram.com/propowermx/" target="_blank" rel="noopener" aria-label="Instagram" style={{ width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', border: '1px solid rgba(255,255,255,0.2)', color: '#fff', textDecoration: 'none' }}>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
        </a>
      </div>
    </div>
  </footer>
);

// ==================== Mobile ====================
const NosotrosMobile = () => (
  <section style={{ padding: '56px 20px', background: '#0a0a0a', color: '#fff' }}>
    <div className="reveal" style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 14 }}>¿Quiénes somos?</div>
    <h2 className="reveal" style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 34, lineHeight: 0.98, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>100% mexicana.<br/><span style={{ color: 'var(--pp-red)' }}>Siempre a la vanguardia.</span></h2>
    <p className="reveal" style={{ fontSize: 15, lineHeight: 1.6, color: 'rgba(255,255,255,0.8)', marginTop: 20 }}>
      <strong style={{ color: '#fff' }}>ProPower Electroconstrucciones</strong> es una empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales.
    </p>
    <div style={{ display: 'grid', gap: 0, marginTop: 24, border: '1px solid rgba(255,255,255,0.15)' }}>
      {[
        { t: 'Misión', d: 'Entregar soluciones con garantía y seguridad.' },
        { t: 'Visión', d: 'Ser el contratista de referencia en el norte de México.' },
        { t: 'Valores', d: 'Compromiso, responsabilidad y honestidad.' },
      ].map((m, i) => (
        <div key={m.t} style={{ padding: '16px 14px', borderBottom: i < 2 ? '1px solid rgba(255,255,255,0.15)' : 'none' }}>
          <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 10, letterSpacing: '0.2em', color: 'var(--pp-red)', textTransform: 'uppercase', marginBottom: 6 }}>0{i+1} · {m.t}</div>
          <p style={{ fontSize: 13, lineHeight: 1.5, color: 'rgba(255,255,255,0.8)', margin: 0 }}>{m.d}</p>
        </div>
      ))}
    </div>
  </section>
);

const ServiciosMobile = () => {
  const [tab, setTab] = React.useState('industria');
  const tabs = [
    { id: 'industria', l: 'Industria', n: SERVICES_INDUSTRIA.length },
    { id: 'mineria', l: 'Minería', n: SERVICES_MINERIA.length },
    { id: 'ingenieria', l: 'Ingeniería', n: SERVICES_INGENIERIA.length },
  ];
  const data = tab === 'industria' ? SERVICES_INDUSTRIA : tab === 'mineria' ? SERVICES_MINERIA : SERVICES_INGENIERIA;
  return (
    <section id="servicios" style={{ padding: '56px 20px', background: '#fff' }}>
      <div className="reveal" style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 12 }}>Servicios</div>
      <h2 className="reveal" style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 30, lineHeight: 1.0, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>30 servicios. Tres especialidades.</h2>
      <div style={{ display: 'flex', gap: 0, marginTop: 20, borderBottom: '1px solid #e7e5e4', overflowX: 'auto' }}>
        {tabs.map(tb => (
          <button key={tb.id} onClick={() => setTab(tb.id)} style={{ padding: '12px 16px', background: 'transparent', border: 'none', borderBottom: tab === tb.id ? '3px solid var(--pp-red)' : '3px solid transparent', cursor: 'pointer', fontFamily: 'Archivo, sans-serif', fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', color: tab === tb.id ? '#0a0a0a' : '#a8a29e', whiteSpace: 'nowrap' }}>
            {tb.l} <span style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 10, color: tab === tb.id ? 'var(--pp-red)' : '#a8a29e' }}>{String(tb.n).padStart(2, '0')}</span>
          </button>
        ))}
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 6, marginTop: 16 }}>
        {data.map((s, i) => (
          <a key={s.t} href="#contacto" style={{ position: 'relative', display: 'block', height: 180, overflow: 'hidden', background: '#0a0a0a', textDecoration: 'none' }}>
            <img src={s.img} alt="" loading="lazy" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', filter: 'brightness(0.55)' }} />
            <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, transparent 30%, rgba(10,10,10,0.95) 100%)' }} />
            <div style={{ position: 'absolute', top: 8, left: 8, fontFamily: 'JetBrains Mono, monospace', fontSize: 8, color: 'var(--pp-red)', letterSpacing: '0.2em', padding: '3px 6px', background: 'rgba(10,10,10,0.7)', border: '1px solid var(--pp-red)' }}>{String(i + 1).padStart(2, '0')}</div>
            <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, padding: 12, color: '#fff' }}>
              <h3 style={{ fontFamily: 'Archivo, sans-serif', fontSize: 12, fontWeight: 700, margin: 0, textTransform: 'uppercase', lineHeight: 1.2 }}>{s.t}</h3>
            </div>
          </a>
        ))}
      </div>
      <div style={{ marginTop: 28 }}>
        <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 9, textTransform: 'uppercase', letterSpacing: '0.2em', color: '#78716c', marginBottom: 14, textAlign: 'center' }}>Marcas aliadas</div>
        <div style={{ overflow: 'hidden', position: 'relative', maskImage: 'linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)', WebkitMaskImage: 'linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent)' }}>
          <div style={{ display: 'flex', gap: 0, animation: 'scrollX 50s linear infinite', width: 'max-content' }}>
            {[...BRAND_LOGOS, ...BRAND_LOGOS].map((src, i) => (
              <div key={i} style={{ flex: '0 0 auto', width: 110, height: 70, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 12, borderRight: '1px solid #e7e5e4' }}>
                <img src={src} alt="" className="brand-logo" />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

const GaleriaMobile = () => (
  <section style={{ padding: '56px 20px', background: '#fafaf9' }}>
    <div className="reveal" style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 12 }}>Galería</div>
    <h2 className="reveal" style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 30, lineHeight: 1.0, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>Obras que hablan por sí solas.</h2>
    <div style={{ display: 'grid', gap: 10, marginTop: 24 }}>
      {PROJECTS.slice(0, 4).map((p, i) => (
        <div key={p.t} style={{ position: 'relative', height: i === 0 ? 240 : 160, overflow: 'hidden' }}>
          <img src={p.img} alt="" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, transparent 40%, rgba(10,10,10,0.9) 100%)' }} />
          <div style={{ position: 'absolute', bottom: 12, left: 14, right: 14, color: '#fff' }}>
            <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 9, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.7)', marginBottom: 4 }}>{p.cat} · {p.year}</div>
            <h4 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 700, fontSize: i === 0 ? 22 : 15, margin: 0, textTransform: 'uppercase' }}>{p.t}</h4>
          </div>
        </div>
      ))}
    </div>
  </section>
);

const ContactoMobile = () => {
  const SECTORES = ['Industria', 'Minería', 'Ingeniería', 'Mantenimiento', 'Otro'];
  const [form, setForm] = React.useState({ nombre: '', empresa: '', correo: '', telefono: '', sector: 'Industria', mensaje: '' });
  const [status, setStatus] = React.useState('idle');
  const set = k => e => setForm(f => ({ ...f, [k]: e.target.value }));
  const inputStyle = { background: 'transparent', border: 'none', outline: 'none', color: '#fff', fontSize: 15, width: '100%', fontFamily: 'Archivo, sans-serif', padding: 0 };
  const labelStyle = { fontFamily: 'JetBrains Mono, monospace', fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)', marginBottom: 6, display: 'block' };

  const handleSubmit = async e => {
    e.preventDefault();
    setStatus('loading');
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const res = await fetch('/contacto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(form),
      });
      setStatus(res.ok ? 'success' : 'error');
    } catch {
      setStatus('error');
    }
  };

  return (
  <section style={{ background: '#0a0a0a', color: '#fff', padding: '56px 20px' }}>
    <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 12 }}>Contacto</div>
    <h2 style={{ fontFamily: 'Archivo, sans-serif', fontWeight: 800, fontSize: 32, lineHeight: 0.98, letterSpacing: '-0.03em', margin: 0, textTransform: 'uppercase' }}>Cuéntanos tu <span style={{ color: 'var(--pp-red)' }}>proyecto.</span></h2>

    {status === 'success' ? (
      <div style={{ marginTop: 28 }}>
        <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--pp-red)', fontFamily: 'JetBrains Mono, monospace', marginBottom: 12 }}>Mensaje enviado</div>
        <p style={{ fontSize: 16, color: 'rgba(255,255,255,0.8)', lineHeight: 1.6 }}>Gracias, {form.nombre.split(' ')[0]}. Un asesor te contactará pronto.</p>
      </div>
    ) : (
    <form onSubmit={handleSubmit} style={{ marginTop: 20 }}>
      {[
        { l: 'Nombre *', k: 'nombre', p: 'Tu nombre completo', t: 'text', req: true },
        { l: 'Empresa', k: 'empresa', p: 'Nombre de tu empresa', t: 'text' },
        { l: 'Correo *', k: 'correo', p: 'correo@empresa.com', t: 'email', req: true },
        { l: 'Teléfono', k: 'telefono', p: '+52 614 000 0000', t: 'tel' },
      ].map(f => (
        <div key={f.k} style={{ borderBottom: '1px solid rgba(255,255,255,0.15)', padding: '14px 0' }}>
          <label style={labelStyle}>{f.l}</label>
          <input type={f.t} required={f.req} placeholder={f.p} value={form[f.k]} onChange={set(f.k)} style={inputStyle} />
        </div>
      ))}
      <div style={{ borderBottom: '1px solid rgba(255,255,255,0.15)', padding: '14px 0' }}>
        <label style={labelStyle}>Sector</label>
        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginTop: 6 }}>
          {SECTORES.map(s => (
            <button type="button" key={s} onClick={() => setForm(f => ({ ...f, sector: s }))} style={{ padding: '5px 10px', border: '1px solid rgba(255,255,255,0.25)', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.06em', fontFamily: 'JetBrains Mono, monospace', background: form.sector === s ? 'var(--pp-red)' : 'transparent', color: '#fff', cursor: 'pointer' }}>{s}</button>
          ))}
        </div>
      </div>
      <div style={{ padding: '14px 0' }}>
        <label style={labelStyle}>Mensaje *</label>
        <textarea required rows={3} placeholder="Cuéntanos sobre tu proyecto…" value={form.mensaje} onChange={set('mensaje')} style={{ ...inputStyle, resize: 'vertical', lineHeight: 1.6 }} />
      </div>
      {status === 'error' && <div style={{ fontSize: 12, color: '#f87171', marginBottom: 8 }}>Ocurrió un error. Intenta de nuevo.</div>}
      <button type="submit" disabled={status === 'loading'} style={{ display: 'block', width: '100%', padding: '14px 24px', background: 'var(--pp-red)', color: '#fff', fontSize: 13, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', marginTop: 20, textAlign: 'center', border: 'none', cursor: 'pointer', opacity: status === 'loading' ? 0.6 : 1 }}>
        {status === 'loading' ? 'Enviando…' : 'Enviar mensaje →'}
      </button>
    </form>
    )}

    <div style={{ marginTop: 32, paddingTop: 24, borderTop: '1px solid rgba(255,255,255,0.1)' }}>
      <div style={{ fontFamily: 'JetBrains Mono, monospace', fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)', marginBottom: 14 }}>Contacto directo</div>
      <div style={{ fontFamily: 'Archivo, sans-serif', fontSize: 22, fontWeight: 700 }}>614 166 6340</div>
      <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.7)', marginTop: 4 }}>contacto@propower.mx</div>
    </div>
  </section>
  );
};

const FooterMobile = () => (
  <footer style={{ background: '#000', color: '#fff', padding: '40px 20px 24px' }}>
    <img src={LOGO_H_RED} alt="ProPower" style={{ height: 60, display: 'block' }} />
    <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.55)', marginTop: 20, lineHeight: 1.6 }}>
      614 166 6340 · contacto@propower.mx<br/>Chihuahua · Delicias, CHIH
    </div>
    <div style={{ borderTop: '1px solid rgba(255,255,255,0.1)', marginTop: 24, paddingTop: 18, fontSize: 11, color: 'rgba(255,255,255,0.45)' }}>
      © 2026 ProPower · 100% mexicana desde 2018
    </div>
  </footer>
);

// ==================== WhatsApp float ====================
const WhatsappFloat = ({ scale = 1 }) => (
  <a href="https://wa.me/526141666340" target="_blank" rel="noopener" className="pp-whatsapp" style={{
    position: 'fixed', bottom: 24 * scale, right: 24 * scale,
    width: 56 * scale, height: 56 * scale, borderRadius: '50%',
    background: '#25d366', display: 'flex', alignItems: 'center', justifyContent: 'center',
    cursor: 'pointer', textDecoration: 'none', zIndex: 999,
  }}>
    <svg width={28 * scale} height={28 * scale} viewBox="0 0 24 24" fill="#fff">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.693.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
  </a>
);

export {
  Nav, HeroDesktop, HeroMobile, OfertaDesktop,
  NosotrosDesktop, NosotrosMobile,
  ServiciosDesktop, ServiciosMobile,
  GaleriaDesktop, GaleriaMobile,
  ContactoDesktop, ContactoMobile,
  Footer, FooterMobile, WhatsappFloat,
};
