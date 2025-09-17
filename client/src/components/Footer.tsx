import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { 
  Phone, 
  Mail, 
  MapPin, 
  Facebook, 
  Twitter, 
  Linkedin, 
  Instagram,
  ExternalLink
} from "lucide-react";

interface FooterProps {
  onContactClick?: () => void;
}

export default function Footer({ onContactClick }: FooterProps) {
  const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const handleContactClick = () => {
    scrollToSection('lien-he');
    onContactClick?.();
  };

  const quickLinks = [
    { name: "Giới thiệu", action: () => scrollToSection('gioi-thieu') },
    { name: "Dịch vụ", action: () => scrollToSection('dich-vu') },
    { name: "Khách hàng", action: () => scrollToSection('khach-hang') },
    { name: "Dùng thử", action: () => scrollToSection('dung-thu') }
  ];

  const services = [
    "Website Doanh nghiệp",
    "Website Bán hàng",
    "Landing Page",
    "Tối ưu SEO",
    "UI/UX Design",
    "Bảo trì Website"
  ];

  const socialLinks = [
    { icon: Facebook, name: "Facebook", url: "#" },
    { icon: Twitter, name: "Twitter", url: "#" },
    { icon: Linkedin, name: "LinkedIn", url: "#" },
    { icon: Instagram, name: "Instagram", url: "#" }
  ];

  return (
    <footer className="bg-primary text-primary-foreground">
      <div className="container mx-auto px-4 py-16">
        <div className="max-w-6xl mx-auto">
          <div className="grid md:grid-cols-4 gap-8">
            {/* Company Info */}
            <div className="space-y-4">
              <div className="flex items-center space-x-2">
                <div className="w-8 h-8 bg-primary-foreground/10 rounded-md flex items-center justify-center">
                  <span className="text-primary-foreground font-bold text-sm">W</span>
                </div>
                <span className="font-bold text-xl">WebPro</span>
              </div>
              
              <p className="text-primary-foreground/80 text-sm leading-relaxed" data-testid="text-footer-description">
                Chuyên gia thiết kế website với 10+ năm kinh nghiệm. 
                Giúp doanh nghiệp xây dựng sự hiện diện trực tuyến chuyên nghiệp.
              </p>

              <div className="flex items-center space-x-2">
                <Badge variant="outline" className="border-primary-foreground/20 text-primary-foreground">
                  10+ năm kinh nghiệm
                </Badge>
              </div>

              {/* Social Links */}
              <div className="flex space-x-3">
                {socialLinks.map((social, index) => (
                  <Button
                    key={index}
                    variant="ghost"
                    size="icon"
                    className="hover:bg-primary-foreground/10 text-primary-foreground"
                    data-testid={`link-social-${social.name.toLowerCase()}`}
                  >
                    <social.icon className="w-4 h-4" />
                  </Button>
                ))}
              </div>
            </div>

            {/* Quick Links */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg" data-testid="text-footer-quicklinks-title">
                Liên kết nhanh
              </h3>
              <ul className="space-y-2">
                {quickLinks.map((link, index) => (
                  <li key={index}>
                    <button
                      onClick={link.action}
                      className="text-primary-foreground/80 hover:text-primary-foreground transition-colors text-sm"
                      data-testid={`link-footer-${link.name.toLowerCase().replace(/\s+/g, '-')}`}
                    >
                      {link.name}
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {/* Services */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg" data-testid="text-footer-services-title">
                Dịch vụ
              </h3>
              <ul className="space-y-2">
                {services.map((service, index) => (
                  <li key={index} className="text-primary-foreground/80 text-sm" data-testid={`text-footer-service-${index}`}>
                    {service}
                  </li>
                ))}
              </ul>
            </div>

            {/* Contact Info */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg" data-testid="text-footer-contact-title">
                Liên hệ
              </h3>
              <div className="space-y-3">
                <div className="flex items-center space-x-3">
                  <Phone className="w-4 h-4 text-primary-foreground/60" />
                  <a 
                    href="tel:0987654321"
                    className="text-primary-foreground/80 hover:text-primary-foreground text-sm"
                    data-testid="link-footer-phone"
                  >
                    0987.654.321
                  </a>
                </div>
                <div className="flex items-center space-x-3">
                  <Mail className="w-4 h-4 text-primary-foreground/60" />
                  <a 
                    href="mailto:contact@webpro.vn"
                    className="text-primary-foreground/80 hover:text-primary-foreground text-sm"
                    data-testid="link-footer-email"
                  >
                    contact@webpro.vn
                  </a>
                </div>
                <div className="flex items-start space-x-3">
                  <MapPin className="w-4 h-4 text-primary-foreground/60 mt-0.5" />
                  <span className="text-primary-foreground/80 text-sm" data-testid="text-footer-address">
                    123 Đường ABC, Quận 1, TP.HCM
                  </span>
                </div>
              </div>

              <Button 
                onClick={handleContactClick}
                variant="outline"
                size="sm"
                className="border-primary-foreground/20 text-primary-foreground hover:bg-primary-foreground hover:text-primary"
                data-testid="button-footer-contact"
              >
                <ExternalLink className="w-4 h-4 mr-2" />
                Liên hệ ngay
              </Button>
            </div>
          </div>

          {/* Bottom Bar */}
          <div className="border-t border-primary-foreground/20 mt-12 pt-8">
            <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
              <div className="text-primary-foreground/60 text-sm" data-testid="text-footer-copyright">
                © 2024 WebPro. Tất cả quyền được bảo lưu.
              </div>
              <div className="flex space-x-6 text-sm">
                <button className="text-primary-foreground/60 hover:text-primary-foreground transition-colors" data-testid="link-footer-privacy">
                  Chính sách bảo mật
                </button>
                <button className="text-primary-foreground/60 hover:text-primary-foreground transition-colors" data-testid="link-footer-terms">
                  Điều khoản sử dụng
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}