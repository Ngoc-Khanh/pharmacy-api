<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Get;

class TestEmailController extends Controller
{
  /**
   * Test sending email via SMTP
   * 
   * @OA\Post(
   *     path="/api/test/send-email",
   *     summary="Test sending email",
   *     tags={"Test"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="to_email", type="string", format="email", example="test@example.com"),
   *             @OA\Property(property="subject", type="string", example="Test Email"),
   *             @OA\Property(property="message", type="string", example="This is a test email")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Email sent successfully",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Email sent successfully")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed to send email"
   *     )
   * )
   */
  #[Post(uri: "/test/send-email", name: "test.sendEmail")]
  public function sendTestEmail(Request $request)
  {
    try {
      $request->validate([
        'to_email' => 'required|email',
        'subject' => 'required|string|max:255',
        'message' => 'required|string'
      ]);

      $toEmail = $request->input('to_email');
      $subject = $request->input('subject');
      $message = $request->input('message');

      // Simple test email
      Mail::raw($message, function ($mail) use ($toEmail, $subject) {
        $mail->to($toEmail)
          ->subject($subject);
      });

      Log::info('Test email sent successfully', [
        'to' => $toEmail,
        'subject' => $subject
      ]);

      return $this->json(
        null,
        'Email sent successfully'
      );
    } catch (\Exception $e) {
      Log::error('Failed to send test email', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return $this->fail(
        null,
        'Failed to send email: ' . $e->getMessage(),
        500
      );
    }
  }

  /**
   * Test sending verification email
   * 
   * @OA\Post(
   *     path="/api/test/send-verification-email",
   *     summary="Test sending verification email",
   *     tags={"Test"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="to_email", type="string", format="email", example="test@example.com"),
   *             @OA\Property(property="verification_code", type="string", example="123456")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Verification email sent successfully"
   *     )
   * )
   */
  #[Post(uri: "/test/send-verification-email", name: "test.sendVerificationEmail")]
  public function sendVerificationEmail(Request $request)
  {
    try {
      $request->validate([
        'to_email' => 'required|email',
        'verification_code' => 'required|string'
      ]);

      $toEmail = $request->input('to_email');
      $verificationCode = $request->input('verification_code');

      // Create a dummy user object for the email
      $user = new User(['email' => $toEmail]);

      Mail::to($toEmail)->send(new EmailVerificationMail($user, $verificationCode));

      Log::info('Verification email sent successfully', [
        'to' => $toEmail,
        'code' => $verificationCode
      ]);

      return $this->json(
        null,
        'Verification email sent successfully'
      );
    } catch (\Exception $e) {
      Log::error('Failed to send verification email', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return $this->fail(
        null,
        'Failed to send verification email: ' . $e->getMessage(),
        500
      );
    }
  }

  /**
   * Check mail configuration
   * 
   * @OA\Get(
   *     path="/api/test/mail-config",
   *     summary="Check mail configuration",
   *     tags={"Test"},
   *     @OA\Response(
   *         response=200,
   *         description="Mail configuration details"
   *     )
   * )
   */
  #[Get(uri: "/test/mail-config", name: "test.mailConfig")]
  public function checkMailConfig()
  {
    $config = [
      'mailer' => config('mail.default'),
      'host' => config('mail.mailers.smtp.host'),
      'port' => config('mail.mailers.smtp.port'),
      'username' => config('mail.mailers.smtp.username'),
      'from_address' => config('mail.from.address'),
      'from_name' => config('mail.from.name'),
      'scheme' => config('mail.mailers.smtp.scheme'),
    ];

    return $this->json($config, 'Mail configuration retrieved');
  }
}
