// Masonry grid adaptativo. Toma cualquier set de imágenes con tamaños distintos
// y las distribuye en N columnas por altura mínima. Mantiene aspect ratios reales.

const MasonryGrid = ({ images, columns = 4, gap = 12, onImageClick }) => {
  const [imgMeta, setImgMeta] = React.useState({});
  const [loaded, setLoaded] = React.useState({});
  const containerRef = React.useRef(null);

  // Observa los tiles y les pone .is-visible al entrar al viewport
  React.useEffect(() => {
    if (!containerRef.current) return;
    const tiles = containerRef.current.querySelectorAll('.gallery-tile');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.05, rootMargin: '0px 0px -40px 0px' });
    tiles.forEach(t => io.observe(t));
    return () => io.disconnect();
  });

  React.useEffect(() => {
    images.forEach(item => {
      const src = typeof item === 'string' ? item : item.src;
      if (imgMeta[src]) return;
      const im = new Image();
      im.onload = () => {
        setImgMeta(m => ({ ...m, [src]: { w: im.naturalWidth, h: im.naturalHeight } }));
      };
      im.onerror = () => {
        setImgMeta(m => ({ ...m, [src]: { w: 1, h: 1 } }));
      };
      im.src = src;
    });
  }, [images]);

  // distribute into columns by minimum height
  const cols = Array.from({ length: columns }, () => ({ items: [], height: 0 }));
  images.forEach((item, i) => {
    const src = typeof item === 'string' ? item : item.src;
    const meta = imgMeta[src] || { w: 4, h: 3 };
    const ratio = meta.h / meta.w;
    const target = cols.reduce((min, c, idx) => c.height < cols[min].height ? idx : min, 0);
    cols[target].items.push({ item, idx: i, ratio });
    cols[target].height += ratio + 0.05;
  });

  return (
    <div ref={containerRef} style={{ display: 'grid', gridTemplateColumns: `repeat(${columns}, 1fr)`, gap }}>
      {cols.map((col, ci) => (
        <div key={ci} style={{ display: 'flex', flexDirection: 'column', gap }}>
          {col.items.map(({ item, idx, ratio }) => {
            const src = typeof item === 'string' ? item : item.src;
            const cat = typeof item === 'string' ? null : item.cat;
            const isLoaded = loaded[src];
            // Las primeras 8 imagenes visibles cargan con prioridad (eager)
            const isAboveFold = idx < 8;
            return (
              <div
                key={idx}
                onClick={() => onImageClick && onImageClick(item, idx)}
                className="pp-zoom gallery-tile"
                style={{
                  position: 'relative', width: '100%', paddingBottom: `${ratio * 100}%`,
                  background: '#1a1a1a', overflow: 'hidden', cursor: onImageClick ? 'pointer' : 'default',
                }}
              >
                {/* Skeleton shimmer mientras carga */}
                {!isLoaded && (
                  <div style={{
                    position: 'absolute', inset: 0,
                    background: 'linear-gradient(90deg, #1a1a1a 0%, #2a2a2a 50%, #1a1a1a 100%)',
                    backgroundSize: '200% 100%',
                    animation: 'ppShimmer 1.4s ease-in-out infinite',
                  }} />
                )}
                <img
                  src={src}
                  alt={cat || ''}
                  loading={isAboveFold ? 'eager' : 'lazy'}
                  decoding="async"
                  fetchpriority={isAboveFold ? 'high' : 'auto'}
                  onLoad={() => setLoaded(l => ({ ...l, [src]: true }))}
                  style={{
                    position: 'absolute', inset: 0, width: '100%', height: '100%',
                    objectFit: 'cover', display: 'block',
                    opacity: isLoaded ? 1 : 0,
                    transition: 'opacity 0.5s ease, transform 0.7s cubic-bezier(0.22, 1, 0.36, 1)',
                  }}
                />
                {cat && (
                  <div style={{
                    position: 'absolute', inset: 0,
                    background: 'linear-gradient(180deg, transparent 55%, rgba(10,10,10,0.85) 100%)',
                    opacity: 0, transition: 'opacity 0.3s',
                    display: 'flex', alignItems: 'flex-end', padding: 14,
                    pointerEvents: 'none',
                  }}
                  onMouseEnter={e => e.currentTarget.style.opacity = 1}
                  onMouseLeave={e => e.currentTarget.style.opacity = 0}
                  >
                    <div style={{
                      fontFamily: 'JetBrains Mono, monospace', fontSize: 10,
                      letterSpacing: '0.18em', textTransform: 'uppercase',
                      color: '#fff', padding: '4px 8px', background: 'var(--pp-red)',
                    }}>
                      {cat}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      ))}
    </div>
  );
};

Object.assign(window, { MasonryGrid });
