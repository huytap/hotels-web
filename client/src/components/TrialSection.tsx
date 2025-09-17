import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useState } from "react";
import { 
  Zap, 
  CheckCircle, 
  Clock, 
  Users, 
  Palette,
  Monitor,
  Smartphone,
  ShoppingCart
} from "lucide-react";
import { useToast } from "@/hooks/use-toast";

interface TrialSectionProps {
  onSubmit?: (data: any) => void;
}

export default function TrialSection({ onSubmit }: TrialSectionProps) {
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    company: '',
    websiteType: '',
    description: '',
    budget: ''
  });

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Trial form submitted:', formData);
    
    try {
      const response = await fetch('/api/trial', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (response.ok && result.success) {
        toast({
          title: "Đăng ký thành công!",
          description: "Chúng tôi sẽ liên hệ với bạn trong vòng 24h để tư vấn miễn phí.",
        });
        
        onSubmit?.(formData);
        
        // Reset form
        setFormData({
          name: '',
          email: '',
          phone: '',
          company: '',
          websiteType: '',
          description: '',
          budget: ''
        });
      } else {
        toast({
          title: "Có lỗi xảy ra!",
          description: result.message || "Vui lòng thử lại sau.",
          variant: "destructive",
        });
      }
    } catch (error) {
      console.error('Trial submission error:', error);
      toast({
        title: "Có lỗi xảy ra!",
        description: "Không thể gửi đăng ký. Vui lòng thử lại sau.",
        variant: "destructive",
      });
    }
  };

  const benefits = [
    {
      icon: Zap,
      title: "Miễn phí 100%",
      description: "Không mất phí, không cam kết"
    },
    {
      icon: Clock,
      title: "Tư vấn 1-1",
      description: "30 phút tư vấn trực tiếp"
    },
    {
      icon: Users,
      title: "Demo thực tế",
      description: "Xem mẫu thiết kế cho dự án"
    },
    {
      icon: Palette,
      title: "Phân tích UX",
      description: "Đánh giá website hiện tại"
    }
  ];

  const websiteTypes = [
    { value: "corporate", label: "Website Doanh nghiệp", icon: Monitor },
    { value: "ecommerce", label: "Website Bán hàng", icon: ShoppingCart },
    { value: "landing", label: "Landing Page", icon: Smartphone }
  ];

  return (
    <section id="dung-thu" className="py-20">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          {/* Section Header */}
          <div className="text-center mb-16">
            <Badge variant="outline" className="mb-4" data-testid="badge-trial">
              Dùng thử miễn phí
            </Badge>
            <h2 className="text-3xl md:text-4xl font-bold mb-4" data-testid="text-trial-title">
              Nhận tư vấn & demo miễn phí
            </h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto" data-testid="text-trial-subtitle">
              Đăng ký ngay để được tư vấn 1-1 và xem demo website phù hợp với doanh nghiệp của bạn.
            </p>
          </div>

          <div className="grid lg:grid-cols-2 gap-12 items-start">
            {/* Benefits */}
            <div className="space-y-8">
              <div>
                <h3 className="text-2xl font-bold mb-6" data-testid="text-trial-benefits-title">
                  Bạn sẽ nhận được gì?
                </h3>
                <div className="grid gap-4">
                  {benefits.map((benefit, index) => (
                    <div key={index} className="flex items-start space-x-4" data-testid={`benefit-${index}`}>
                      <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <benefit.icon className="w-6 h-6 text-primary" />
                      </div>
                      <div>
                        <h4 className="font-semibold mb-1" data-testid={`text-benefit-title-${index}`}>
                          {benefit.title}
                        </h4>
                        <p className="text-sm text-muted-foreground" data-testid={`text-benefit-description-${index}`}>
                          {benefit.description}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Process */}
              <div>
                <h3 className="text-xl font-bold mb-4" data-testid="text-trial-process-title">
                  Quy trình làm việc
                </h3>
                <div className="space-y-3">
                  <div className="flex items-center space-x-3">
                    <div className="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-xs text-primary-foreground font-bold">
                      1
                    </div>
                    <span className="text-sm" data-testid="text-process-step-1">Đăng ký form dưới đây</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <div className="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-xs text-primary-foreground font-bold">
                      2
                    </div>
                    <span className="text-sm" data-testid="text-process-step-2">Nhận cuộc gọi tư vấn trong 24h</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <div className="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-xs text-primary-foreground font-bold">
                      3
                    </div>
                    <span className="text-sm" data-testid="text-process-step-3">Xem demo và nhận báo giá</span>
                  </div>
                </div>
              </div>

              {/* Guarantee */}
              <Card className="border-green-200 bg-green-50/50">
                <CardContent className="p-6">
                  <div className="flex items-start space-x-3">
                    <CheckCircle className="w-6 h-6 text-green-600 flex-shrink-0 mt-1" />
                    <div>
                      <h4 className="font-semibold text-green-800 mb-1" data-testid="text-guarantee-title">
                        Cam kết 100%
                      </h4>
                      <p className="text-sm text-green-700" data-testid="text-guarantee-description">
                        Hoàn toàn miễn phí, không ràng buộc. Nếu không hài lòng, bạn có thể từ chối bất cứ lúc nào.
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Trial Form */}
            <Card className="shadow-lg">
              <CardHeader>
                <CardTitle className="text-xl" data-testid="text-trial-form-title">
                  Đăng ký tư vấn miễn phí
                </CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="text-sm font-medium mb-1 block">
                        Họ tên *
                      </label>
                      <Input
                        value={formData.name}
                        onChange={(e) => handleInputChange('name', e.target.value)}
                        placeholder="Nguyễn Văn A"
                        required
                        data-testid="input-name"
                      />
                    </div>
                    <div>
                      <label className="text-sm font-medium mb-1 block">
                        Số điện thoại *
                      </label>
                      <Input
                        type="tel"
                        value={formData.phone}
                        onChange={(e) => handleInputChange('phone', e.target.value)}
                        placeholder="0987654321"
                        required
                        data-testid="input-phone"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="text-sm font-medium mb-1 block">
                      Email *
                    </label>
                    <Input
                      type="email"
                      value={formData.email}
                      onChange={(e) => handleInputChange('email', e.target.value)}
                      placeholder="email@domain.com"
                      required
                      data-testid="input-email"
                    />
                  </div>

                  <div>
                    <label className="text-sm font-medium mb-1 block">
                      Tên công ty
                    </label>
                    <Input
                      value={formData.company}
                      onChange={(e) => handleInputChange('company', e.target.value)}
                      placeholder="Công ty ABC"
                      data-testid="input-company"
                    />
                  </div>

                  <div>
                    <label className="text-sm font-medium mb-1 block">
                      Loại website cần làm *
                    </label>
                    <Select value={formData.websiteType} onValueChange={(value) => handleInputChange('websiteType', value)}>
                      <SelectTrigger data-testid="select-website-type">
                        <SelectValue placeholder="Chọn loại website" />
                      </SelectTrigger>
                      <SelectContent>
                        {websiteTypes.map((type) => (
                          <SelectItem key={type.value} value={type.value} data-testid={`option-${type.value}`}>
                            {type.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <label className="text-sm font-medium mb-1 block">
                      Ngân sách dự kiến
                    </label>
                    <Select value={formData.budget} onValueChange={(value) => handleInputChange('budget', value)}>
                      <SelectTrigger data-testid="select-budget">
                        <SelectValue placeholder="Chọn mức ngân sách" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="under-5m" data-testid="option-under-5m">Dưới 5 triệu</SelectItem>
                        <SelectItem value="5m-10m" data-testid="option-5m-10m">5-10 triệu</SelectItem>
                        <SelectItem value="10m-20m" data-testid="option-10m-20m">10-20 triệu</SelectItem>
                        <SelectItem value="over-20m" data-testid="option-over-20m">Trên 20 triệu</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <label className="text-sm font-medium mb-1 block">
                      Mô tả chi tiết dự án
                    </label>
                    <Textarea
                      value={formData.description}
                      onChange={(e) => handleInputChange('description', e.target.value)}
                      placeholder="Ví dụ: Tôi cần website bán thời trang online, có thanh toán, quản lý sản phẩm..."
                      rows={3}
                      data-testid="textarea-description"
                    />
                  </div>

                  <Button type="submit" className="w-full" size="lg" data-testid="button-submit-trial">
                    Đăng ký nhận tư vấn miễn phí
                  </Button>

                  <p className="text-xs text-muted-foreground text-center">
                    Bằng cách đăng ký, bạn đồng ý với việc chúng tôi liên hệ để tư vấn dự án.
                  </p>
                </form>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </section>
  );
}