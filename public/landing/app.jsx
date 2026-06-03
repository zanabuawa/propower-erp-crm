const App = () => {
  const getSize = () => {
    const w = window.innerWidth;
    if (w < 768) return 'mobile';
    if (w < 1100) return 'tablet';
    return 'desktop';
  };
  const [size, setSize] = React.useState(getSize());
  React.useEffect(() => {
    const handler = () => setSize(getSize());
    window.addEventListener('resize', handler);
    return () => window.removeEventListener('resize', handler);
  }, []);
  return (
    <div style={{ position: 'relative' }}>
      <SplashScreen />
      {size === 'mobile' ? (
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
          <NosotrosDesktop size={size} />
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
