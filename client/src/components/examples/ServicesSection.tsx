import ServicesSection from '../ServicesSection';

export default function ServicesSectionExample() {
  return (
    <div className="min-h-screen bg-background">
      <ServicesSection onContactClick={() => console.log('Contact clicked from services')} />
    </div>
  );
}