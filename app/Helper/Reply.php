<?php

namespace App\Helper;

class Reply
{
    /**
     * Return success response.
     */
    public static function success(string $message = 'Record saved successfully.'): array
    {
        return [
            'status'  => 'success',
            'message' => self::translate($message),
        ];
    }

    /**
     * Return success response with additional data.
     */
    public static function successWithData(string $message, array $data = []): array
    {
        return array_merge(self::success($message), $data);
    }

    /**
     * Return error response.
     */
    public static function error(string $message, ?string $errorName = null, array $errorData = []): array
    {
        return [
            'status'     => 'fail',
            'error_name' => $errorName,
            'data'       => $errorData,
            'message'    => self::translate($message),
        ];
    }

    /**
     * Return validation form errors.
     */
    public static function formErrors(\Illuminate\Validation\Validator $validator): array
    {
        return [
            'status' => 'fail',
            'errors' => $validator->getMessageBag()->toArray(),
        ];
    }

    /**
     * Return redirect action for AJAX responses.
     */
    public static function redirect(string $url, ?string $message = null): array
    {
        $response = [
            'status' => 'success',
            'action' => 'redirect',
            'url'    => $url,
        ];

        if ($message) {
            $response['message'] = self::translate($message);
        }

        return $response;
    }

    /**
     * Return raw data only.
     */
    public static function dataOnly(array $data): array
    {
        return $data;
    }

    private static function translate(string $message): string
    {
        $translated = trans($message);
        return $translated === $message ? $message : $translated;
    }
}
