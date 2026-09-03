<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProcessorImageResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'embedding' => ['sometimes', 'nullable', 'array', 'size:'.config('services.image_processor.embedding_dimensions')],
            'embedding.*' => ['required', 'numeric'],
            'faces' => ['sometimes', 'array', 'max:'.config('services.image_processor.max_faces')],
            'faces.*.face_index' => ['required', 'integer', 'min:0', 'distinct'],
            'faces.*.bounding_box' => ['required', 'array:x,y,width,height'],
            'faces.*.bounding_box.x' => ['required', 'integer', 'min:0'],
            'faces.*.bounding_box.y' => ['required', 'integer', 'min:0'],
            'faces.*.bounding_box.width' => ['required', 'integer', 'min:1'],
            'faces.*.bounding_box.height' => ['required', 'integer', 'min:1'],
            'faces.*.detection_score' => ['nullable', 'numeric'],
            'faces.*.embedding' => ['required', 'array', 'size:'.config('services.image_processor.embedding_dimensions')],
            'faces.*.embedding.*' => ['required', 'numeric'],
            'faces.*.metadata' => ['sometimes', 'array'],
            'model' => ['sometimes', 'string', 'max:255'],
            'metadata' => ['sometimes', 'array'],
            'celebrity_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'tags' => ['sometimes', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],
            'width' => ['sometimes', 'integer', 'min:1'],
            'height' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->exists('faces') && ! $this->exists('embedding')) {
                    $validator->errors()->add('faces', 'The processor must return a faces array.');
                }
            },
        ];
    }
}
