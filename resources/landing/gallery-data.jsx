const _GD = (typeof window !== 'undefined' && window.__GALLERY_DATA__ && window.__GALLERY_DATA__.categories?.length)
  ? window.__GALLERY_DATA__
  : null;

const r = (n) => Array.from({ length: n }, (_, i) => i + 1);

const STATIC_CATEGORIES = [
  {
    id: 'baja-tension',
    title: 'Baja Tensión',
    short: 'Baja Tensión',
    sector: 'Industria',
    desc: 'Instalaciones eléctricas en baja tensión para naves industriales y comerciales.',
    images: r(42).map(n => `/assets/img/Galeria/Baja-Tension/${n}.webp`),
  },
  {
    id: 'media-tension',
    title: 'Media Tensión',
    short: 'Media Tensión',
    sector: 'Industria',
    desc: 'Subestaciones, líneas y acometidas de media tensión.',
    images: r(54).map(n => `/assets/img/Galeria/Media-Tension/${n}.webp`),
  },
  {
    id: 'pruebas-electricas',
    title: 'Pruebas Eléctricas',
    short: 'Pruebas',
    sector: 'Mantenimiento',
    desc: 'Pruebas a equipo eléctrico: aislamiento, rigidez, continuidad y puesta a tierra.',
    images: r(5).map(n => `/assets/img/Galeria/Pruebas Electricas/${n}.webp`),
  },
  {
    id: 'habilitacion',
    title: 'Habilitación Eléctrica',
    short: 'Habilitación',
    sector: 'Industria',
    desc: 'Habilitación de instalaciones eléctricas para nuevos espacios industriales.',
    images: r(4).map(n => `/assets/img/Galeria/Habilitacion electrica/${n}.webp`),
  },
  {
    id: 'tableros',
    title: 'Mejora de Tableros Eléctricos',
    short: 'Tableros',
    sector: 'Mantenimiento',
    desc: 'Modernización y mejora de tableros existentes para cumplir con norma actual.',
    images: r(7).map(n => `/assets/img/Galeria/Mejora de tableros electricos existentes/${n}.webp`),
  },
  {
    id: 'control',
    title: 'Actualizaciones de Control',
    short: 'Control',
    sector: 'Ingeniería',
    desc: 'Actualizaciones de sistemas de control para plantas energéticas.',
    images: r(6).map(n => `/assets/img/Galeria/Actualizaciones de control para planta energetica/${n}.webp`),
  },
  {
    id: 'rodillo',
    title: 'Reparación de Rodillo de Roladora',
    short: 'Roladora',
    sector: 'Industria',
    desc: 'Desmontaje y reparación de rodillo de roladora industrial.',
    images: r(5).map(n => `/assets/img/Galeria/Desmontaje y reparacionde de rodillo de roladora/${n}.webp`),
  },
  {
    id: 'molino',
    title: 'Laminado de Estructura de Molino',
    short: 'Molino',
    sector: 'Industria',
    desc: 'Trabajos de laminado en estructura de molino industrial.',
    images: r(4).map(n => `/assets/img/Galeria/Laminado de estructura de molino/${n}.webp`),
  },
  {
    id: 'iluminacion',
    title: 'Iluminación LED y Láminas Translúcidas',
    short: 'Iluminación',
    sector: 'Mantenimiento',
    desc: 'Reemplazo de láminas translúcidas y actualización de iluminación LED industrial.',
    images: r(19).map(n => `/assets/img/Galeria/Remplazo de laminas translucidas y actualizacion de iluminacion led/${n}.webp`),
  },
  {
    id: 'venta',
    title: 'Venta de Equipo y Material',
    short: 'Equipo',
    sector: 'Comercial',
    desc: 'Venta y distribución de equipo y material eléctrico industrial.',
    images: r(16).map(n => `/assets/img/Galeria/Venta de equipo y material electrico/${n}.webp`),
  },
];

const PROJECT_CATEGORIES = _GD ? _GD.categories : STATIC_CATEGORIES;

const ALL_GALLERY_IMAGES = (() => {
  const out = [];
  const max = Math.max(...PROJECT_CATEGORIES.map(c => c.images.length));
  for (let i = 0; i < max; i++) {
    PROJECT_CATEGORIES.forEach(c => {
      if (c.images[i]) out.push({ src: c.images[i], cat: c.title, sector: c.sector, slug: c.id });
    });
  }
  return out;
})();

export { PROJECT_CATEGORIES, ALL_GALLERY_IMAGES };
