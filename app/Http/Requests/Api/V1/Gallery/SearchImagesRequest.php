<?php

namespace App\Http\Requests\Api\V1\Gallery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SearchImagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['nullable', 'required_without:gallery_image_uid', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'gallery_image_uid' => ['nullable', 'required_without:image', 'uuid', 'exists:gallery_images,uid'],
            'limit' => ['sometimes', 'integer', 'between:1,50'],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->hasFile('image') && $this->filled('gallery_image_uid')) {
                    $validator->errors()->add('image', 'Provide either an image or a gallery image UID, not both.');
                }
            },
        ];
    }
}
