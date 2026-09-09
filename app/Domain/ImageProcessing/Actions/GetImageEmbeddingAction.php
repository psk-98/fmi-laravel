<?php

namespace App\Domain\ImageProcessing\Actions;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GetImageEmbeddingAction
{
    public function execute(UploadedFile $image): array
    {
        $stream = fopen($image->getRealPath(), 'rb');

        if ($stream === false) {
            throw new RuntimeException('Unable to read the search image.');
        }

        try {
            $response = $this->request()
                ->attach('image', $stream, $image->getClientOriginalName())
                ->post('api/v1/images/embed')
                ->throw();
        } finally {
            fclose($stream);
        }

        return $this->json($response->json());
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('services.image_processor.url'), '/').'/')
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout((int) config('services.image_processor.timeout'))
            ->retry([200, 500], throw: false);

        $token = config('services.image_processor.api_token');

        return filled($token) ? $request->withToken((string) $token) : $request;
    }

    /** @return array<string, mixed> */
    private function json(mixed $result): array
    {
        if (! is_array($result)) {
            throw new RuntimeException('The image processor returned an invalid JSON response.');
        }

        return $result;
    }
}
