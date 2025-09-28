<?php

namespace App\Console\Commands;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestBookingEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:booking-email {email : The email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test booking confirmation email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Create sample booking data
        $booking = new Booking();
        $booking->id = 1;
        $booking->booking_number = 'BK-' . date('Ymd') . '-001';
        $booking->first_name = 'Nguyễn';
        $booking->last_name = 'Văn A';
        $booking->email = $email;
        $booking->phone_number = '0901234567';
        $booking->nationality = 'VN';
        $booking->check_in = now()->addDays(3)->toDateString();
        $booking->check_out = now()->addDays(5)->toDateString();
        $booking->nights = 2;
        $booking->guests = 2;
        $booking->total_amount = 2400000;
        $booking->discount_amount = 200000;
        $booking->tax_amount = 240000;
        $booking->status = 'pending';

        // Create sample hotel data
        $hotel = new Hotel();
        $hotel->name = 'Hotel Test';
        $hotel->email = 'hotel@example.com';
        $hotel->phone = '028-1234-5678';
        $hotel->address = '123 Nguyễn Huệ, Q1, TP.HCM';

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
            Mail::to($email)->send(new BookingConfirmation($booking, $hotel, $bookingDetails));
            $this->info("Test booking confirmation email sent successfully to: {$email}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        }
    }
}
