import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Award, Users, Clock, TrendingUp, Code2, Palette, Zap } from "lucide-react";
import developerImage from "@assets/generated_images/Professional_developer_headshot_e9eddf65.png";

interface AboutSectionProps {
  onContactClick?: () => void;
}

export default function AboutSection({ onContactClick }: AboutSectionProps) {
  const handleContactClick = () => {
    const element = document.getElementById('lien-he');
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
    onContactClick?.();
  };

  const achievements = [
    {
      icon: Award,
      number: "10+",
      label: "Năm kinh nghiệm",
      color: "text-blue-600"
    },
    {
      icon: Users,
      number: "150+",
      label: "Khách hàng tin tưởng",
      color: "text-green-600"
    },
    {
      icon: Clock,
      number: "200+",
      label: "Dự án hoàn thành",
      color: "text-purple-600"
    },
    {
      icon: TrendingUp,
      number: "98%",
      label: "Khách hàng hài lòng",
      color: "text-orange-600"
    }
  ];

  const skills = [
    {
      icon: Code2,
      title: "Full-stack Development",
      description: "React, Node.js, TypeScript, Python"
    },
    {
      icon: Palette,
      title: "UI/UX Design",
      description: "Figma, Adobe XD, Modern Design Systems"
    },
    {
      icon: Zap,
      title: "Performance Optimization",
      description: "SEO, Speed Optimization, Responsive Design"
    }
  ];

  return (
    <section id="gioi-thieu" className="py-20 bg-secondary/30">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          {/* Section Header */}
          <div className="text-center mb-16">
            <Badge variant="outline" className="mb-4" data-testid="badge-about">
              Về chúng tôi
            </Badge>
            <h2 className="text-3xl md:text-4xl font-bold mb-4" data-testid="text-about-title">
              Chuyên gia với 10+ năm kinh nghiệm
            </h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto" data-testid="text-about-subtitle">
              Tôi đã giúp hàng trăm doanh nghiệp xây dựng website chuyên nghiệp, 
              từ startup đến các công ty lớn.
            </p>
          </div>

          <div className="grid lg:grid-cols-2 gap-12 items-center mb-16">
            {/* Developer Info */}
            <div className="space-y-6">
              <div className="flex items-start space-x-4">
                <img 
                  src={developerImage}
                  alt="Developer"
                  className="w-20 h-20 rounded-full object-cover border-4 border-primary/20"
                  data-testid="img-developer"
                />
                <div>
                  <h3 className="text-xl font-semibold mb-2" data-testid="text-developer-name">
                    Nguyễn Văn Dev
                  </h3>
                  <p className="text-muted-foreground mb-2" data-testid="text-developer-title">
                    Senior Full-stack Developer & UI/UX Designer
                  </p>
                  <div className="flex flex-wrap gap-2">
                    <Badge variant="secondary">React</Badge>
                    <Badge variant="secondary">Node.js</Badge>
                    <Badge variant="secondary">TypeScript</Badge>
                    <Badge variant="secondary">UI/UX</Badge>
                  </div>
                </div>
              </div>

              <p className="text-muted-foreground leading-relaxed" data-testid="text-developer-description">
                Với hơn 10 năm kinh nghiệm trong lĩnh vực phát triển web, tôi đã làm việc 
                với các công nghệ từ cơ bản đến tiên tiến nhất. Chuyên môn của tôi không chỉ 
                dừng lại ở việc code mà còn hiểu sâu về trải nghiệm người dùng và tối ưu hóa 
                cho doanh nghiệp.
              </p>

              <Button onClick={handleContactClick} size="lg" data-testid="button-contact-about">
                Tư vấn miễn phí
              </Button>
            </div>

            {/* Skills */}
            <div className="space-y-4">
              {skills.map((skill, index) => (
                <Card key={index} className="hover-elevate" data-testid={`card-skill-${index}`}>
                  <CardContent className="p-6">
                    <div className="flex items-start space-x-4">
                      <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                        <skill.icon className="w-6 h-6 text-primary" />
                      </div>
                      <div>
                        <h4 className="font-semibold mb-2" data-testid={`text-skill-title-${index}`}>
                          {skill.title}
                        </h4>
                        <p className="text-sm text-muted-foreground" data-testid={`text-skill-description-${index}`}>
                          {skill.description}
                        </p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>

          {/* Achievements Grid */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {achievements.map((achievement, index) => (
              <Card key={index} className="text-center hover-elevate" data-testid={`card-achievement-${index}`}>
                <CardContent className="p-6">
                  <div className={`w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center`}>
                    <achievement.icon className={`w-6 h-6 ${achievement.color}`} />
                  </div>
                  <div className="text-2xl font-bold mb-1" data-testid={`text-achievement-number-${index}`}>
                    {achievement.number}
                  </div>
                  <div className="text-sm text-muted-foreground" data-testid={`text-achievement-label-${index}`}>
                    {achievement.label}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}