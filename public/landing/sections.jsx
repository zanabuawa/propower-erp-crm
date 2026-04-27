// ProPower — componentes interactivos (CDN, CSS-powered hover, no React state on scroll)
const { useState, useEffect, useRef } = React;

const WHATSAPP = '526141666340';
const PHONE    = '614 166 6340';
const EMAIL    = 'contacto@propower.mx';

const LOGO_RED     = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1 red.png';
const LOGO_H_RED   = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1h red.png';
const LOGO_H_WHITE = '/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1h white.png';

const HERO_IMGS = [
  '/assets/img/Inicio/pexels-1920-1.webp',
  '/assets/img/Inicio/pexels-1920-2.webp',
  '/assets/img/Inicio/pexels-1920-4.webp',
  '/assets/img/Inicio/pexels-1920-5.webp',
];
const INDUSTRIA_IMG  = '/assets/img/Inicio/pexels-sergey-sergeev-2153675005-32845692.jpg';
const MINERIA_IMG    = '/assets/img/Inicio/pexels-hannu-iso-oja-3301403-4946889.jpg';
const INGENIERIA_IMG = '/assets/img/Inicio/pexels-freek-wolsink-508219-34207359.jpg';

const BRAND_LOGOS = ['3M','ABB','Siemens','Eaton','SquareD','General Electric','Southwire','KleinTools','Hubbel','Leviton','Viakon']
  .map(n => `/assets/img/Marcas/${n}.webp`);

const scrollTo = id => {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

// ── Global singleton reveal observer (CSS class, zero React re-renders) ────────
let _revealObs = null;
const initReveal = () => {
  if (_revealObs) return;
  _revealObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('pp-visible');
        _revealObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
};

const useReveal = () => {
  const ref = useRef(null);
  useEffect(() => {
    initReveal();
    const el = ref.current;
    if (el) _revealObs.observe(el);
    return () => { if (el) _revealObs.unobserve(el); };
  }, []);
  return ref;
};

// ── Icons ────────────────────────────────────────────────────────────────────
const IconBolt    = () => <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.5"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>;
const IconPick    = () => <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.5"><path d="M18 2l-7 7M5 5l14 14M10 10l-5 5-3-3 5-5M14 14l5 5 3-3-5-5"/></svg>;
const IconCompass = () => <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="1.5"><circle cx="12" cy="12" r="10"/><path d="M16.24 7.76l-2.12 6.36-6.36 2.12 2.12-6.36 6.36-2.12z"/></svg>;

// ── Nav ──────────────────────────────────────────────────────────────────────
const NAV_LINKS = [
  { l: 'Inicio',    id: 'inicio'   },
  { l: 'Nosotros',  id: 'nosotros' },
  { l: 'Servicios', id: 'servicios'},
  { l: 'Galería',   id: 'galeria'  },
  { l: 'Contacto',  id: 'contacto' },
];

const Nav = ({ mobile = false }) => {
  const [scrolled, setScrolled] = useState(false);
  const [active,   setActive]   = useState('Inicio');
  const [open,     setOpen]     = useState(false);

  useEffect(() => {
    let raf = null;
    const onScroll = () => {
      if (raf) return;
      raf = requestAnimationFrame(() => {
        setScrolled(window.scrollY > 50);
        let cur = 'Inicio';
        for (const { l, id } of NAV_LINKS) {
          const el = document.getElementById(id);
          if (el && el.getBoundingClientRect().top <= 100) cur = l;
        }
        setActive(cur);
        raf = null;
      });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => { window.removeEventListener('scroll', onScroll); if (raf) cancelAnimationFrame(raf); };
  }, []);

  const navBg = scrolled ? 'rgba(10,10,10,0.96)' : '#0a0a0a';

  if (mobile) {
    return (
      <>
        <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', padding:'14px 18px', background: navBg, borderBottom:'1px solid rgba(255,255,255,0.08)', position:'sticky', top:0, zIndex:200, backdropFilter:'blur(10px)', transition:'background 0.3s' }}>
          <img src={LOGO_H_RED} alt="ProPower" style={{ height:52 }} />
          <button onClick={() => setOpen(o => !o)} style={{ background:'none', border:'none', cursor:'pointer', padding:4, display:'flex', flexDirection:'column', gap:5 }}>
            <span className="pp-menu-line" style={{ transform: open ? 'rotate(45deg) translate(5px,5px)' : 'none' }}/>
            <span className="pp-menu-line" style={{ opacity: open ? 0 : 1 }}/>
            <span className="pp-menu-line" style={{ transform: open ? 'rotate(-45deg) translate(5px,-5px)' : 'none' }}/>
          </button>
        </div>
        {open && (
          <div style={{ position:'fixed', inset:0, top:60, background:'#0a0a0a', zIndex:199, padding:'32px 20px', display:'flex', flexDirection:'column' }}>
            {NAV_LINKS.map(({ l, id }) => (
              <button key={l} onClick={() => { scrollTo(id); setOpen(false); }}
                style={{ background:'none', border:'none', borderBottom:'1px solid rgba(255,255,255,0.1)', padding:'20px 0', textAlign:'left', fontFamily:'Archivo, sans-serif', fontSize:28, fontWeight:800, textTransform:'uppercase', color: l === active ? 'var(--pp-red)' : '#fff', cursor:'pointer', letterSpacing:'-0.02em' }}
              >{l}</button>
            ))}
            <a href={`tel:+52${PHONE.replace(/\D/g,'')}`} style={{ marginTop:32, fontFamily:'JetBrains Mono, monospace', fontSize:13, color:'rgba(255,255,255,0.7)', letterSpacing:'0.08em', textDecoration:'none' }}>
              <span style={{ color:'var(--pp-red)' }}>◆</span> {PHONE}
            </a>
          </div>
        )}
      </>
    );
  }

  return (
    <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between', padding:'22px 56px', background: navBg, borderBottom:'1px solid rgba(255,255,255,0.08)', color:'#fff', position:'sticky', top:0, zIndex:200, backdropFilter:'blur(10px)', transition:'background 0.3s, box-shadow 0.3s', boxShadow: scrolled ? '0 2px 20px rgba(0,0,0,0.5)' : 'none' }}>
      <img src={LOGO_H_RED} alt="ProPower" style={{ height:70 }} />
      <div style={{ display:'flex', gap:40 }}>
        {NAV_LINKS.map(({ l, id }) => (
          <button key={l} onClick={() => scrollTo(id)} className="pp-nav-btn"
            style={{ padding:0, color: l === active ? 'var(--pp-red)' : '#fff', fontSize:14, fontWeight:500, letterSpacing:'0.02em', fontFamily:'Figtree, sans-serif', position:'relative' }}
          >
            {l}
            {l === active && <div style={{ position:'absolute', bottom:-24, left:0, right:0, height:2, background:'var(--pp-red)' }}/>}
          </button>
        ))}
      </div>
      <a href={`tel:+52${PHONE.replace(/\D/g,'')}`} style={{ display:'flex', alignItems:'center', gap:10, fontFamily:'JetBrains Mono, monospace', fontSize:12, color:'rgba(255,255,255,0.7)', letterSpacing:'0.08em', textDecoration:'none' }}>
        <span style={{ color:'var(--pp-red)' }}>◆</span>{PHONE}
      </a>
    </div>
  );
};

// ── Hero Desktop ─────────────────────────────────────────────────────────────
const HeroDesktop = () => {
  const [slide, setSlide] = useState(0);
  useEffect(() => {
    const t = setInterval(() => setSlide(s => (s + 1) % HERO_IMGS.length), 5000);
    return () => clearInterval(t);
  }, []);

  return (
    <div id="inicio" style={{ background:'#0a0a0a', color:'#fff', fontFamily:'Figtree, sans-serif' }}>
      <Nav />
      <div style={{ position:'relative', height:720, overflow:'hidden' }}>
        {HERO_IMGS.map((img, i) => (
          <img key={i} src={img} alt="" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover', opacity: i === slide ? 1 : 0, transition:'opacity 1.2s ease', zIndex: i === slide ? 1 : 0 }}/>
        ))}
        <div style={{ position:'absolute', inset:0, zIndex:2, background:'linear-gradient(90deg,rgba(10,10,10,0.94) 0%,rgba(10,10,10,0.75) 45%,rgba(10,10,10,0.3) 80%,rgba(10,10,10,0.55) 100%)' }}/>
        <img src={LOGO_RED} alt="" style={{ position:'absolute', right:-80, top:40, width:560, opacity:0.07, filter:'brightness(0) invert(1)', pointerEvents:'none', zIndex:2 }}/>

        <div style={{ position:'relative', zIndex:3, padding:'64px 56px', maxWidth:860 }}>
          <div style={{ display:'inline-flex', alignItems:'center', gap:10, fontSize:12, letterSpacing:'0.22em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:24 }}>
            <span style={{ width:28, height:1, background:'var(--pp-red)', display:'inline-block' }}/>
            ProPower Electroconstrucciones · desde 2018
          </div>
          <h1 style={{ fontFamily:'Archivo, sans-serif', fontWeight:900, fontSize:96, lineHeight:0.92, letterSpacing:'-0.04em', margin:0, textTransform:'uppercase' }}>
            Soluciones,<br/>calidad y<br/><span style={{ color:'var(--pp-red)' }}>garantía.</span>
          </h1>
          <div style={{ marginTop:36, padding:'16px 22px', borderLeft:'3px solid var(--pp-red)', fontFamily:'Archivo Narrow, sans-serif', fontSize:22, fontWeight:500, letterSpacing:'0.02em', color:'rgba(255,255,255,0.92)', textTransform:'uppercase' }}>
            Más vatios. Menos paros. Más <strong style={{ color:'var(--pp-red)' }}>ProPower</strong>.
          </div>

          <div style={{ display:'flex', gap:0, marginTop:36, border:'1px solid rgba(255,255,255,0.15)' }}>
            {[{n:'13+',l:'Años operando'},{n:'200+',l:'Proyectos entregados'},{n:'2',l:'Sucursales en CHIH'},{n:'100%',l:'Capital mexicano'}].map((s,i) => (
              <div key={s.l} style={{ flex:1, padding:'16px 20px', borderRight: i < 3 ? '1px solid rgba(255,255,255,0.15)' : 'none' }}>
                <div style={{ fontFamily:'Archivo, sans-serif', fontSize:30, fontWeight:800, letterSpacing:'-0.02em' }}>{s.n}</div>
                <div style={{ fontSize:11, color:'rgba(255,255,255,0.55)', textTransform:'uppercase', letterSpacing:'0.12em', marginTop:2 }}>{s.l}</div>
              </div>
            ))}
          </div>

          <div style={{ display:'flex', gap:20, marginTop:32, alignItems:'center' }}>
            <button onClick={() => scrollTo('contacto')} className="pp-btn"
              style={{ padding:'16px 30px', background:'var(--pp-red)', color:'#fff', fontSize:14, fontWeight:700, letterSpacing:'0.04em', textTransform:'uppercase' }}>
              Contáctanos →
            </button>
            <button onClick={() => scrollTo('servicios')} className="pp-nav-btn"
              style={{ fontSize:13, color:'rgba(255,255,255,0.7)', fontFamily:'JetBrains Mono, monospace', letterSpacing:'0.08em', textTransform:'uppercase' }}>
              ↓ Conoce nuestros servicios
            </button>
          </div>
        </div>

        <div style={{ position:'absolute', bottom:28, left:56, right:56, display:'flex', justifyContent:'space-between', alignItems:'center', zIndex:3 }}>
          <div style={{ display:'flex', gap:6 }}>
            {HERO_IMGS.map((_, i) => (
              <button key={i} onClick={() => setSlide(i)} className="pp-dot"
                style={{ width: i === slide ? 36 : 10, height:3, background: i === slide ? 'var(--pp-red)' : 'rgba(255,255,255,0.35)' }}
              />
            ))}
          </div>
          <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, color:'rgba(255,255,255,0.5)', letterSpacing:'0.1em' }}>
            {String(slide+1).padStart(2,'0')} / {String(HERO_IMGS.length).padStart(2,'0')}
          </div>
        </div>
      </div>
    </div>
  );
};

// ── Hero Mobile ───────────────────────────────────────────────────────────────
const HeroMobile = () => {
  const [slide, setSlide] = useState(0);
  useEffect(() => {
    const t = setInterval(() => setSlide(s => (s+1) % HERO_IMGS.length), 5000);
    return () => clearInterval(t);
  }, []);
  return (
    <div id="inicio" style={{ background:'#0a0a0a', color:'#fff' }}>
      <Nav mobile />
      <div style={{ position:'relative', height:580, overflow:'hidden' }}>
        {HERO_IMGS.map((img, i) => (
          <img key={i} src={img} alt="" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover', opacity: i===slide?1:0, transition:'opacity 1.2s', zIndex: i===slide?1:0 }}/>
        ))}
        <div style={{ position:'absolute', inset:0, background:'linear-gradient(180deg,rgba(10,10,10,0.5) 0%,rgba(10,10,10,0.92) 60%)', zIndex:2 }}/>
        <div style={{ position:'absolute', bottom:0, left:0, right:0, padding:20, zIndex:3 }}>
          <div style={{ fontSize:10, letterSpacing:'0.22em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:14 }}>◼ Desde 2018</div>
          <h1 style={{ fontFamily:'Archivo, sans-serif', fontWeight:900, fontSize:44, lineHeight:0.94, letterSpacing:'-0.035em', margin:0, textTransform:'uppercase' }}>
            Soluciones, calidad y <span style={{ color:'var(--pp-red)' }}>garantía.</span>
          </h1>
          <div style={{ marginTop:16, paddingLeft:12, borderLeft:'2px solid var(--pp-red)', fontFamily:'Archivo Narrow, sans-serif', fontSize:14, fontWeight:500, color:'rgba(255,255,255,0.9)', textTransform:'uppercase' }}>
            Más vatios. Menos paros. Más <strong style={{ color:'var(--pp-red)' }}>ProPower</strong>.
          </div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', border:'1px solid rgba(255,255,255,0.15)', marginTop:20 }}>
            {[['13+','años'],['200+','obras'],['2','sedes'],['100%','MX']].map(([n,l],i) => (
              <div key={l} style={{ padding:'8px 6px', borderRight: i<3?'1px solid rgba(255,255,255,0.15)':'none', textAlign:'center' }}>
                <div style={{ fontFamily:'Archivo, sans-serif', fontSize:16, fontWeight:800 }}>{n}</div>
                <div style={{ fontSize:9, color:'rgba(255,255,255,0.55)', textTransform:'uppercase', letterSpacing:'0.1em' }}>{l}</div>
              </div>
            ))}
          </div>
          <button onClick={() => scrollTo('contacto')} className="pp-btn"
            style={{ display:'block', width:'100%', padding:'14px 22px', background:'var(--pp-red)', color:'#fff', fontSize:13, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.04em', marginTop:18, textAlign:'center' }}>
            Contáctanos →
          </button>
        </div>
      </div>
    </div>
  );
};

// ── Oferta ───────────────────────────────────────────────────────────────────
const OfertaDesktop = () => {
  const ref = useReveal();
  const cards = [
    { img:INDUSTRIA_IMG,  t:'Industria', d:'Impulsa tu industria con servicios electromecánicos de alta disponibilidad.', Icon:IconBolt,    tags:['Subestaciones','Tableros','Automatización'] },
    { img:MINERIA_IMG,    t:'Minería',   d:'Energía resistente para operaciones en condiciones extremas.',                Icon:IconPick,    tags:['Alta tensión','Plantas'] },
    { img:INGENIERIA_IMG, t:'Ingeniería',d:'Soluciones a la medida, diseñadas para tu proyecto.',                        Icon:IconCompass, tags:['Proyecto llave','Supervisión'] },
  ];
  return (
    <section ref={ref} id="oferta" className="pp-reveal" style={{ padding:'120px 56px', background:'#fff' }}>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:56 }}>
        <div>
          <div style={{ fontSize:12, letterSpacing:'0.25em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:16 }}>[ 01 ] Conoce nuestra oferta</div>
          <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:56, lineHeight:1.0, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>Tres sectores.<br/>Una sola exigencia.</h2>
        </div>
        <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:12, color:'#78716c', letterSpacing:'0.1em', textTransform:'uppercase' }}>Industria · Minería · Ingeniería</div>
      </div>
      <div style={{ display:'grid', gridTemplateColumns:'1.4fr 1fr 1fr', gap:16 }}>
        {cards.map((c,i) => (
          <div key={c.t} className="pp-card" style={{ position:'relative', height:520, cursor:'pointer' }} onClick={() => scrollTo('contacto')}>
            <img src={c.img} alt={c.t} className="pp-img" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover' }}/>
            <div style={{ position:'absolute', inset:0, background:'linear-gradient(180deg,rgba(10,10,10,0.15) 0%,rgba(10,10,10,0.88) 70%)' }}/>
            <div style={{ position:'absolute', top:24, left:24, right:24, display:'flex', justifyContent:'space-between', alignItems:'flex-start' }}>
              <div className="pp-sector-icon" style={{ width:56, height:56, display:'flex', alignItems:'center', justifyContent:'center' }}>
                <c.Icon/>
              </div>
              <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, letterSpacing:'0.2em', color:'rgba(255,255,255,0.7)', textTransform:'uppercase' }}>0{i+1} / 03</div>
            </div>
            <div style={{ position:'absolute', bottom:0, left:0, right:0, padding:28, color:'#fff' }}>
              <h3 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize: i===0?44:36, margin:0, letterSpacing:'-0.02em', textTransform:'uppercase' }}>{c.t}</h3>
              <p style={{ fontSize:15, color:'rgba(255,255,255,0.85)', marginTop:10, lineHeight:1.5 }}>{c.d}</p>
              <div style={{ display:'flex', gap:8, marginTop:16, flexWrap:'wrap' }}>
                {c.tags.map(t => <span key={t} style={{ fontSize:11, padding:'4px 10px', border:'1px solid rgba(255,255,255,0.35)', textTransform:'uppercase', letterSpacing:'0.08em', fontFamily:'JetBrains Mono, monospace' }}>{t}</span>)}
              </div>
              <div className="pp-sector-cta" style={{ display:'flex', alignItems:'center', gap:10, marginTop:22, fontSize:13, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.04em', color:'var(--pp-red)' }}>
                Más información →
              </div>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
};

// ── Nosotros Desktop ──────────────────────────────────────────────────────────
const NosotrosDesktop = () => {
  const ref = useReveal();
  return (
    <section ref={ref} id="nosotros" className="pp-reveal" style={{ padding:'120px 56px', background:'#0a0a0a', color:'#fff' }}>
      <div style={{ display:'grid', gridTemplateColumns:'1fr 1.2fr', gap:80 }}>
        <div>
          <div style={{ fontSize:12, letterSpacing:'0.25em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:20 }}>[ 02 ] ¿Quiénes somos?</div>
          <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:64, lineHeight:0.98, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>
            100%<br/>mexicana.<br/><span style={{ color:'var(--pp-red)' }}>Siempre a la</span><br/>vanguardia.
          </h2>
          <div style={{ marginTop:40, fontFamily:'Archivo Narrow, sans-serif', fontSize:18, color:'rgba(255,255,255,0.55)', lineHeight:1.6, letterSpacing:'0.02em' }}>Desde 2018 · Chihuahua · México</div>
        </div>
        <div>
          <p style={{ fontSize:19, lineHeight:1.65, color:'rgba(255,255,255,0.85)', margin:0 }}>
            <strong style={{ color:'#fff' }}>ProPower Electroconstrucciones</strong> es una empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales.
          </p>
          <p style={{ fontSize:17, lineHeight:1.65, color:'rgba(255,255,255,0.7)', marginTop:22 }}>
            Nuestro equipo, ambicioso y con un fuerte espíritu de trabajo, se mantiene siempre a la vanguardia, con el objetivo de ofrecer a nuestros clientes seguridad y calidad en cada proyecto.
          </p>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', marginTop:48, border:'1px solid rgba(255,255,255,0.15)' }}>
            {[
              { t:'Misión', d:'Entregar soluciones electromecánicas con garantía y seguridad, superando las expectativas de cada cliente.' },
              { t:'Visión', d:'Ser el contratista de referencia en el norte de México en electroconstrucciones industriales.' },
              { t:'Valores',d:'Compromiso, responsabilidad y honestidad en cada obra y en cada relación.' },
            ].map((m,i) => (
              <div key={m.t} style={{ padding:'24px 22px', borderRight: i<2?'1px solid rgba(255,255,255,0.15)':'none' }}>
                <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, letterSpacing:'0.2em', color:'var(--pp-red)', textTransform:'uppercase', marginBottom:14 }}>0{i+1} · {m.t}</div>
                <p style={{ fontSize:14, lineHeight:1.55, color:'rgba(255,255,255,0.8)', margin:0 }}>{m.d}</p>
              </div>
            ))}
          </div>
          <div style={{ display:'flex', gap:28, marginTop:40, fontFamily:'JetBrains Mono, monospace', fontSize:12, textTransform:'uppercase', letterSpacing:'0.18em' }}>
            <span style={{ color:'var(--pp-red)' }}>✦ Compromiso</span>
            <span style={{ color:'var(--pp-red)' }}>✦ Responsabilidad</span>
            <span style={{ color:'var(--pp-red)' }}>✦ Honestidad</span>
          </div>
        </div>
      </div>
    </section>
  );
};

// ── Servicios Desktop ─────────────────────────────────────────────────────────
const SERVICES = [
  { n:'01', t:'Media y Alta Tensión',        d:'Instalación y mantenimiento de subestaciones, transformadores y líneas de distribución.' },
  { n:'02', t:'Tableros y Centros de Carga', d:'Diseño, armado y puesta en marcha de tableros principales y secundarios.' },
  { n:'03', t:'Canalizaciones Industriales', d:'Charolas, conduit, bus bar y sistemas de canalización para obra pesada.' },
  { n:'04', t:'Sistemas de Puesta a Tierra', d:'Cálculo, diseño e instalación según NOM-022 y normas aplicables.' },
  { n:'05', t:'Iluminación Industrial',      d:'Iluminación de alto rendimiento para naves, patios y áreas operativas.' },
  { n:'06', t:'Automatización y Control',    d:'PLCs, variadores, arrancadores y tableros de control eléctrico.' },
  { n:'07', t:'Mantenimiento Preventivo',    d:'Programas de mantenimiento eléctrico para minimizar paros no planeados.' },
  { n:'08', t:'Proyectos Llave en Mano',     d:'Ingeniería, procura y construcción EPC para proyectos industriales completos.' },
];

const ServiciosDesktop = () => {
  const ref = useReveal();
  return (
    <section ref={ref} id="servicios" className="pp-reveal" style={{ padding:'120px 56px', background:'#fff' }}>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:64 }}>
        <div>
          <div style={{ fontSize:12, letterSpacing:'0.25em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:16 }}>[ 03 ] Servicios</div>
          <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:56, lineHeight:1.0, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>Ocho líneas de<br/>servicio técnico.</h2>
        </div>
        <p style={{ fontSize:15, color:'#57534e', maxWidth:320, lineHeight:1.6, margin:0 }}>Desde la planeación eléctrica hasta la puesta en marcha, cubrimos cada etapa de tu obra con personal certificado.</p>
      </div>
      <div style={{ border:'1px solid #0a0a0a' }}>
        {SERVICES.map((s,i) => (
          <div key={s.n} className="pp-svc"
            style={{ display:'grid', gridTemplateColumns:'80px 1fr 2fr 120px', gap:24, alignItems:'center', padding:'26px 28px', borderBottom: i<SERVICES.length-1?'1px solid #0a0a0a':'none', background:'#fff' }}
            onClick={() => scrollTo('contacto')}>
            <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:14, color:'var(--pp-red)', letterSpacing:'0.1em' }}>{s.n} /</div>
            <div className="pp-svc-t" style={{ fontFamily:'Archivo, sans-serif', fontSize:26, fontWeight:700, letterSpacing:'-0.02em', textTransform:'uppercase', color:'#0a0a0a' }}>{s.t}</div>
            <div className="pp-svc-d" style={{ fontSize:15, color:'#44403c', lineHeight:1.5 }}>{s.d}</div>
            <div className="pp-svc-a" style={{ fontFamily:'JetBrains Mono, monospace', fontSize:12, color:'#0a0a0a', textTransform:'uppercase', letterSpacing:'0.1em', textAlign:'right' }}>Saber más →</div>
          </div>
        ))}
      </div>
      <div style={{ marginTop:80 }}>
        <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, textTransform:'uppercase', letterSpacing:'0.2em', color:'#78716c', marginBottom:24, textAlign:'center' }}>Trabajamos con las mejores marcas del sector</div>
        <div style={{ display:'grid', gridTemplateColumns:'repeat(6,1fr)', border:'1px solid #e7e5e4' }}>
          {BRAND_LOGOS.map((src,i) => (
            <div key={src} style={{ background:'#fff', height:110, display:'flex', alignItems:'center', justifyContent:'center', padding:22, borderRight:(i+1)%6!==0?'1px solid #e7e5e4':'none', borderTop:i>=6?'1px solid #e7e5e4':'none' }}>
              <img src={src} alt="" style={{ maxWidth:'100%', maxHeight:'100%', objectFit:'contain', filter:'grayscale(1)', opacity:0.65 }}/>
            </div>
          ))}
          <div style={{ background:'#0a0a0a', color:'#fff', height:110, display:'flex', alignItems:'center', justifyContent:'center', fontFamily:'Archivo, sans-serif', fontSize:16, fontWeight:700, borderTop:'1px solid #e7e5e4' }}>+20 más</div>
        </div>
      </div>
    </section>
  );
};

// ── Galería Desktop ───────────────────────────────────────────────────────────
const PROJECTS = [
  { img:HERO_IMGS[0],   t:'Subestación industrial',      loc:'Chihuahua, CHIH',       year:'2024', cat:'Industria'  },
  { img:INDUSTRIA_IMG,  t:'Nave de manufactura',          loc:'Delicias, CHIH',        year:'2024', cat:'Industria'  },
  { img:MINERIA_IMG,    t:'Sistema eléctrico de mina',    loc:'Sierra, CHIH',          year:'2023', cat:'Minería'    },
  { img:HERO_IMGS[2],   t:'Tableros de distribución',     loc:'Chihuahua, CHIH',       year:'2023', cat:'Ingeniería' },
  { img:HERO_IMGS[1],   t:'Iluminación perimetral',       loc:'Parque Industrial',     year:'2023', cat:'Industria'  },
  { img:INGENIERIA_IMG, t:'Ingeniería de detalle',         loc:'Proyecto confidencial', year:'2022', cat:'Ingeniería' },
  { img:HERO_IMGS[3],   t:'Canalización de alta tensión', loc:'Chihuahua, CHIH',       year:'2022', cat:'Industria'  },
];

const FILTERS = ['Todos','Industria','Minería','Ingeniería'];

const GaleriaDesktop = () => {
  const ref = useReveal();
  const [filter, setFilter] = useState('Todos');
  const shown = filter === 'Todos' ? PROJECTS : PROJECTS.filter(p => p.cat === filter);
  const feat = shown[0] || PROJECTS[0];
  const rest = shown.length > 1 ? shown.slice(1,5) : PROJECTS.slice(1,5);

  const thumb = (p, key, extraStyle={}) => (
    <div key={key} className="pp-card" style={{ position:'relative', ...extraStyle }}>
      <img src={p.img} alt="" className="pp-img" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover' }}/>
      <div style={{ position:'absolute', inset:0, background:'linear-gradient(180deg,transparent 40%,rgba(10,10,10,0.85) 100%)' }}/>
      <div style={{ position:'absolute', bottom:16, left:16, right:16, color:'#fff' }}>
        <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:9, letterSpacing:'0.18em', textTransform:'uppercase', color:'rgba(255,255,255,0.7)', marginBottom:4 }}>{p.cat} · {p.year}</div>
        <h4 style={{ fontFamily:'Archivo, sans-serif', fontWeight:700, fontSize:18, margin:0, letterSpacing:'-0.01em', textTransform:'uppercase', lineHeight:1.1 }}>{p.t}</h4>
      </div>
    </div>
  );

  return (
    <section ref={ref} id="galeria" className="pp-reveal" style={{ padding:'120px 56px', background:'#fafaf9' }}>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:56 }}>
        <div>
          <div style={{ fontSize:12, letterSpacing:'0.25em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:16 }}>[ 04 ] Galería</div>
          <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:56, lineHeight:1.0, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>Obras que<br/>hablan por sí solas.</h2>
        </div>
        <div style={{ display:'flex', gap:8 }}>
          {FILTERS.map(f => (
            <button key={f} onClick={() => setFilter(f)} className="pp-btn-filter"
              style={{ padding:'8px 14px', border:'1px solid #0a0a0a', background: filter===f?'#0a0a0a':'transparent', color: filter===f?'#fff':'#0a0a0a', fontSize:11, fontFamily:'JetBrains Mono, monospace', textTransform:'uppercase', letterSpacing:'0.12em' }}
            >{f}</button>
          ))}
        </div>
      </div>

      <div style={{ display:'grid', gridTemplateColumns:'2fr 1fr 1fr', gridTemplateRows:'repeat(3,240px)', gap:12 }}>
        <div className="pp-card" style={{ gridColumn:'1/2', gridRow:'1/3', position:'relative' }}>
          <img src={feat.img} alt="" className="pp-img" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover' }}/>
          <div style={{ position:'absolute', inset:0, background:'linear-gradient(180deg,transparent 40%,rgba(10,10,10,0.88) 100%)' }}/>
          <div style={{ position:'absolute', top:20, left:20, fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', textTransform:'uppercase', color:'#fff', padding:'5px 10px', background:'var(--pp-red)' }}>Proyecto destacado</div>
          <div style={{ position:'absolute', bottom:28, left:28, right:28, color:'#fff' }}>
            <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, letterSpacing:'0.18em', textTransform:'uppercase', color:'rgba(255,255,255,0.7)', marginBottom:8 }}>{feat.cat} · {feat.year}</div>
            <h3 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:36, margin:0, letterSpacing:'-0.02em', textTransform:'uppercase' }}>{feat.t}</h3>
            <div style={{ fontSize:14, color:'rgba(255,255,255,0.85)', marginTop:6 }}>{feat.loc}</div>
          </div>
        </div>

        {rest.map((p, i) => thumb(p, `r${i}`))}

        <div className="pp-card" style={{ gridColumn:'1/3', position:'relative' }}>
          <img src={PROJECTS[5].img} alt="" className="pp-img" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover' }}/>
          <div style={{ position:'absolute', inset:0, background:'linear-gradient(90deg,rgba(10,10,10,0.85) 0%,rgba(10,10,10,0.2) 60%)' }}/>
          <div style={{ position:'absolute', bottom:20, left:24, color:'#fff' }}>
            <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.18em', textTransform:'uppercase', color:'rgba(255,255,255,0.7)', marginBottom:4 }}>{PROJECTS[5].cat} · {PROJECTS[5].year}</div>
            <h4 style={{ fontFamily:'Archivo, sans-serif', fontWeight:700, fontSize:22, margin:0, letterSpacing:'-0.01em', textTransform:'uppercase' }}>{PROJECTS[5].t}</h4>
          </div>
        </div>
        {thumb(PROJECTS[6], 'b2')}
      </div>

      <div style={{ display:'flex', justifyContent:'center', marginTop:48 }}>
        <button onClick={() => scrollTo('contacto')} className="pp-btn"
          style={{ padding:'16px 36px', background:'#0a0a0a', color:'#fff', fontSize:13, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.08em', fontFamily:'JetBrains Mono, monospace' }}>
          Ver galería completa →
        </button>
      </div>
    </section>
  );
};

// ── Contacto Desktop ──────────────────────────────────────────────────────────
const ContactoDesktop = () => {
  const ref = useReveal();
  const [sector, setSector] = useState('Industria');
  const [form, setForm]     = useState({ nombre:'', empresa:'', correo:'', telefono:'', mensaje:'' });
  const [sent, setSent]     = useState(false);

  const ch = e => setForm(f => ({ ...f, [e.target.name]: e.target.value }));

  const submit = e => {
    e.preventDefault();
    const msg = encodeURIComponent(
      `*Contacto desde ProPower.mx*\n\nNombre: ${form.nombre}\nEmpresa: ${form.empresa}\nCorreo: ${form.correo}\nTeléfono: ${form.telefono}\nSector: ${sector}\nMensaje: ${form.mensaje}`
    );
    window.open(`https://wa.me/${WHATSAPP}?text=${msg}`, '_blank');
    setSent(true);
    setTimeout(() => setSent(false), 5000);
  };

  const lbl = { fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', display:'block', paddingTop:4 };

  return (
    <section ref={ref} id="contacto" className="pp-reveal" style={{ background:'#0a0a0a', color:'#fff' }}>
      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr' }}>
        <div style={{ padding:'100px 56px', borderRight:'1px solid rgba(255,255,255,0.1)' }}>
          <div style={{ fontSize:12, letterSpacing:'0.25em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:20 }}>[ 05 ] Contacto</div>
          <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:52, lineHeight:0.98, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>
            Cuéntanos tu<br/><span style={{ color:'var(--pp-red)' }}>proyecto.</span>
          </h2>
          <p style={{ fontSize:16, color:'rgba(255,255,255,0.65)', marginTop:18, marginBottom:40, lineHeight:1.6, maxWidth:440 }}>
            Escríbenos y un asesor técnico se pondrá en contacto contigo en menos de 24 horas hábiles.
          </p>

          {sent ? (
            <div style={{ padding:24, border:'1px solid var(--pp-red)', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', fontSize:13, letterSpacing:'0.1em', textTransform:'uppercase' }}>
              ✓ Redirigiendo a WhatsApp…
            </div>
          ) : (
            <form onSubmit={submit}>
              {[{name:'nombre',l:'Nombre',p:'Tu nombre completo',type:'text'},{name:'empresa',l:'Empresa',p:'Nombre de tu empresa',type:'text'},{name:'correo',l:'Correo',p:'correo@empresa.com',type:'email'},{name:'telefono',l:'Teléfono',p:'+52 614 000 0000',type:'tel'}].map(f => (
                <div key={f.name}>
                  <label style={lbl}>{f.l}</label>
                  <input name={f.name} type={f.type} placeholder={f.p} value={form[f.name]} onChange={ch} required={f.name!=='telefono'} className="pp-input"/>
                </div>
              ))}
              <div style={{ paddingTop:18, borderBottom:'1px solid rgba(255,255,255,0.15)', paddingBottom:14 }}>
                <label style={lbl}>Sector / Tipo de proyecto</label>
                <div style={{ display:'flex', gap:8, marginTop:10, flexWrap:'wrap' }}>
                  {['Industria','Minería','Ingeniería','Mantenimiento','Otro'].map(s => (
                    <button key={s} type="button" onClick={() => setSector(s)}
                      style={{ padding:'6px 12px', border:`1px solid ${sector===s?'var(--pp-red)':'rgba(255,255,255,0.25)'}`, fontSize:12, textTransform:'uppercase', letterSpacing:'0.06em', fontFamily:'JetBrains Mono, monospace', background: sector===s?'var(--pp-red)':'transparent', color:'#fff', cursor:'pointer', transition:'all 0.2s' }}
                    >{s}</button>
                  ))}
                </div>
              </div>
              <div>
                <label style={lbl}>Mensaje</label>
                <textarea name="mensaje" placeholder="Cuéntanos sobre tu obra, ubicación y fechas tentativas…" value={form.mensaje} onChange={ch} rows={3} className="pp-input" style={{ resize:'none', paddingTop:12 }}/>
              </div>
              <button type="submit" className="pp-btn"
                style={{ display:'inline-flex', padding:'18px 36px', background:'var(--pp-red)', color:'#fff', fontSize:14, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.06em', marginTop:24 }}>
                Enviar mensaje →
              </button>
            </form>
          )}
        </div>

        <div style={{ padding:'100px 56px' }}>
          <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', marginBottom:20 }}>Contacto directo</div>
          <div style={{ display:'grid', gap:20, marginBottom:48 }}>
            {[
              { k:'Teléfono', v:PHONE,                  href:`tel:+52${PHONE.replace(/\D/g,'')}` },
              { k:'Correo',   v:EMAIL,                   href:`mailto:${EMAIL}` },
              { k:'Horario',  v:'Lun–Vie · 9:00–18:00', href:null },
            ].map(x => (
              <div key={x.k} style={{ display:'flex', justifyContent:'space-between', borderBottom:'1px solid rgba(255,255,255,0.1)', paddingBottom:14 }}>
                <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, letterSpacing:'0.18em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)' }}>{x.k}</div>
                {x.href
                  ? <a href={x.href} className="pp-link" style={{ fontFamily:'Archivo, sans-serif', fontSize:22, fontWeight:700, color:'#fff', textDecoration:'none' }}>{x.v}</a>
                  : <div style={{ fontFamily:'Archivo, sans-serif', fontSize:22, fontWeight:700 }}>{x.v}</div>
                }
              </div>
            ))}
          </div>
          <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', marginBottom:20 }}>Sucursales</div>
          <div style={{ display:'grid', gap:20 }}>
            {[
              { t:'Chihuahua', sub:'Oficina matriz · CHIH', emb:'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6537.871959268932!2d-106.12901740537757!3d28.70382956590884!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86ea438974075dc5%3A0xb8c2426f69011cbb!2sProPower%20Electroconstrucciones!5e0!3m2!1ses-419!2smx!4v1763958506976!5m2!1ses-419!2smx' },
              { t:'Delicias',   sub:'Sucursal · CHIH',       emb:'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8259.094005874922!2d-105.45656117616318!3d28.183644407838823!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86eb159b6256c213%3A0x3aa93cc16e2a0b9!2sPropower%20Electroconstrucciones!5e0!3m2!1ses-419!2smx!4v1763958405258!5m2!1ses-419!2smx' },
            ].map(s => (
              <div key={s.t} style={{ border:'1px solid rgba(255,255,255,0.15)' }}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'14px 18px', borderBottom:'1px solid rgba(255,255,255,0.15)' }}>
                  <div>
                    <div style={{ fontFamily:'Archivo, sans-serif', fontSize:18, fontWeight:700, textTransform:'uppercase' }}>{s.t}</div>
                    <div style={{ fontSize:12, color:'rgba(255,255,255,0.55)', marginTop:2 }}>{s.sub}</div>
                  </div>
                  <a href={`https://maps.google.com/?q=ProPower+Electroconstrucciones+${s.t}`} target="_blank" rel="noreferrer" className="pp-link"
                    style={{ color:'var(--pp-red)', fontSize:12, fontFamily:'JetBrains Mono, monospace', textTransform:'uppercase', letterSpacing:'0.1em', textDecoration:'none' }}>Ver ruta →</a>
                </div>
                <iframe src={s.emb} width="100%" height="200" style={{ border:0, display:'block', filter:'grayscale(1) invert(0.92) contrast(0.95)' }} loading="lazy" referrerPolicy="no-referrer-when-downgrade"/>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

// ── Footer ────────────────────────────────────────────────────────────────────
const Footer = () => (
  <footer style={{ background:'#000', color:'#fff', padding:'80px 56px 32px' }}>
    <div style={{ borderBottom:'1px solid rgba(255,255,255,0.15)', paddingBottom:40, marginBottom:40 }}>
      <img src={LOGO_H_WHITE} alt="ProPower Electroconstrucciones" style={{ height:240, marginBottom:16 }} />
      <div style={{ fontFamily:'Archivo Narrow, sans-serif', fontSize:16, textTransform:'uppercase', letterSpacing:'0.08em', color:'rgba(255,255,255,0.65)' }}>
        Más vatios · Menos paros · Más ProPower
      </div>
    </div>
    <div style={{ display:'grid', gridTemplateColumns:'2fr 1fr 1fr 1fr', gap:60, paddingBottom:48 }}>
      <div style={{ fontSize:14, color:'rgba(255,255,255,0.6)', lineHeight:1.6, maxWidth:320 }}>
        Empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales desde 2018.
      </div>
      {[
        { t:'Navegación', l:[{txt:'Inicio',fn:()=>scrollTo('inicio')},{txt:'Nosotros',fn:()=>scrollTo('nosotros')},{txt:'Servicios',fn:()=>scrollTo('servicios')},{txt:'Galería',fn:()=>scrollTo('galeria')},{txt:'Contacto',fn:()=>scrollTo('contacto')}] },
        { t:'Sectores',   l:[{txt:'Industria',fn:()=>scrollTo('oferta')},{txt:'Minería',fn:()=>scrollTo('oferta')},{txt:'Ingeniería',fn:()=>scrollTo('oferta')}] },
        { t:'Contacto',   l:[{txt:PHONE,fn:()=>window.location.href=`tel:+52${PHONE.replace(/\D/g,'')}`},{txt:EMAIL,fn:()=>window.location.href=`mailto:${EMAIL}`},{txt:'Chihuahua, CHIH',fn:null},{txt:'Delicias, CHIH',fn:null}] },
      ].map(col => (
        <div key={col.t}>
          <div style={{ fontSize:11, fontFamily:'JetBrains Mono, monospace', textTransform:'uppercase', letterSpacing:'0.2em', color:'rgba(255,255,255,0.5)', marginBottom:20 }}>{col.t}</div>
          {col.l.map(x => x.fn
            ? <button key={x.txt} onClick={x.fn} className="pp-footer-btn">{x.txt}</button>
            : <div key={x.txt} style={{ fontSize:14, color:'rgba(255,255,255,0.85)', marginBottom:10 }}>{x.txt}</div>
          )}
        </div>
      ))}
    </div>
    <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', borderTop:'1px solid rgba(255,255,255,0.1)', paddingTop:24, fontSize:12, color:'rgba(255,255,255,0.45)' }}>
      <div>© 2026 ProPower Electroconstrucciones — Todos los derechos reservados.</div>
      <div style={{ display:'flex', gap:20 }}>
        <a href={`https://wa.me/${WHATSAPP}`} target="_blank" rel="noreferrer" className="pp-link" style={{ color:'rgba(255,255,255,0.45)', textDecoration:'none' }}>WhatsApp</a>
        <span className="pp-link" style={{ cursor:'pointer', color:'rgba(255,255,255,0.45)' }}>Facebook</span>
        <span className="pp-link" style={{ cursor:'pointer', color:'rgba(255,255,255,0.45)' }}>Instagram</span>
      </div>
    </div>
  </footer>
);

// ── Mobile sections ───────────────────────────────────────────────────────────
const NosotrosMobile = () => {
  const ref = useReveal();
  return (
    <section ref={ref} id="nosotros" className="pp-reveal" style={{ padding:'56px 20px', background:'#0a0a0a', color:'#fff' }}>
      <div style={{ fontSize:10, letterSpacing:'0.22em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:14 }}>[ 02 ] ¿Quiénes somos?</div>
      <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:34, lineHeight:0.98, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>100% mexicana.<br/><span style={{ color:'var(--pp-red)' }}>Siempre a la vanguardia.</span></h2>
      <p style={{ fontSize:15, lineHeight:1.6, color:'rgba(255,255,255,0.8)', marginTop:20 }}><strong style={{ color:'#fff' }}>ProPower Electroconstrucciones</strong> es una empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales.</p>
      <div style={{ marginTop:24, border:'1px solid rgba(255,255,255,0.15)' }}>
        {[{t:'Misión',d:'Entregar soluciones con garantía y seguridad.'},{t:'Visión',d:'Ser el contratista de referencia en el norte de México.'},{t:'Valores',d:'Compromiso, responsabilidad y honestidad.'}].map((m,i)=>(
          <div key={m.t} style={{ padding:'16px 14px', borderBottom: i<2?'1px solid rgba(255,255,255,0.15)':'none' }}>
            <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', color:'var(--pp-red)', textTransform:'uppercase', marginBottom:6 }}>0{i+1} · {m.t}</div>
            <p style={{ fontSize:13, lineHeight:1.5, color:'rgba(255,255,255,0.8)', margin:0 }}>{m.d}</p>
          </div>
        ))}
      </div>
    </section>
  );
};

const ServiciosMobile = () => {
  const ref = useReveal();
  return (
    <section ref={ref} id="servicios" className="pp-reveal" style={{ padding:'56px 20px', background:'#fff' }}>
      <div style={{ fontSize:10, letterSpacing:'0.22em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:12 }}>[ 03 ] Servicios</div>
      <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:30, lineHeight:1.0, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>Ocho líneas de servicio técnico.</h2>
      <div style={{ border:'1px solid #0a0a0a', marginTop:24 }}>
        {SERVICES.map((s,i) => (
          <div key={s.n} style={{ padding:'14px', borderBottom: i<SERVICES.length-1?'1px solid #0a0a0a':'none' }}>
            <div style={{ display:'flex', justifyContent:'space-between' }}>
              <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:11, color:'var(--pp-red)' }}>{s.n} /</div>
              <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10 }}>→</div>
            </div>
            <div style={{ fontFamily:'Archivo, sans-serif', fontSize:18, fontWeight:700, textTransform:'uppercase', marginTop:4, letterSpacing:'-0.01em' }}>{s.t}</div>
            <div style={{ fontSize:12, color:'#44403c', marginTop:4, lineHeight:1.5 }}>{s.d}</div>
          </div>
        ))}
      </div>
    </section>
  );
};

const GaleriaMobile = () => {
  const ref = useReveal();
  return (
    <section ref={ref} id="galeria" className="pp-reveal" style={{ padding:'56px 20px', background:'#fafaf9' }}>
      <div style={{ fontSize:10, letterSpacing:'0.22em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:12 }}>[ 04 ] Galería</div>
      <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:30, lineHeight:1.0, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>Obras que hablan por sí solas.</h2>
      <div style={{ display:'grid', gap:10, marginTop:24 }}>
        {PROJECTS.slice(0,4).map((p,i) => (
          <div key={p.t} className="pp-card" style={{ position:'relative', height: i===0?240:160 }}>
            <img src={p.img} alt="" className="pp-img" style={{ position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover' }}/>
            <div style={{ position:'absolute', inset:0, background:'linear-gradient(180deg,transparent 40%,rgba(10,10,10,0.9) 100%)' }}/>
            <div style={{ position:'absolute', bottom:12, left:14, right:14, color:'#fff' }}>
              <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:9, letterSpacing:'0.18em', textTransform:'uppercase', color:'rgba(255,255,255,0.7)', marginBottom:4 }}>{p.cat} · {p.year}</div>
              <h4 style={{ fontFamily:'Archivo, sans-serif', fontWeight:700, fontSize: i===0?22:15, margin:0, textTransform:'uppercase' }}>{p.t}</h4>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
};

const ContactoMobile = () => {
  const ref = useReveal();
  const [sector, setSector] = useState('Industria');
  const [form, setForm] = useState({ nombre:'', correo:'', telefono:'', mensaje:'' });
  const ch = e => setForm(f => ({ ...f, [e.target.name]: e.target.value }));
  const submit = e => {
    e.preventDefault();
    const msg = encodeURIComponent(`*Contacto ProPower*\nNombre: ${form.nombre}\nCorreo: ${form.correo}\nTeléfono: ${form.telefono}\nSector: ${sector}\nMensaje: ${form.mensaje}`);
    window.open(`https://wa.me/${WHATSAPP}?text=${msg}`, '_blank');
  };
  return (
    <section ref={ref} id="contacto" className="pp-reveal" style={{ background:'#0a0a0a', color:'#fff', padding:'56px 20px' }}>
      <div style={{ fontSize:10, letterSpacing:'0.22em', textTransform:'uppercase', color:'var(--pp-red)', fontFamily:'JetBrains Mono, monospace', marginBottom:12 }}>[ 05 ] Contacto</div>
      <h2 style={{ fontFamily:'Archivo, sans-serif', fontWeight:800, fontSize:32, lineHeight:0.98, letterSpacing:'-0.03em', margin:0, textTransform:'uppercase' }}>Cuéntanos tu <span style={{ color:'var(--pp-red)' }}>proyecto.</span></h2>
      <form onSubmit={submit} style={{ marginTop:20 }}>
        {[{name:'nombre',l:'Nombre',p:'Tu nombre',type:'text'},{name:'correo',l:'Correo',p:'correo@empresa.com',type:'email'},{name:'telefono',l:'Teléfono',p:'+52 614 000 0000',type:'tel'}].map(f=>(
          <div key={f.name}>
            <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', paddingTop:14 }}>{f.l}</div>
            <input name={f.name} type={f.type} placeholder={f.p} value={form[f.name]} onChange={ch} required={f.name!=='telefono'} className="pp-input-m"/>
          </div>
        ))}
        <div style={{ paddingTop:14, paddingBottom:14, borderBottom:'1px solid rgba(255,255,255,0.15)' }}>
          <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', marginBottom:8 }}>Sector</div>
          <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
            {['Industria','Minería','Ingeniería','Otro'].map(s=>(
              <button key={s} type="button" onClick={()=>setSector(s)} style={{ padding:'5px 10px', border:`1px solid ${sector===s?'var(--pp-red)':'rgba(255,255,255,0.25)'}`, fontSize:11, fontFamily:'JetBrains Mono, monospace', textTransform:'uppercase', background:sector===s?'var(--pp-red)':'transparent', color:'#fff', cursor:'pointer' }}>{s}</button>
            ))}
          </div>
        </div>
        <div>
          <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', paddingTop:14 }}>Mensaje</div>
          <textarea name="mensaje" placeholder="Cuéntanos sobre tu proyecto…" value={form.mensaje} onChange={ch} rows={3} className="pp-input-m" style={{ resize:'none', paddingTop:10 }}/>
        </div>
        <button type="submit" className="pp-btn"
          style={{ display:'block', width:'100%', padding:'14px 24px', background:'var(--pp-red)', color:'#fff', fontSize:13, fontWeight:700, textTransform:'uppercase', letterSpacing:'0.06em', marginTop:20, textAlign:'center' }}>
          Enviar por WhatsApp →
        </button>
      </form>
      <div style={{ marginTop:32, paddingTop:24, borderTop:'1px solid rgba(255,255,255,0.1)' }}>
        <div style={{ fontFamily:'JetBrains Mono, monospace', fontSize:10, letterSpacing:'0.2em', textTransform:'uppercase', color:'rgba(255,255,255,0.5)', marginBottom:14 }}>Contacto directo</div>
        <a href={`tel:+52${PHONE.replace(/\D/g,'')}`} style={{ display:'block', fontFamily:'Archivo, sans-serif', fontSize:22, fontWeight:700, color:'#fff', textDecoration:'none' }}>{PHONE}</a>
        <a href={`mailto:${EMAIL}`} style={{ fontSize:14, color:'rgba(255,255,255,0.7)', marginTop:4, display:'block', textDecoration:'none' }}>{EMAIL}</a>
      </div>
    </section>
  );
};

const FooterMobile = () => (
  <footer style={{ background:'#000', color:'#fff', padding:'40px 20px 24px' }}>
    <div style={{ fontFamily:'Archivo, sans-serif', fontSize:60, lineHeight:0.85, fontWeight:900, letterSpacing:'-0.05em', textTransform:'uppercase' }}>
      Pro<span style={{ color:'var(--pp-red)' }}>Power.</span>
    </div>
    <div style={{ fontSize:11, color:'rgba(255,255,255,0.6)', marginTop:10, textTransform:'uppercase', letterSpacing:'0.08em', fontFamily:'Archivo Narrow, sans-serif' }}>Más vatios · Menos paros · Más ProPower</div>
    <div style={{ fontSize:12, color:'rgba(255,255,255,0.55)', marginTop:20, lineHeight:1.6 }}>
      <a href={`tel:+52${PHONE.replace(/\D/g,'')}`} style={{ color:'rgba(255,255,255,0.55)', textDecoration:'none' }}>{PHONE}</a> · <a href={`mailto:${EMAIL}`} style={{ color:'rgba(255,255,255,0.55)', textDecoration:'none' }}>{EMAIL}</a><br/>Chihuahua · Delicias, CHIH
    </div>
    <div style={{ borderTop:'1px solid rgba(255,255,255,0.1)', marginTop:24, paddingTop:18, fontSize:11, color:'rgba(255,255,255,0.45)' }}>© 2026 ProPower · 100% mexicana desde 2018</div>
  </footer>
);

// ── WhatsApp flotante ─────────────────────────────────────────────────────────
const WhatsappFloat = () => (
  <a href={`https://wa.me/${WHATSAPP}`} target="_blank" rel="noreferrer" className="pp-wa"
    style={{ position:'fixed', bottom:24, right:24, width:56, height:56, borderRadius:'50%', background:'#25d366', display:'flex', alignItems:'center', justifyContent:'center', boxShadow:'0 8px 24px rgba(37,211,102,0.5)', zIndex:1000, textDecoration:'none' }}
  >
    <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.693.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
  </a>
);

// ── Splash Screen ─────────────────────────────────────────────────────────────
const SplashScreen = () => {
  const [fading, setFading] = useState(false);
  const [gone,   setGone]   = useState(false);

  useEffect(() => {
    const t1 = setTimeout(() => setFading(true), 1800);
    const t2 = setTimeout(() => setGone(true),   2500);
    return () => { clearTimeout(t1); clearTimeout(t2); };
  }, []);

  if (gone) return null;

  return (
    <div style={{ position:'fixed', inset:0, background:'#fff', zIndex:9999, display:'flex', alignItems:'center', justifyContent:'center', opacity: fading ? 0 : 1, transition:'opacity 0.7s ease', pointerEvents: fading ? 'none' : 'auto' }}>
      <img src={LOGO_RED} alt="ProPower" className="pp-logo-pulse" style={{ width:140 }}/>
    </div>
  );
};

Object.assign(window, {
  LOGO_RED, LOGO_H_RED, LOGO_H_WHITE, HERO_IMGS, BRAND_LOGOS,
  SplashScreen,
  Nav, HeroDesktop, HeroMobile, OfertaDesktop,
  NosotrosDesktop, NosotrosMobile,
  ServiciosDesktop, ServiciosMobile,
  GaleriaDesktop, GaleriaMobile,
  ContactoDesktop, ContactoMobile,
  Footer, FooterMobile, WhatsappFloat,
});
