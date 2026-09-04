<?php

namespace App\Domain\ImageProcessing\Actions;

use App\Models\GalleryImage;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessImageAction
{
    public function execute(GalleryImage $image): array
    {
        $stream = Storage::disk($image->disk)->readStream($image->path);

        if ($stream === false) {
            throw new RuntimeException("Unable to read image {$image->uid} from storage.");
        }

        // try {
        $res = $this->request()
            ->attach('image', $stream, $image->original_name ?? basename($image->path))
            ->post('api/v1/images/process', [
                'image_uid' => $image->uid,
                'callback_url' => route('api.v1.processor.images.update', $image)
            ]);
        // } finally {
        //     fclose($stream);
        // }

        return $this->json($res->json());
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('services.image_processor.url'), '/') . '/')
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
