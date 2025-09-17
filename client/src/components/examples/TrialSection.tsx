import TrialSection from '../TrialSection';
import { Toaster } from "@/components/ui/toaster";

export default function TrialSectionExample() {
  return (
    <div className="min-h-screen bg-background">
      <TrialSection onSubmit={(data) => console.log('Trial form submitted:', data)} />
      <Toaster />
    </div>
  );
}