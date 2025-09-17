import { Button } from "@/components/ui/button";
import { Phone, Menu, X } from "lucide-react";
import { useState } from "react";

interface HeaderProps {
  onContactClick?: () => void;
  onTrialClick?: () => void;
}

export default function Header({ onContactClick, onTrialClick }: HeaderProps) {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
      setIsMobileMenuOpen(false);
    }
  };

  const handleContactClick = () => {
    scrollToSection('lien-he');
    onContactClick?.();
  };

  const handleTrialClick = () => {
    scrollToSection('dung-thu');
    onTrialClick?.();
  };

  return (
    <header className="fixed top-0 left-0 right-0 z-50 bg-background/95 backdrop-blur-sm border-b">
      <div className="container mx-auto px-4 py-4">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <div className="flex items-center space-x-2">
            <div className="w-8 h-8 bg-primary rounded-md flex items-center justify-center">
              <span className="text-primary-foreground font-bold text-sm">W</span>
            </div>
            <span className="font-bold text-xl">WebPro</span>
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            <button 
              onClick={() => scrollToSection('gioi-thieu')} 
              className="text-foreground hover:text-primary transition-colors"
              data-testid="nav-about"
            >
              Giới thiệu
            </button>
            <button 
              onClick={() => scrollToSection('dich-vu')} 
              className="text-foreground hover:text-primary transition-colors"
              data-testid="nav-services"
            >
              Dịch vụ
            </button>
            <button 
              onClick={() => scrollToSection('khach-hang')} 
              className="text-foreground hover:text-primary transition-colors"
              data-testid="nav-clients"
            >
              Khách hàng
            </button>
            <button 
              onClick={handleTrialClick} 
              className="text-foreground hover:text-primary transition-colors"
              data-testid="nav-trial"
            >
              Dùng thử
            </button>
          </nav>

          {/* Contact Info & CTA */}
          <div className="hidden md:flex items-center space-x-4">
            <div className="flex items-center space-x-2 text-sm text-muted-foreground">
              <Phone className="w-4 h-4" />
              <span data-testid="text-phone">0987.654.321</span>
            </div>
            <Button onClick={handleContactClick} variant="outline" size="sm" data-testid="button-contact">
              Liên hệ
            </Button>
            <Button onClick={handleTrialClick} size="sm" data-testid="button-trial-header">
              Dùng thử miễn phí
            </Button>
          </div>

          {/* Mobile Menu Button */}
          <Button
            variant="ghost"
            size="icon"
            className="md:hidden"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            data-testid="button-mobile-menu"
          >
            {isMobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </Button>
        </div>

        {/* Mobile Navigation */}
        {isMobileMenuOpen && (
          <div className="md:hidden mt-4 pb-4 border-t pt-4">
            <nav className="flex flex-col space-y-4">
              <button 
                onClick={() => scrollToSection('gioi-thieu')} 
                className="text-left text-foreground hover:text-primary transition-colors"
                data-testid="nav-about-mobile"
              >
                Giới thiệu
              </button>
              <button 
                onClick={() => scrollToSection('dich-vu')} 
                className="text-left text-foreground hover:text-primary transition-colors"
                data-testid="nav-services-mobile"
              >
                Dịch vụ
              </button>
              <button 
                onClick={() => scrollToSection('khach-hang')} 
                className="text-left text-foreground hover:text-primary transition-colors"
                data-testid="nav-clients-mobile"
              >
                Khách hàng
              </button>
              <button 
                onClick={handleTrialClick} 
                className="text-left text-foreground hover:text-primary transition-colors"
                data-testid="nav-trial-mobile"
              >
                Dùng thử
              </button>
              <div className="flex items-center space-x-2 text-sm text-muted-foreground pt-2">
                <Phone className="w-4 h-4" />
                <span data-testid="text-phone-mobile">0987.654.321</span>
              </div>
              <div className="flex flex-col space-y-2 pt-2">
                <Button onClick={handleContactClick} variant="outline" size="sm" data-testid="button-contact-mobile">
                  Liên hệ
                </Button>
                <Button onClick={handleTrialClick} size="sm" data-testid="button-trial-mobile">
                  Dùng thử miễn phí
                </Button>
              </div>
            </nav>
          </div>
        )}
      </div>
    </header>
  );
}