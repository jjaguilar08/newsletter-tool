<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscribers;

use App\Enums\SubscriberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SubscriberStatus::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
