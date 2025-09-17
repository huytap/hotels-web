import Footer from '../Footer';

export default function FooterExample() {
  return (
    <div className="min-h-screen bg-background">
      <div className="h-96 bg-muted/20 rounded-lg m-8 flex items-center justify-center">
        <span className="text-muted-foreground">Page content above footer</span>
      </div>
      <Footer onContactClick={() => console.log('Contact clicked from footer')} />
    </div>
  );
}