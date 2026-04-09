<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsSetting;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SmsService
{
    /**
     * Send an SMS message.
     * Resolves tenant-specific credentials, falls back to global.
     */
    public function send(string $toNumber, string $message, ?int $companyId = null): bool
    {
        $setting = SmsSetting::resolveFor($companyId);

        if (!$setting) {
            Log::warning("SmsService: No active SMS setting for company {$companyId}");
            return false;
        }

        $log = SmsLog::create([
            'company_id' => $companyId,
            'to_number'  => $toNumber,
            'message'    => $message,
            'status'     => 'queued',
        ]);

        try {
            match ($setting->provider) {
                'twilio' => $this->sendViaTwilio($setting, $toNumber, $message, $log),
                default  => throw new \RuntimeException("Provider {$setting->provider} not implemented."),
            };

            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return true;

        } catch (\Throwable $e) {
            Log::error("SmsService failed for {$toNumber}: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return false;
        }
    }

    private function sendViaTwilio(SmsSetting $setting, string $to, string $message, SmsLog $log): void
    {
        $client = new TwilioClient($setting->account_sid, $setting->auth_token);

        $twilioMsg = $client->messages->create($to, [
            'from' => $setting->from_number,
            'body' => $message,
        ]);

        $log->update(['provider_message_id' => $twilioMsg->sid]);
    }
}
