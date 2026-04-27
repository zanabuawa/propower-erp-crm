import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './styles.css';
import {
  Nav, HeroDesktop, HeroMobile, OfertaDesktop,
  NosotrosDesktop, NosotrosMobile, ServiciosDesktop, ServiciosMobile,
  GaleriaDesktop, GaleriaMobile, ContactoDesktop, ContactoMobile,
  Footer, FooterMobile, WhatsappFloat,
} from './sections.jsx';

const App = () => {
  const [isMobile, setIsMobile] = useState(window.innerWidth < 768);

  useEffect(() => {
    const handler = () => setIsMobile(window.innerWidth < 768);
    window.addEventListener('resize', handler);
    return () => window.removeEventListener('resize', handler);
  }, []);

  return (
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
  );
};

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
