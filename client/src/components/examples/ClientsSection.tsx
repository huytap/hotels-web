import ClientsSection from '../ClientsSection';

export default function ClientsSectionExample() {
  return (
    <div className="min-h-screen">
      <ClientsSection onContactClick={() => console.log('Contact clicked from clients')} />
    </div>
  );
}