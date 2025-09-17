import { useState } from "react";
import Header from "@/components/Header";
import HeroSection from "@/components/HeroSection";
import AboutSection from "@/components/AboutSection";
import ServicesSection from "@/components/ServicesSection";
import ClientsSection from "@/components/ClientsSection";
import TrialSection from "@/components/TrialSection";
import ContactSection from "@/components/ContactSection";
import Footer from "@/components/Footer";

export default function Home() {
  const [activeSection, setActiveSection] = useState('');

  const handleContactClick = () => {
    console.log('Contact interaction triggered');
    setActiveSection('contact');
  };

  const handleTrialClick = () => {
    console.log('Trial interaction triggered');
    setActiveSection('trial');
  };

  const handleTrialSubmit = (data: any) => {
    console.log('Trial form submitted:', data);
    // TODO: Remove mock functionality - integrate with real backend
  };

  const handleContactSubmit = (data: any) => {
    console.log('Contact form submitted:', data);
    // TODO: Remove mock functionality - integrate with real backend
  };

  return (
    <div className="min-h-screen bg-background">
      <Header 
        onContactClick={handleContactClick}
        onTrialClick={handleTrialClick}
      />
      
      <main>
        <HeroSection 
          onContactClick={handleContactClick}
          onTrialClick={handleTrialClick}
        />
        
        <AboutSection 
          onContactClick={handleContactClick}
        />
        
        <ServicesSection 
          onContactClick={handleContactClick}
        />
        
        <ClientsSection 
          onContactClick={handleContactClick}
        />
        
        <TrialSection 
          onSubmit={handleTrialSubmit}
        />
        
        <ContactSection 
          onSubmit={handleContactSubmit}
        />
      </main>
      
      <Footer 
        onContactClick={handleContactClick}
      />
    </div>
  );
}