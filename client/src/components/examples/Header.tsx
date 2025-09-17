import Header from '../Header';

export default function HeaderExample() {
  return (
    <div className="min-h-screen bg-background">
      <Header 
        onContactClick={() => console.log('Contact clicked')}
        onTrialClick={() => console.log('Trial clicked')}
      />
      <div className="pt-20 p-8">
        <h1 className="text-2xl font-bold">Header Component Example</h1>
        <p className="text-muted-foreground mt-2">
          Scroll down to see the fixed header behavior. Try the mobile menu on smaller screens.
        </p>
        <div className="h-96 bg-muted/20 rounded-lg mt-4 flex items-center justify-center">
          <span className="text-muted-foreground">Content area</span>
        </div>
      </div>
    </div>
  );
}