<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * SendEmailJob — dispatched for every outbound email.
 * Never send email synchronously from a controller.
 */
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string  $toEmail,
        private readonly string  $templateSlug,
        private readonly array   $placeholders  = [],
        private readonly ?int    $companyId     = null,
        private readonly ?string $attachmentPath = null,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $emailService->send(
            toEmail:        $this->toEmail,
            templateSlug:   $this->templateSlug,
            placeholders:   $this->placeholders,
            companyId:      $this->companyId,
            attachmentPath: $this->attachmentPath,
        );

        if ($this->attachmentPath
            && str_contains($this->attachmentPath, 'temp_pdfs')
            && file_exists($this->attachmentPath)
        ) {
            @unlink($this->attachmentPath);
        }
    }

    public function tags(): array
    {
        return ['email', "template:{$this->templateSlug}", "company:{$this->companyId}"];
    }
}
