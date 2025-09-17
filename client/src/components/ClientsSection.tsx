import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Star, Quote, ExternalLink } from "lucide-react";
import portfolioImage from "@assets/generated_images/Responsive_web_design_showcase_0966f9c3.png";

interface ClientsSectionProps {
  onContactClick?: () => void;
}

export default function ClientsSection({ onContactClick }: ClientsSectionProps) {
  const handleContactClick = () => {
    const element = document.getElementById('lien-he');
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
    onContactClick?.();
  };

  // TODO: Remove mock data when implementing real backend
  const testimonials = [
    {
      name: "Anh Minh Tuấn",
      company: "CEO - TechStart Vietnam",
      content: "Website được làm rất chuyên nghiệp, đúng deadline và hỗ trợ nhiệt tình. Doanh số online tăng 200% sau khi ra mắt.",
      rating: 5,
      avatar: "MT"
    },
    {
      name: "Chị Thu Hương", 
      company: "Giám đốc - Beauty House",
      content: "Thiết kế đẹp, responsive tốt trên mobile. Khách hàng khen website chuyên nghiệp, tăng độ tin cậy rất nhiều.",
      rating: 5,
      avatar: "TH"
    },
    {
      name: "Anh Đức Anh",
      company: "Founder - EduTech",
      content: "Hệ thống e-learning phức tạp nhưng anh Dev làm rất tốt. Performance nhanh, user experience xuất sắc.",
      rating: 5,
      avatar: "DA"
    }
  ];

  const portfolioProjects = [
    {
      title: "E-commerce Fashion",
      category: "Bán hàng online",
      description: "Website bán thời trang với 10,000+ sản phẩm",
      image: portfolioImage,
      url: "#"
    },
    {
      title: "Corporate Website",
      category: "Doanh nghiệp",
      description: "Website giới thiệu công ty công nghệ",
      image: portfolioImage,
      url: "#"
    },
    {
      title: "Restaurant Landing",
      category: "Nhà hàng",
      description: "Landing page với đặt bàn online",
      image: portfolioImage,
      url: "#"
    }
  ];

  const clientLogos = [
    { name: "TechCorp", logo: "TC" },
    { name: "Digital Solutions", logo: "DS" },
    { name: "Smart Business", logo: "SB" },
    { name: "Innovation Hub", logo: "IH" },
    { name: "Future Tech", logo: "FT" },
    { name: "Creative Agency", logo: "CA" }
  ];

  return (
    <section id="khach-hang" className="py-20 bg-secondary/30">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          {/* Section Header */}
          <div className="text-center mb-16">
            <Badge variant="outline" className="mb-4" data-testid="badge-clients">
              Khách hàng
            </Badge>
            <h2 className="text-3xl md:text-4xl font-bold mb-4" data-testid="text-clients-title">
              Khách hàng tin tưởng & Dự án nổi bật
            </h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto" data-testid="text-clients-subtitle">
              Hơn 150 doanh nghiệp đã tin tưởng và đạt được kết quả tuyệt vời với các website chúng tôi thiết kế.
            </p>
          </div>

          {/* Client Logos */}
          <div className="mb-16">
            <h3 className="text-center text-lg font-semibold mb-8 text-muted-foreground" data-testid="text-trusted-by">
              Được tin tưởng bởi
            </h3>
            <div className="grid grid-cols-3 md:grid-cols-6 gap-6">
              {clientLogos.map((client, index) => (
                <div 
                  key={index}
                  className="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate"
                  data-testid={`logo-client-${index}`}
                >
                  <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <span className="font-bold text-sm text-primary">{client.logo}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Testimonials */}
          <div className="mb-16">
            <h3 className="text-2xl font-bold text-center mb-8" data-testid="text-testimonials-title">
              Phản hồi từ khách hàng
            </h3>
            <div className="grid md:grid-cols-3 gap-6">
              {testimonials.map((testimonial, index) => (
                <Card key={index} className="hover-elevate" data-testid={`card-testimonial-${index}`}>
                  <CardContent className="p-6">
                    <div className="flex items-center mb-4">
                      {[...Array(testimonial.rating)].map((_, starIndex) => (
                        <Star key={starIndex} className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                      ))}
                    </div>
                    
                    <Quote className="w-8 h-8 text-primary/20 mb-4" />
                    
                    <p className="text-muted-foreground mb-4 leading-relaxed" data-testid={`text-testimonial-content-${index}`}>
                      {testimonial.content}
                    </p>
                    
                    <div className="flex items-center space-x-3">
                      <div className="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                        <span className="font-semibold text-sm text-primary">{testimonial.avatar}</span>
                      </div>
                      <div>
                        <div className="font-semibold text-sm" data-testid={`text-testimonial-name-${index}`}>
                          {testimonial.name}
                        </div>
                        <div className="text-xs text-muted-foreground" data-testid={`text-testimonial-company-${index}`}>
                          {testimonial.company}
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>

          {/* Portfolio */}
          <div className="mb-12">
            <h3 className="text-2xl font-bold text-center mb-8" data-testid="text-portfolio-title">
              Dự án tiêu biểu
            </h3>
            <div className="grid md:grid-cols-3 gap-6">
              {portfolioProjects.map((project, index) => (
                <Card key={index} className="overflow-hidden hover-elevate group" data-testid={`card-portfolio-${index}`}>
                  <div className="relative overflow-hidden">
                    <img 
                      src={project.image}
                      alt={project.title}
                      className="w-full h-48 object-cover transition-transform group-hover:scale-105"
                      data-testid={`img-portfolio-${index}`}
                    />
                    <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                      <Button variant="outline" size="sm" className="bg-white/10 backdrop-blur-sm border-white/20 text-white">
                        <ExternalLink className="w-4 h-4 mr-2" />
                        Xem demo
                      </Button>
                    </div>
                  </div>
                  
                  <CardContent className="p-6">
                    <Badge variant="secondary" className="mb-2" data-testid={`badge-portfolio-category-${index}`}>
                      {project.category}
                    </Badge>
                    <h4 className="font-semibold mb-2" data-testid={`text-portfolio-title-${index}`}>
                      {project.title}
                    </h4>
                    <p className="text-sm text-muted-foreground" data-testid={`text-portfolio-description-${index}`}>
                      {project.description}
                    </p>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>

          {/* CTA */}
          <div className="text-center">
            <h3 className="text-xl font-semibold mb-4" data-testid="text-clients-cta-title">
              Bạn muốn trở thành khách hàng tiếp theo?
            </h3>
            <p className="text-muted-foreground mb-6" data-testid="text-clients-cta-subtitle">
              Hãy để chúng tôi giúp bạn xây dựng website thành công như các doanh nghiệp trên.
            </p>
            <Button size="lg" onClick={handleContactClick} data-testid="button-clients-contact">
              Bắt đầu dự án của bạn
            </Button>
          </div>
        </div>
      </div>
    </section>
  );
}