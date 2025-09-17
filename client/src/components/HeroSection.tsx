import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { ArrowRight, Star, Code, Smartphone, Search } from "lucide-react";
import heroImage from "@assets/generated_images/Professional_developer_workspace_hero_982269bc.png";

interface HeroSectionProps {
  onTrialClick?: () => void;
  onContactClick?: () => void;
}

export default function HeroSection({ onTrialClick, onContactClick }: HeroSectionProps) {
  const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const handleTrialClick = () => {
    scrollToSection('dung-thu');
    onTrialClick?.();
  };

  const handleContactClick = () => {
    scrollToSection('lien-he');
    onContactClick?.();
  };

  return (
    <section className="relative min-h-screen flex items-center overflow-hidden">
      {/* Background Image with Overlay */}
      <div 
        className="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style={{ backgroundImage: `url(${heroImage})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/30"></div>
      </div>

      {/* Content */}
      <div className="relative z-10 container mx-auto px-4 py-20">
        <div className="max-w-4xl">
          {/* Badge */}
          <Badge variant="outline" className="mb-6 bg-white/10 backdrop-blur-sm border-white/20 text-white" data-testid="badge-experience">
            <Star className="w-3 h-3 mr-1 fill-yellow-400 text-yellow-400" />
            10+ năm kinh nghiệm thiết kế web
          </Badge>

          {/* Main Heading */}
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight" data-testid="text-hero-title">
            Tạo Website 
            <span className="text-blue-400"> Chuyên Nghiệp</span>
            <br />
            Cho Doanh Nghiệp
          </h1>

          {/* Subheading */}
          <p className="text-lg md:text-xl text-gray-200 mb-8 max-w-2xl leading-relaxed" data-testid="text-hero-subtitle">
            Với hơn 10 năm kinh nghiệm, tôi giúp doanh nghiệp xây dựng website hiện đại, 
            responsive và tối ưu SEO. Dùng thử miễn phí ngay hôm nay!
          </p>

          {/* Features */}
          <div className="flex flex-wrap gap-4 mb-8">
            <div className="flex items-center space-x-2 text-white">
              <div className="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
                <Code className="w-4 h-4 text-blue-400" />
              </div>
              <span className="text-sm font-medium" data-testid="text-feature-modern">Thiết kế hiện đại</span>
            </div>
            <div className="flex items-center space-x-2 text-white">
              <div className="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                <Smartphone className="w-4 h-4 text-green-400" />
              </div>
              <span className="text-sm font-medium" data-testid="text-feature-responsive">100% Responsive</span>
            </div>
            <div className="flex items-center space-x-2 text-white">
              <div className="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center">
                <Search className="w-4 h-4 text-purple-400" />
              </div>
              <span className="text-sm font-medium" data-testid="text-feature-seo">Tối ưu SEO</span>
            </div>
          </div>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row gap-4">
            <Button 
              size="lg" 
              onClick={handleTrialClick}
              className="bg-blue-600 hover:bg-blue-700 text-white border-0 shadow-lg"
              data-testid="button-trial-hero"
            >
              Dùng thử miễn phí
              <ArrowRight className="w-4 h-4 ml-2" />
            </Button>
            <Button 
              size="lg" 
              variant="outline"
              onClick={handleContactClick}
              className="bg-white/10 backdrop-blur-sm border-white/20 text-white hover:bg-white/20"
              data-testid="button-contact-hero"
            >
              Tư vấn ngay
            </Button>
          </div>

          {/* Stats */}
          <div className="flex flex-wrap gap-8 mt-12 pt-8 border-t border-white/20">
            <div className="text-center">
              <div className="text-2xl md:text-3xl font-bold text-white" data-testid="text-stat-projects">200+</div>
              <div className="text-sm text-gray-300">Dự án hoàn thành</div>
            </div>
            <div className="text-center">
              <div className="text-2xl md:text-3xl font-bold text-white" data-testid="text-stat-clients">150+</div>
              <div className="text-sm text-gray-300">Khách hàng hài lòng</div>
            </div>
            <div className="text-center">
              <div className="text-2xl md:text-3xl font-bold text-white" data-testid="text-stat-years">10+</div>
              <div className="text-sm text-gray-300">Năm kinh nghiệm</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}