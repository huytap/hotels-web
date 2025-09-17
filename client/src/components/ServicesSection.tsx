import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { 
  Monitor, 
  Smartphone, 
  ShoppingCart, 
  Search, 
  Palette, 
  Zap,
  Check,
  Star
} from "lucide-react";

interface ServicesSectionProps {
  onContactClick?: () => void;
}

export default function ServicesSection({ onContactClick }: ServicesSectionProps) {
  const handleContactClick = () => {
    const element = document.getElementById('lien-he');
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
    onContactClick?.();
  };

  const services = [
    {
      icon: Monitor,
      title: "Website Doanh Nghiệp",
      description: "Website giới thiệu công ty chuyên nghiệp, thể hiện thương hiệu và tăng độ tin cậy",
      price: "Từ 5,000,000₫",
      features: [
        "Thiết kế responsive đầy đủ",
        "Tối ưu SEO cơ bản",
        "Tích hợp Google Analytics",
        "SSL miễn phí",
        "Hỗ trợ 6 tháng"
      ],
      popular: false,
      color: "blue"
    },
    {
      icon: ShoppingCart,
      title: "Website Bán Hàng",
      description: "Hệ thống bán hàng online đầy đủ tính năng, tích hợp thanh toán và quản lý",
      price: "Từ 12,000,000₫",
      features: [
        "Quản lý sản phẩm đầy đủ",
        "Tích hợp thanh toán",
        "Quản lý đơn hàng",
        "Báo cáo doanh thu",
        "App mobile (tùy chọn)",
        "Hỗ trợ 12 tháng"
      ],
      popular: true,
      color: "green"
    },
    {
      icon: Smartphone,
      title: "Landing Page",
      description: "Trang đích chuyên biệt để tăng conversion rate, thu hút khách hàng tiềm năng",
      price: "Từ 3,000,000₫",
      features: [
        "Thiết kế conversion-focused",
        "Form thu thập leads",
        "Tích hợp Google Ads",
        "A/B testing",
        "Hỗ trợ 3 tháng"
      ],
      popular: false,
      color: "purple"
    }
  ];

  const additionalServices = [
    {
      icon: Search,
      title: "Tối ưu SEO",
      description: "Tăng thứ hạng Google, thu hút traffic tự nhiên"
    },
    {
      icon: Palette,
      title: "UI/UX Design",
      description: "Thiết kế giao diện đẹp, trải nghiệm người dùng tốt"
    },
    {
      icon: Zap,
      title: "Tối ưu tốc độ",
      description: "Website load nhanh, cải thiện trải nghiệm"
    }
  ];

  return (
    <section id="dich-vu" className="py-20">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          {/* Section Header */}
          <div className="text-center mb-16">
            <Badge variant="outline" className="mb-4" data-testid="badge-services">
              Dịch vụ
            </Badge>
            <h2 className="text-3xl md:text-4xl font-bold mb-4" data-testid="text-services-title">
              Các gói dịch vụ phù hợp với nhu cầu
            </h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto" data-testid="text-services-subtitle">
              Từ website cơ bản đến hệ thống phức tạp, chúng tôi có giải pháp phù hợp với mọi quy mô doanh nghiệp.
            </p>
          </div>

          {/* Main Services */}
          <div className="grid md:grid-cols-3 gap-8 mb-16">
            {services.map((service, index) => (
              <Card 
                key={index} 
                className={`relative hover-elevate ${service.popular ? 'border-primary shadow-lg' : ''}`}
                data-testid={`card-service-${index}`}
              >
                {service.popular && (
                  <div className="absolute -top-3 left-1/2 transform -translate-x-1/2">
                    <Badge className="bg-primary text-primary-foreground" data-testid="badge-popular">
                      <Star className="w-3 h-3 mr-1 fill-current" />
                      Phổ biến nhất
                    </Badge>
                  </div>
                )}
                
                <CardHeader className="text-center pb-4">
                  <div className={`w-16 h-16 mx-auto mb-4 bg-${service.color}-100 rounded-lg flex items-center justify-center`}>
                    <service.icon className={`w-8 h-8 text-${service.color}-600`} />
                  </div>
                  <CardTitle className="text-xl mb-2" data-testid={`text-service-title-${index}`}>
                    {service.title}
                  </CardTitle>
                  <p className="text-sm text-muted-foreground" data-testid={`text-service-description-${index}`}>
                    {service.description}
                  </p>
                </CardHeader>

                <CardContent className="space-y-4">
                  <div className="text-center">
                    <div className="text-2xl font-bold text-primary mb-1" data-testid={`text-service-price-${index}`}>
                      {service.price}
                    </div>
                    <p className="text-xs text-muted-foreground">Thanh toán một lần</p>
                  </div>

                  <ul className="space-y-2">
                    {service.features.map((feature, featureIndex) => (
                      <li key={featureIndex} className="flex items-start space-x-2" data-testid={`text-feature-${index}-${featureIndex}`}>
                        <Check className="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" />
                        <span className="text-sm">{feature}</span>
                      </li>
                    ))}
                  </ul>

                  <Button 
                    onClick={handleContactClick}
                    className="w-full"
                    variant={service.popular ? "default" : "outline"}
                    data-testid={`button-service-contact-${index}`}
                  >
                    Tư vấn gói này
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>

          {/* Additional Services */}
          <div className="text-center mb-8">
            <h3 className="text-2xl font-bold mb-4" data-testid="text-additional-services-title">
              Dịch vụ bổ sung
            </h3>
          </div>

          <div className="grid md:grid-cols-3 gap-6">
            {additionalServices.map((service, index) => (
              <Card key={index} className="hover-elevate" data-testid={`card-additional-service-${index}`}>
                <CardContent className="p-6 text-center">
                  <div className="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                    <service.icon className="w-6 h-6 text-primary" />
                  </div>
                  <h4 className="font-semibold mb-2" data-testid={`text-additional-service-title-${index}`}>
                    {service.title}
                  </h4>
                  <p className="text-sm text-muted-foreground" data-testid={`text-additional-service-description-${index}`}>
                    {service.description}
                  </p>
                </CardContent>
              </Card>
            ))}
          </div>

          {/* CTA */}
          <div className="text-center mt-12">
            <p className="text-muted-foreground mb-4" data-testid="text-services-cta">
              Không tìm thấy gói phù hợp? Hãy liên hệ để được tư vấn giải pháp riêng.
            </p>
            <Button size="lg" onClick={handleContactClick} data-testid="button-services-contact">
              Tư vấn miễn phí
            </Button>
          </div>
        </div>
      </div>
    </section>
  );
}