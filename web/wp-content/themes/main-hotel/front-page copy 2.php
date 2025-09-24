<?php get_header(); ?>
<?php the_content(); ?>
<section class="relative min-h-screen flex items-center overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo get_template_directory_uri() ?>/assets/images/banner.png');">
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/30"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 py-20">
        <div class="max-w-5xl">
            <div class="whitespace-nowrap inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border [border-color:var(--badge-outline)] shadow-xs mb-6 bg-white/10 backdrop-blur-sm border-white/20 text-white" data-testid="badge-experience">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-3 h-3 mr-1 fill-yellow-400 text-yellow-400">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                10+ năm kinh nghiệm thiết kế web
            </div>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight" data-testid="text-hero-title">
                Tạo Website<span class="text-blue-400"> Chuyên Nghiệp</span><br>Cho Khách Sạn
            </h1>
            <p class="text-lg md:text-xl text-gray-200 mb-8 max-w-2xl leading-relaxed" data-testid="text-hero-subtitle">
                Với hơn 10 năm kinh nghiệm, tôi giúp khách sạn xây dựng website hiện đại, responsive và tối ưu SEO. Dùng thử miễn phí ngay hôm nay!
            </p>
            <div class="flex flex-wrap gap-4 mb-8">
                <div class="flex items-center space-x-2 text-white">
                    <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code w-4 h-4 text-blue-400" data-replit-metadata="client/src/components/HeroSection.tsx:66:16" data-component-name="Code">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <span class="text-sm font-medium" data-testid="text-feature-modern">Thiết kế hiện đại</span>
                </div>
                <div class="flex items-center space-x-2 text-white">
                    <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smartphone w-4 h-4 text-green-400" data-replit-metadata="client/src/components/HeroSection.tsx:72:16" data-component-name="Smartphone">
                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                            <path d="M12 18h.01"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium" data-testid="text-feature-responsive">100% Responsive</span>
                </div>
                <div class="flex items-center space-x-2 text-white">
                    <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search w-4 h-4 text-purple-400" data-replit-metadata="client/src/components/HeroSection.tsx:78:16" data-component-name="Search">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium" data-testid="text-feature-seo">Tối ưu SEO</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover-elevate active-elevate-2 border-primary-border min-h-10 rounded-md px-8 bg-blue-600 hover:bg-blue-700 text-white border-0 shadow-lg" data-testid="button-trial-hero">
                    Dùng thử miễn phí
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-4 h-4 ml-2" data-replit-metadata="client/src/components/HeroSection.tsx:93:14" data-component-name="ArrowRight">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </button>
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-10 rounded-md px-8 bg-white/10 backdrop-blur-sm border-white/20 text-white hover:bg-white/20" data-testid="button-contact-hero">
                    Tư vấn ngay
                </button>
            </div>
            <div class="flex flex-wrap gap-8 mt-12 pt-8 border-t border-white/20">
                <div class="text-center">
                    <div class="text-2xl md:text-3xl font-bold text-white" data-testid="text-stat-projects">200+</div>
                    <div class="text-sm text-gray-300">Dự án hoàn thành</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl md:text-3xl font-bold text-white" data-testid="text-stat-clients">150+</div>
                    <div class="text-sm text-gray-300">Khách hàng hài lòng</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl md:text-3xl font-bold text-white" data-testid="text-stat-years">10+</div>
                    <div class="text-sm text-gray-300">Năm kinh nghiệm</div>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="gioi-thieu" class="py-20 bg-secondary/30">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <div class="whitespace-nowrap inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border [border-color:var(--badge-outline)] shadow-xs mb-4" data-testid="badge-about">Về chúng tôi</div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4" data-testid="text-about-title">Chuyên gia với 10+ năm kinh nghiệm</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto" data-testid="text-about-subtitle">Tôi đã giúp hàng trăm khách sạn xây dựng website chuyên nghiệp, giúp tăng khách hàng và doanh số đáng kể.</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/avatar.jpg" alt="Developer" class="w-20 h-20 rounded-full object-cover border-4 border-primary/20" data-testid="img-developer" />
                        <div>
                            <h3 class="text-xl font-semibold mb-2" data-testid="text-developer-name">Nguyễn Huy Tập</h3>
                            <p class="text-muted-foreground mb-2" data-testid="text-developer-title">Senior Full-stack Developer</p>
                            <div class="flex flex-wrap gap-2">
                                <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground">Laravel</div>
                                <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground">Wordpress</div>
                                <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground">Node.js</div>
                                <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground">Python</div>
                                <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground">UI/UX</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted-foreground leading-relaxed" data-testid="text-developer-description">Với hơn 10 năm kinh nghiệm trong lĩnh vực phát triển web, tôi đã làm việc với các công nghệ từ cơ bản đến tiên tiến nhất. Chuyên môn của tôi không chỉ dừng lại ở việc code mà còn hiểu sâu về trải nghiệm người dùng và tối ưu hóa website.</p>
                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 bg-primary text-primary-foreground border border-primary-border min-h-10 rounded-md px-8" data-testid="button-contact-about">Tư vấn miễn phí</button>
                </div>
                <div class="space-y-4">
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate" data-testid="card-skill-0">
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code-xml w-6 h-6 text-primary">
                                        <path d="m18 16 4-4-4-4"></path>
                                        <path d="m6 8-4 4 4 4"></path>
                                        <path d="m14.5 4-5 16"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2" data-testid="text-skill-title-0">Full-stack Development</h4>
                                    <p class="text-sm text-muted-foreground" data-testid="text-skill-description-0">Laravel, Wordpress, Node.js, Python, UI/UX</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate" data-testid="card-skill-1">
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-palette w-6 h-6 text-primary">
                                        <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                        <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                        <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                        <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2" data-testid="text-skill-title-1">UI/UX Design</h4>
                                    <p class="text-sm text-muted-foreground" data-testid="text-skill-description-1">Figma, Adobe XD, Modern Design Systems</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate" data-testid="card-skill-2">
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap w-6 h-6 text-primary">
                                        <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2" data-testid="text-skill-title-2">Performance Optimization</h4>
                                    <p class="text-sm text-muted-foreground" data-testid="text-skill-description-2">SEO, Speed Optimization, Responsive Design</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm text-center hover-elevate" data-testid="card-number-0">
                    <div class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award w-6 h-6 text-blue-600">
                                <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
                                <circle cx="12" cy="8" r="6"></circle>
                            </svg>
                        </div>
                        <div class="text-2xl font-bold mb-1" data-testid="text-number-0">10+</div>
                        <div class="text-sm text-muted-foreground" data-testid="text-description-0">Năm kinh nghiệm</div>
                    </div>
                </div>

                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm text-center hover-elevate" data-testid="card-number-1">
                    <div class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-6 h-6 text-green-600">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="text-2xl font-bold mb-1" data-testid="text-number-1">150+</div>
                        <div class="text-sm text-muted-foreground" data-testid="text-description-1">Khách hàng tin tưởng</div>
                    </div>
                </div>

                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm text-center hover-elevate" data-testid="card-number-2">
                    <div class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle w-6 h-6 text-yellow-600">
                                <path d="M9 12l2 2 4-4"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                        </div>
                        <div class="text-2xl font-bold mb-1" data-testid="text-number-2">300+</div>
                        <div class="text-sm text-muted-foreground" data-testid="text-description-2">Dự án hoàn thành</div>
                    </div>
                </div>

                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm text-center hover-elevate" data-testid="card-number-3">
                    <div class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe w-6 h-6 text-red-600">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M2 12h20"></path>
                                <path d="M12 2a15.3 15.3 0 0 1 0 20"></path>
                                <path d="M12 2a15.3 15.3 0 0 0 0 20"></path>
                            </svg>
                        </div>
                        <div class="text-2xl font-bold mb-1" data-testid="text-number-3">5</div>
                        <div class="text-sm text-muted-foreground" data-testid="text-description-3">Tỉnh thành phục vụ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="dich-vu" class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <div class="whitespace-nowrap inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border [border-color:var(--badge-outline)] shadow-xs mb-4">
                    Dịch vụ
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Các gói dịch vụ phù hợp với nhu cầu
                </h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Từ website cơ bản đến hệ thống phức tạp, chúng tôi có giải pháp phù hợp với mọi quy mô khách sạn.
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm relative hover-elevate">
                    <div class="flex flex-col space-y-1.5 p-6 text-center pb-4">
                        <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smartphone w-8 h-8 text-purple-600">
                                <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                                <path d="M12 18h.01"></path>
                            </svg>
                        </div>
                        <div class="font-semibold tracking-tight text-xl mb-2">
                            Landing Page
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Trang đích chuyên biệt để tăng conversion rate, thu hút khách hàng tiềm năng
                        </p>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary mb-1">
                                Miễn phí
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Chỉ tốn phí gia hạn hosting hàng năm
                            </p>
                        </div>
                        <ul class="space-y-2">
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tạo landing page theo mẫu có sẵn</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Đầy đủ thông tin của khách sạn</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tích hợp Google Analytics</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tích hợp đầy đủ các mạng xã hội</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Hỗ trợ trọn đời</span>
                            </li>
                        </ul>
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-9 px-4 py-2 w-full">
                            Tư vấn gói này
                        </button>
                    </div>
                </div>
                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm relative hover-elevate">
                    <div class="flex flex-col space-y-1.5 p-6 text-center pb-4">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor w-8 h-8 text-blue-600">
                                <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                                <line x1="8" x2="16" y1="21" y2="21"></line>
                                <line x1="12" x2="12" y1="17" y2="21"></line>
                            </svg>
                        </div>
                        <div class="font-semibold tracking-tight text-xl mb-2">
                            Website khách sạn
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Website giới thiệu chuyên nghiệp, thể hiện thương hiệu và tăng độ tin cậy
                        </p>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary mb-1">
                                Từ 5 triệu
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Thanh toán 1 lần và gia hạn hosting hàng năm
                            </p>
                        </div>
                        <ul class="space-y-2">
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tạo website theo mẫu có sẵn</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tối ưu SEO</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tích hợp Google Analytics</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">SSL miễn phí</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Hỗ trợ trọn đời</span>
                            </li>
                        </ul>
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-9 px-4 py-2 w-full">
                            Tư vấn gói này
                        </button>
                    </div>
                </div>
                <div class="shadcn-card rounded-xl border bg-card text-card-foreground relative hover-elevate border-primary shadow-lg">
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                        <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent shadow-xs bg-primary text-primary-foreground">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-3 h-3 mr-1 fill-current">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>Phổ biến nhất
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1.5 p-6 text-center pb-4">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-8 h-8 text-green-600">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                            </svg>
                        </div>
                        <div class="font-semibold tracking-tight text-xl mb-2">
                            Website + booking engine
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Hệ thống đặt phòng online đầy đủ tính năng, tích hợp thanh toán và quản lý
                        </p>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary mb-1">
                                Từ 12,000,000₫
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Thanh toán một lần và gia hạn hosting hàng năm
                            </p>
                        </div>
                        <ul class="space-y-2">
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Quản lý tất cả các module: Đóng/mở phòng, giá, chương trình khuyến mãi, ...</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Quản lý booking</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Tích hợp thanh toán</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Báo cáo doanh thu</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">App mobile (tùy chọn)</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check w-4 h-4 text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">Hỗ trợ trọn đời</span>
                            </li>
                        </ul>
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 bg-primary text-primary-foreground border border-primary-border min-h-9 px-4 py-2 w-full">
                            Tư vấn gói này
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold mb-4">
                    Dịch vụ bổ sung
                </h3>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search w-6 h-6 text-primary">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">
                            Tối ưu SEO
                        </h4>
                        <p class="text-sm text-muted-foreground">
                            Tăng thứ hạng Google, thu hút traffic tự nhiên
                        </p>
                    </div>
                </div>
                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-palette w-6 h-6 text-primary">
                                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">
                            UI/UX Design
                        </h4>
                        <p class="text-sm text-muted-foreground">
                            Thiết kế giao diện đẹp, trải nghiệm người dùng tốt
                        </p>
                    </div>
                </div>
                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap w-6 h-6 text-primary">
                                <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">
                            Tối ưu tốc độ
                        </h4>
                        <p class="text-sm text-muted-foreground">
                            Website load nhanh, cải thiện trải nghiệm
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-12">
                <p class="text-muted-foreground mb-4">
                    Không tìm thấy gói phù hợp? Hãy liên hệ để được tư vấn giải pháp riêng.
                </p>
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 bg-primary text-primary-foreground border border-primary-border min-h-10 rounded-md px-8">
                    Tư vấn miễn phí
                </button>
            </div>
        </div>
    </div>
</section>
<section id="khach-hang" class="py-20 bg-secondary/30">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <div class="whitespace-nowrap inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border [border-color:var(--badge-outline)] shadow-xs mb-4">
                    Khách hàng
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Khách hàng tin tưởng &amp; Dự án nổi bật
                </h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Hơn 150 khách sạn đã tin tưởng và đạt được kết quả tuyệt vời với các website chúng tôi thiết kế.
                </p>
            </div>
            <div class="mb-16">
                <h3 class="text-center text-lg font-semibold mb-8 text-muted-foreground">
                    Được tin tưởng bởi
                </h3>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-6">
                    <div class="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-sm text-primary">TC</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-sm text-primary">DS</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-sm text-primary">SB</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-sm text-primary">IH</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-sm text-primary">FT</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center p-4 bg-card rounded-lg hover-elevate">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-sm text-primary">CA</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-16">
                <h3 class="text-2xl font-bold text-center mb-8">
                    Phản hồi từ khách hàng
                </h3>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-quote w-8 h-8 text-primary/20 mb-4">
                                <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                                <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                            </svg>
                            <p class="text-muted-foreground mb-4 leading-relaxed">
                                Website được làm rất chuyên nghiệp, đúng deadline và hỗ trợ nhiệt tình. Doanh số online tăng 200% sau khi ra mắt.
                            </p>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                                    <span class="font-semibold text-sm text-primary">MT</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Anh Minh Tuấn</div>
                                    <div class="text-xs text-muted-foreground">CEO - TechStart Vietnam</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-quote w-8 h-8 text-primary/20 mb-4">
                                <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                                <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                            </svg>
                            <p class="text-muted-foreground mb-4 leading-relaxed">
                                Thiết kế đẹp, responsive tốt trên mobile. Khách hàng khen website chuyên nghiệp, tăng độ tin cậy rất nhiều.
                            </p>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                                    <span class="font-semibold text-sm text-primary">TH</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Chị Thu Hương</div>
                                    <div class="text-xs text-muted-foreground">Giám đốc - Beauty House</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4 fill-yellow-400 text-yellow-400">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-quote w-8 h-8 text-primary/20 mb-4">
                                <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                                <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"></path>
                            </svg>
                            <p class="text-muted-foreground mb-4 leading-relaxed">
                                Hệ thống e-learning phức tạp nhưng anh Dev làm rất tốt. Performance nhanh, user experience xuất sắc.
                            </p>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                                    <span class="font-semibold text-sm text-primary">DA</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Anh Đức Anh</div>
                                    <div class="text-xs text-muted-foreground">Founder - EduTech</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-12">
                <h3 class="text-2xl font-bold text-center mb-8">
                    Dự án tiêu biểu
                </h3>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm overflow-hidden hover-elevate group">
                        <div class="relative overflow-hidden">
                            <img src="<?php echo get_template_directory_uri() ?>/assets/images/edenstar.png" alt="E-commerce Fashion" class="w-full h-48 object-cover transition-transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-8 rounded-md px-3 text-xs bg-white/10 backdrop-blur-sm border-white/20 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link w-4 h-4 mr-2">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>Xem demo
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground mb-2">
                                Khách sạn
                            </div>
                            <h4 class="font-semibold mb-2">
                                Eden Star Sài Gòn
                            </h4>
                            <p class="text-sm text-muted-foreground">
                                Website khách sạn có tích hợp booking engine
                            </p>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm overflow-hidden hover-elevate group">
                        <div class="relative overflow-hidden">
                            <img src="<?php echo get_template_directory_uri() ?>/assets/images/harmony.png" alt="Corporate Website" class="w-full h-48 object-cover transition-transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-8 rounded-md px-3 text-xs bg-white/10 backdrop-blur-sm border-white/20 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link w-4 h-4 mr-2">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>Xem demo
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground mb-2">
                                Khách sạn
                            </div>
                            <h4 class="font-semibold mb-2">
                                Harmony Sài Gòn
                            </h4>
                            <p class="text-sm text-muted-foreground">
                                Website khách sạn có tích hợp booking engine
                            </p>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm overflow-hidden hover-elevate group">
                        <div class="relative overflow-hidden">
                            <img src="<?php echo get_template_directory_uri() ?>/assets/images/roseland.png" alt="Restaurant Landing" class="w-full h-48 object-cover transition-transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-8 rounded-md px-3 text-xs bg-white/10 backdrop-blur-sm border-white/20 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link w-4 h-4 mr-2">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>Xem demo
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="whitespace-nowrap inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border-transparent bg-secondary text-secondary-foreground mb-2">
                                Tập đoàn khách sạn
                            </div>
                            <h4 class="font-semibold mb-2">
                                Roseland
                            </h4>
                            <p class="text-sm text-muted-foreground">
                                Website khách sạn có tích hợp booking engine
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <h3 class="text-xl font-semibold mb-4">
                    Bạn muốn trở thành khách hàng tiếp theo?
                </h3>
                <p class="text-muted-foreground mb-6">
                    Hãy để chúng tôi giúp bạn xây dựng website thành công như các khách sạn trên.
                </p>
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 bg-primary text-primary-foreground border border-primary-border min-h-10 rounded-md px-8">
                    Bắt đầu dự án của bạn
                </button>
            </div>
        </div>
    </div>
</section>
<section id="dung-thu" class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <div class="whitespace-nowrap inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border [border-color:var(--badge-outline)] shadow-xs mb-4">
                    Dùng thử miễn phí
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Nhận tư vấn &amp; demo miễn phí
                </h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Đăng ký ngay để được tư vấn 1-1 và xem demo website phù hợp với khách sạn của bạn.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <div class="space-y-8">
                    <div>
                        <h3 class="text-2xl font-bold mb-6">Bạn sẽ nhận được gì?</h3>
                        <div class="grid gap-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zap w-6 h-6 text-primary">
                                        <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Miễn phí 100%</h4>
                                    <p class="text-sm text-muted-foreground">Không mất phí, không cam kết</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-6 h-6 text-primary">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Tư vấn 1-1</h4>
                                    <p class="text-sm text-muted-foreground">30 phút tư vấn trực tiếp</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-6 h-6 text-primary">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Demo thực tế</h4>
                                    <p class="text-sm text-muted-foreground">Xem mẫu thiết kế cho dự án</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-palette w-6 h-6 text-primary">
                                        <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                        <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                        <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                        <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Phân tích UX</h4>
                                    <p class="text-sm text-muted-foreground">Đánh giá website hiện tại</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-4">Quy trình làm việc</h3>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-xs text-primary-foreground font-bold">1</div>
                                <span class="text-sm">Đăng ký form dưới đây</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-xs text-primary-foreground font-bold">2</div>
                                <span class="text-sm">Nhận cuộc gọi tư vấn trong 24h</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-xs text-primary-foreground font-bold">3</div>
                                <span class="text-sm">Xem demo và nhận báo giá</span>
                            </div>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border text-card-foreground shadow-sm border-green-200 bg-green-50/50">
                        <div class="p-6">
                            <div class="flex items-start space-x-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big w-6 h-6 text-green-600 flex-shrink-0 mt-1">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-green-800 mb-1">Cam kết 100%</h4>
                                    <p class="text-sm text-green-700">Hoàn toàn miễn phí, không ràng buộc. Nếu không hài lòng, bạn có thể từ chối bất cứ lúc nào.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-lg">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <div class="font-semibold tracking-tight text-xl">Đăng ký tư vấn miễn phí</div>
                    </div>
                    <div class="p-6 pt-0">
                        <form class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium mb-1 block">Họ tên *</label>
                                    <input class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Nguyễn Văn A" required="" value="">
                                </div>
                                <div>
                                    <label class="text-sm font-medium mb-1 block">Số điện thoại *</label>
                                    <input type="tel" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="0987654321" required="" value="">
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-1 block">Email *</label>
                                <input type="email" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="email@domain.com" required="" value="">
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-1 block">Tên khách sạn</label>
                                <input class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Khách sạn ABC" value="">
                            </div>
                            <div class="relative">
                                <label class="text-sm font-medium mb-1 block">Loại website cần làm *</label>
                                <button type="button" role="combobox" aria-controls="radix-:r0:" aria-expanded="false" aria-required="true" aria-autocomplete="none" dir="ltr" data-state="closed" data-placeholder="" class="flex h-9 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background data-[placeholder]:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&amp;>span]:line-clamp-1">
                                    <span style="pointer-events: none;">Chọn loại website</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </button>
                                <select aria-hidden="true" required="" tabindex="-1" style="position: absolute; border: 0px; width: 1px; height: 1px; padding: 0px; margin: -1px; overflow: hidden; clip: rect(0px, 0px, 0px, 0px); white-space: nowrap; overflow-wrap: normal;">
                                    <option value="landing">Landing Page</option>
                                    <option value="corporate">Website khách sạn</option>
                                    <option value="ecommerce">Website + Booking engine</option>
                                </select>
                            </div>
                            <div class="relative">
                                <label class="text-sm font-medium mb-1 block">Ngân sách dự kiến</label>
                                <button type="button" role="combobox" aria-controls="radix-:r1:" aria-expanded="false" aria-autocomplete="none" dir="ltr" data-state="closed" data-placeholder="" class="flex h-9 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background data-[placeholder]:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&amp;>span]:line-clamp-1">
                                    <span style="pointer-events: none;">Chọn mức ngân sách</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </button>
                                <select aria-hidden="true" tabindex="-1" style="position: absolute; border: 0px; width: 1px; height: 1px; padding: 0px; margin: -1px; overflow: hidden; clip: rect(0px, 0px, 0px, 0px); white-space: nowrap; overflow-wrap: normal;">
                                    <option value="under-5m">Dưới 5 triệu</option>
                                    <option value="5m-10m">5-10 triệu</option>
                                    <option value="10m-20m">10-20 triệu</option>
                                    <option value="over-20m">Trên 20 triệu</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-1 block">Mô tả chi tiết dự án</label>
                                <textarea class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Ví dụ: Tôi cần website bán thời trang online, có thanh toán, quản lý sản phẩm..." rows="3"></textarea>
                            </div>
                            <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 bg-primary text-primary-foreground border border-primary-border min-h-10 rounded-md px-8 w-full" type="submit">
                                Đăng ký nhận tư vấn miễn phí
                            </button>
                            <p class="text-xs text-muted-foreground text-center">
                                Bằng cách đăng ký, bạn đồng ý với việc chúng tôi liên hệ để tư vấn dự án.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="lien-he" class="py-20 bg-secondary/30">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <div class="whitespace-nowrap inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover-elevate border [border-color:var(--badge-outline)] shadow-xs mb-4">
                    Liên hệ
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Kết nối với chúng tôi
                </h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Hãy để lại thông tin, chúng tôi sẽ liên hệ tư vấn miễn phí và báo giá phù hợp với nhu cầu của bạn.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 space-y-6">
                    <div>
                        <h3 class="text-xl font-bold mb-6">Thông tin liên hệ</h3>
                        <div class="space-y-4">
                            <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                                <div class="p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone w-5 h-5 text-primary">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-sm mb-1">Số điện thoại</h4>
                                            <a href="tel:0987654321" class="text-primary hover:underline font-medium">0922.604.888</a>
                                            <p class="text-xs text-muted-foreground mt-1">Sẵn sàng hỗ trợ 24/7</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                                <div class="p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail w-5 h-5 text-primary">
                                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-sm mb-1">Email</h4>
                                            <a href="mailto:nghuytap@gmail.com" class="text-primary hover:underline font-medium">nghuytap@gmail.com</a>
                                            <p class="text-xs text-muted-foreground mt-1">Phản hồi trong 24h</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-sm hover-elevate">
                                <div class="p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin w-5 h-5 text-primary">
                                                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-sm mb-1">Địa chỉ</h4>
                                            <div class="font-medium">1500/5/1, P.Châu Thành, TP.HCM</div>
                                            <p class="text-xs text-muted-foreground mt-1">Tư vấn online</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadcn-card rounded-xl border text-card-foreground shadow-sm border-primary/20 bg-primary/5">
                        <div class="p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle w-12 h-12 text-primary mx-auto mb-4">
                                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                            </svg>
                            <h4 class="font-semibold mb-2">Cần tư vấn gấp?</h4>
                            <p class="text-sm text-muted-foreground mb-4">Gọi trực tiếp để được hỗ trợ ngay lập tức</p>
                            <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 border [border-color:var(--button-outline)] shadow-xs active:shadow-none min-h-8 rounded-md px-3 text-xs border-primary text-primary hover:bg-primary hover:text-primary-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone w-4 h-4 mr-2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>Gọi ngay
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="shadcn-card rounded-xl border bg-card border-card-border text-card-foreground shadow-lg">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <div class="font-semibold tracking-tight text-xl flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send w-5 h-5 mr-2">
                                    <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                                    <path d="m21.854 2.147-10.94 10.939"></path>
                                </svg>Gửi tin nhắn cho chúng tôi
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <form class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium mb-2 block">Họ tên *</label>
                                        <input class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Nguyễn Văn A" required="" value="">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium mb-2 block">Số điện thoại *</label>
                                        <input type="tel" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="0987654321" required="" value="">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium mb-2 block">Email *</label>
                                    <input type="email" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="email@domain.com" required="" value="">
                                </div>
                                <div class="relative">
                                    <label class="text-sm font-medium mb-2 block">Chủ đề *</label>
                                    <button type="button" role="combobox" aria-controls="radix-:r2:" aria-expanded="false" aria-required="true" aria-autocomplete="none" dir="ltr" data-state="closed" data-placeholder="" class="flex h-9 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background data-[placeholder]:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&amp;>span]:line-clamp-1">
                                        <span style="pointer-events: none;">Chọn chủ đề</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </button>
                                    <select aria-hidden="true" required="" tabindex="-1" style="position: absolute; border: 0px; width: 1px; height: 1px; padding: 0px; margin: -1px; overflow: hidden; clip: rect(0px, 0px, 0px, 0px); white-space: nowrap; overflow-wrap: normal;">
                                        <option value="Tư vấn dự án mới">Tư vấn dự án mới</option>
                                        <option value="Báo giá website">Báo giá website</option>
                                        <option value="Hỗ trợ kỹ thuật">Hỗ trợ kỹ thuật</option>
                                        <option value="Bảo trì website">Bảo trì website</option>
                                        <option value="SEO &amp; Marketing">SEO &amp; Marketing</option>
                                        <option value="Khác">Khác</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium mb-2 block">Tin nhắn *</label>
                                    <textarea class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Vui lòng mô tả chi tiết yêu cầu của bạn..." rows="5" required=""></textarea>
                                </div>
                                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover-elevate active-elevate-2 bg-primary text-primary-foreground border border-primary-border min-h-10 rounded-md px-8 w-full" type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send w-4 h-4 mr-2">
                                        <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                                        <path d="m21.854 2.147-10.94 10.939"></path>
                                    </svg>Gửi tin nhắn
                                </button>
                                <p class="text-xs text-muted-foreground text-center">
                                    Chúng tôi cam kết bảo mật thông tin và phản hồi trong vòng 24 giờ.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php get_footer(); ?>