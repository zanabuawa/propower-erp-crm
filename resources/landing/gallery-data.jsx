// Catálogo real de proyectos de la galería ProPower
const PROJECT_CATEGORIES = [
  {
    id: 'baja-tension',
    title: 'Baja Tensión',
    short: 'Baja Tensión',
    sector: 'Industria',
    desc: 'Instalaciones eléctricas en baja tensión para naves industriales y comerciales.',
    images: [1, 10, 15, 20, 25, 30, 35, 40].map(n => `/assets/img/Galeria/Baja-Tension/${n}.webp`),
  },
  {
    id: 'media-tension',
    title: 'Media Tensión',
    short: 'Media Tensión',
    sector: 'Industria',
    desc: 'Subestaciones, líneas y acometidas de media tensión.',
    images: [1, 10, 15, 20, 25, 30, 35, 42].map(n => `/assets/img/Galeria/Media-Tension/${n}.webp`),
  },
  {
    id: 'pruebas-electricas',
    title: 'Pruebas Eléctricas',
    short: 'Pruebas',
    sector: 'Mantenimiento',
    desc: 'Pruebas a equipo eléctrico: aislamiento, rigidez, continuidad y puesta a tierra.',
    images: [1, 2, 3, 4, 5].map(n => `/assets/img/Galeria/Pruebas Electricas/${n}.webp`),
  },
  {
    id: 'habilitacion',
    title: 'Habilitación Eléctrica',
    short: 'Habilitación',
    sector: 'Industria',
    desc: 'Habilitación de instalaciones eléctricas para nuevos espacios industriales.',
    images: [1, 2, 3, 4].map(n => `/assets/img/Galeria/Habilitacion electrica/${n}.webp`),
  },
  {
    id: 'tableros',
    title: 'Mejora de Tableros Eléctricos',
    short: 'Tableros',
    sector: 'Mantenimiento',
    desc: 'Modernización y mejora de tableros existentes para cumplir con norma actual.',
    images: [1, 2, 3, 4, 5].map(n => `/assets/img/Galeria/Mejora de tableros electricos existentes/${n}.webp`),
  },
  {
    id: 'control',
    title: 'Actualizaciones de Control',
    short: 'Control',
    sector: 'Ingeniería',
    desc: 'Actualizaciones de sistemas de control para plantas energéticas.',
    images: [1, 2, 3, 4, 5].map(n => `/assets/img/Galeria/Actualizaciones de control para planta energetica/${n}.webp`),
  },
  {
    id: 'rodillo',
    title: 'Reparación de Rodillo de Roladora',
    short: 'Roladora',
    sector: 'Industria',
    desc: 'Desmontaje y reparación de rodillo de roladora industrial.',
    images: [1, 2, 3, 4].map(n => `/assets/img/Galeria/Desmontaje y reparacionde de rodillo de roladora/${n}.webp`),
  },
  {
    id: 'molino',
    title: 'Laminado de Estructura de Molino',
    short: 'Molino',
    sector: 'Industria',
    desc: 'Trabajos de laminado en estructura de molino industrial.',
    images: [1, 2, 3, 4].map(n => `/assets/img/Galeria/Laminado de estructura de molino/${n}.webp`),
  },
  {
    id: 'iluminacion',
    title: 'Iluminación LED y Láminas Translúcidas',
    short: 'Iluminación',
    sector: 'Mantenimiento',
    desc: 'Reemplazo de láminas translúcidas y actualización de iluminación LED industrial.',
    images: [1, 4, 8, 11, 13].map(n => `/assets/img/Galeria/Remplazo de laminas translucidas y actualizacion de iluminacion led/${n}.webp`),
  },
  {
    id: 'venta',
    title: 'Venta de Equipo y Material',
    short: 'Equipo',
    sector: 'Comercial',
    desc: 'Venta y distribución de equipo y material eléctrico industrial.',
    images: [1, 5, 10, 16].map(n => `/assets/img/Galeria/Venta de equipo y material electrico/${n}.webp`),
  },
];

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
