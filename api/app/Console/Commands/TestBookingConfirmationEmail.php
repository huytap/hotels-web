<?php

namespace App\Console\Commands;

use App\Mail\BookingConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestBookingConfirmationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:booking-confirmation {email : Email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test booking confirmation email template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Create sample booking data as stdClass objects (not Eloquent models)
        $booking = (object) [
            'id' => 1,
            'booking_number' => 'BK-' . date('Ymd') . '-001',
            'first_name' => 'Nguyễn',
            'last_name' => 'Văn A',
            'email' => $email,
            'phone_number' => '0901234567',
            'nationality' => 'VN',
            'check_in' => now()->addDays(3)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'nights' => 2,
            'guests' => 2,
            'total_amount' => 2400000,
            'discount_amount' => 200000,
            'tax_amount' => 240000,
            'status' => 'pending',
        ];

        // Create sample hotel data
        $hotel = (object) [
            'name' => 'Hotel Test',
            'email' => 'hotel@example.com',
            'phone' => '028-1234-5678',
            'address' => '123 Nguyễn Huệ, Q1, TP.HCM',
        ];

        // Create sample booking details
        $bookingDetails = [
            [
                'roomtype_name' => 'Deluxe Double Room',
                'quantity' => 1,
                'adults' => 2,
                'children' => 0,
                'price_per_night' => 1200000,
                'discount_amount' => 200000,
                'total_amount' => 2400000,
            ]
        ];

        try {
            // Use Mail::html to send directly without the Mailable class for testing
            $view = view('emails.booking-confirmation', [
                'booking' => $booking,
                'hotel' => $hotel,
                'bookingDetails' => $bookingDetails,
            ])->render();

            Mail::html($view, function ($message) use ($email, $booking) {
                $message->to($email)
                        ->subject('Xác nhận đặt phòng - ' . $booking->booking_number);
            });

            $this->info("Booking confirmation email sent successfully to: {$email}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        }
    }
}