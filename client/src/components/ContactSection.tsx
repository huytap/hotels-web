import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useState } from "react";
import { 
  Phone, 
  Mail, 
  MapPin, 
  Clock,
  MessageCircle,
  Send
} from "lucide-react";
import { useToast } from "@/hooks/use-toast";

interface ContactSectionProps {
  onSubmit?: (data: any) => void;
}

export default function ContactSection({ onSubmit }: ContactSectionProps) {
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: ''
  });

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      const response = await fetch('/api/contact', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (response.ok && result.success) {
        toast({
          title: "Tin nhắn đã được gửi!",
          description: "Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong vòng 24h.",
        });
        
        onSubmit?.(formData);
        
        // Reset form
        setFormData({
          name: '',
          email: '',
          phone: '',
          subject: '',
          message: ''
        });
      } else {
        toast({
          title: "Có lỗi xảy ra!",
          description: result.message || "Vui lòng thử lại sau.",
          variant: "destructive",
        });
      }
    } catch (error) {
      console.error('Contact submission error:', error);
      toast({
        title: "Có lỗi xảy ra!",
        description: "Không thể gửi tin nhắn. Vui lòng thử lại sau.",
        variant: "destructive",
      });
    }
  };

  const contactInfo = [
    {
      icon: Phone,
      title: "Số điện thoại",
      content: "0987.654.321",
      description: "Sẵn sàng hỗ trợ 24/7",
      action: "tel:0987654321"
    },
    {
      icon: Mail,
      title: "Email",
      content: "contact@webpro.vn",
      description: "Phản hồi trong 24h",
      action: "mailto:contact@webpro.vn"
    },
    {
      icon: MapPin,
      title: "Địa chỉ",
      content: "123 Đường ABC, Quận 1, TP.HCM",
      description: "Tư vấn trực tiếp tại văn phòng"
    },
    {
      icon: Clock,
      title: "Giờ làm việc",
      content: "T2-T6: 8:00 - 18:00",
      description: "T7: 8:00 - 12:00"
    }
  ];

  const subjects = [
    "Tư vấn dự án mới",
    "Báo giá website",
    "Hỗ trợ kỹ thuật",
    "Bảo trì website",
    "SEO & Marketing",
    "Khác"
  ];

  return (
    <section id="lien-he" className="py-20 bg-secondary/30">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          {/* Section Header */}
          <div className="text-center mb-16">
            <Badge variant="outline" className="mb-4" data-testid="badge-contact">
              Liên hệ
            </Badge>
            <h2 className="text-3xl md:text-4xl font-bold mb-4" data-testid="text-contact-title">
              Kết nối với chúng tôi
            </h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto" data-testid="text-contact-subtitle">
              Hãy để lại thông tin, chúng tôi sẽ liên hệ tư vấn miễn phí và báo giá phù hợp với nhu cầu của bạn.
            </p>
          </div>

          <div className="grid lg:grid-cols-3 gap-8">
            {/* Contact Info */}
            <div className="lg:col-span-1 space-y-6">
              <div>
                <h3 className="text-xl font-bold mb-6" data-testid="text-contact-info-title">
                  Thông tin liên hệ
                </h3>
                <div className="space-y-4">
                  {contactInfo.map((info, index) => (
                    <Card key={index} className="hover-elevate" data-testid={`card-contact-info-${index}`}>
                      <CardContent className="p-4">
                        <div className="flex items-start space-x-3">
                          <div className="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <info.icon className="w-5 h-5 text-primary" />
                          </div>
                          <div className="flex-1">
                            <h4 className="font-semibold text-sm mb-1" data-testid={`text-contact-info-title-${index}`}>
                              {info.title}
                            </h4>
                            {info.action ? (
                              <a 
                                href={info.action}
                                className="text-primary hover:underline font-medium"
                                data-testid={`link-contact-info-${index}`}
                              >
                                {info.content}
                              </a>
                            ) : (
                              <div className="font-medium" data-testid={`text-contact-info-content-${index}`}>
                                {info.content}
                              </div>
                            )}
                            <p className="text-xs text-muted-foreground mt-1" data-testid={`text-contact-info-description-${index}`}>
                              {info.description}
                            </p>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              </div>

              {/* Quick Contact */}
              <Card className="border-primary/20 bg-primary/5">
                <CardContent className="p-6 text-center">
                  <MessageCircle className="w-12 h-12 text-primary mx-auto mb-4" />
                  <h4 className="font-semibold mb-2" data-testid="text-quick-contact-title">
                    Cần tư vấn gấp?
                  </h4>
                  <p className="text-sm text-muted-foreground mb-4" data-testid="text-quick-contact-description">
                    Gọi trực tiếp để được hỗ trợ ngay lập tức
                  </p>
                  <Button 
                    variant="outline" 
                    size="sm" 
                    className="border-primary text-primary hover:bg-primary hover:text-primary-foreground"
                    data-testid="button-quick-call"
                  >
                    <Phone className="w-4 h-4 mr-2" />
                    Gọi ngay
                  </Button>
                </CardContent>
              </Card>
            </div>

            {/* Contact Form */}
            <div className="lg:col-span-2">
              <Card className="shadow-lg">
                <CardHeader>
                  <CardTitle className="text-xl flex items-center" data-testid="text-contact-form-title">
                    <Send className="w-5 h-5 mr-2" />
                    Gửi tin nhắn cho chúng tôi
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="text-sm font-medium mb-2 block">
                          Họ tên *
                        </label>
                        <Input
                          value={formData.name}
                          onChange={(e) => handleInputChange('name', e.target.value)}
                          placeholder="Nguyễn Văn A"
                          required
                          data-testid="input-contact-name"
                        />
                      </div>
                      <div>
                        <label className="text-sm font-medium mb-2 block">
                          Số điện thoại *
                        </label>
                        <Input
                          type="tel"
                          value={formData.phone}
                          onChange={(e) => handleInputChange('phone', e.target.value)}
                          placeholder="0987654321"
                          required
                          data-testid="input-contact-phone"
                        />
                      </div>
                    </div>

                    <div>
                      <label className="text-sm font-medium mb-2 block">
                        Email *
                      </label>
                      <Input
                        type="email"
                        value={formData.email}
                        onChange={(e) => handleInputChange('email', e.target.value)}
                        placeholder="email@domain.com"
                        required
                        data-testid="input-contact-email"
                      />
                    </div>

                    <div>
                      <label className="text-sm font-medium mb-2 block">
                        Chủ đề *
                      </label>
                      <Select value={formData.subject} onValueChange={(value) => handleInputChange('subject', value)} required>
                        <SelectTrigger data-testid="select-contact-subject">
                          <SelectValue placeholder="Chọn chủ đề" />
                        </SelectTrigger>
                        <SelectContent>
                          {subjects.map((subject) => (
                            <SelectItem key={subject} value={subject} data-testid={`option-subject-${subject.toLowerCase().replace(/\s+/g, '-')}`}>
                              {subject}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <label className="text-sm font-medium mb-2 block">
                        Tin nhắn *
                      </label>
                      <Textarea
                        value={formData.message}
                        onChange={(e) => handleInputChange('message', e.target.value)}
                        placeholder="Vui lòng mô tả chi tiết yêu cầu của bạn..."
                        rows={5}
                        required
                        data-testid="textarea-contact-message"
                      />
                    </div>

                    <Button type="submit" size="lg" className="w-full" data-testid="button-submit-contact">
                      <Send className="w-4 h-4 mr-2" />
                      Gửi tin nhắn
                    </Button>

                    <p className="text-xs text-muted-foreground text-center">
                      Chúng tôi cam kết bảo mật thông tin và phản hồi trong vòng 24 giờ.
                    </p>
                  </form>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}