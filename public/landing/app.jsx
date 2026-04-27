const App = () => {
  const [isMobile, setIsMobile] = React.useState(window.innerWidth < 768);
  React.useEffect(() => {
    const handler = () => setIsMobile(window.innerWidth < 768);
    window.addEventListener('resize', handler);
    return () => window.removeEventListener('resize', handler);
  }, []);
  return (
    <div style={{ position: 'relative' }}>
      <SplashScreen />
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
