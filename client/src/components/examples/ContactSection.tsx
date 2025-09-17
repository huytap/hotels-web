import ContactSection from '../ContactSection';
import { Toaster } from "@/components/ui/toaster";

export default function ContactSectionExample() {
  return (
    <div className="min-h-screen">
      <ContactSection onSubmit={(data) => console.log('Contact form submitted:', data)} />
      <Toaster />
    </div>
  );
}