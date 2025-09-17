import AboutSection from '../AboutSection';

export default function AboutSectionExample() {
  return (
    <div className="min-h-screen">
      <AboutSection onContactClick={() => console.log('Contact clicked from about')} />
    </div>
  );
}