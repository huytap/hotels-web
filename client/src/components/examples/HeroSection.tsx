import HeroSection from '../HeroSection';

export default function HeroSectionExample() {
  return (
    <HeroSection 
      onTrialClick={() => console.log('Trial clicked from hero')}
      onContactClick={() => console.log('Contact clicked from hero')}
    />
  );
}